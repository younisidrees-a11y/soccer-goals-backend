<?php

namespace App\Console\Commands;

use App\Models\League;
use App\Models\MatchFixture;
use App\Models\Team;
use App\Services\AiMatchReportWriter;
use App\Services\ApiFootballClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Fills in real match statistics, event timelines, lineups, and a
 * ratings-based Man of the Match for finished matches - all from
 * API-Football, since football-data.org's tier doesn't carry any of
 * this. Only touches matches that don't have it yet (motm is null), so
 * it's safe to run on a schedule alongside football-data:sync without
 * re-spending API calls on matches already enriched.
 *
 * Run api-football:map-teams first, or matches for unmapped teams are
 * skipped.
 */
#[Signature('api-football:sync-stats {league : League slug, e.g. premier-league}')]
#[Description("Pull real statistics, events, lineups, and Man of the Match from API-Football for finished matches that don't have them yet")]
class SyncApiFootballStats extends Command
{
    public function handle(): int
    {
        $league = League::where('slug', $this->argument('league'))->first();

        if (! $league) {
            $this->error("No league found with slug \"{$this->argument('league')}\".");

            return self::FAILURE;
        }

        if (! $league->api_football_id) {
            $this->error("{$league->name} has no api_football_id set - run api-football:map-teams first.");

            return self::FAILURE;
        }

        $client = app(ApiFootballClient::class);

        $pending = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->where('league_id', $league->id)
            ->where('status', 'final')
            ->whereNull('motm')
            ->get();

        if ($pending->isEmpty()) {
            $this->info('Nothing to enrich - every finished match already has real stats.');

            return self::SUCCESS;
        }

        $teamsByApiId = Team::where('league_id', $league->id)->whereNotNull('api_football_id')->get()->keyBy('api_football_id');

        // Fetch each matchday's round once and match fixtures by team pair,
        // rather than looking up one fixture at a time - far fewer calls.
        $byMatchday = $pending->groupBy('matchday');
        $resolved = 0;
        $enriched = 0;
        $skippedUnmapped = 0;

        foreach ($byMatchday as $matchday => $matches) {
            $round = "Regular Season - {$matchday}";
            $response = $client->getFixturesByRound($league->api_football_id, (int) $league->season, $round);

            if (! $response || empty($response['response'])) {
                $this->warn("Matchday {$matchday}: could not fetch round from API-Football, skipping.");

                continue;
            }

            $fixturesByTeamPair = collect($response['response'])->keyBy(
                fn ($f) => $f['teams']['home']['id'].'-'.$f['teams']['away']['id']
            );

            foreach ($matches as $match) {
                $homeApiId = $match->homeTeam->api_football_id;
                $awayApiId = $match->awayTeam->api_football_id;

                if (! $homeApiId || ! $awayApiId) {
                    $skippedUnmapped++;
                    $this->warn("Skipped {$match->homeTeam->name} vs {$match->awayTeam->name}: team not mapped to API-Football yet.");

                    continue;
                }

                $fixture = $fixturesByTeamPair->get("{$homeApiId}-{$awayApiId}");

                if (! $fixture) {
                    $this->warn("Skipped {$match->homeTeam->name} vs {$match->awayTeam->name}: not found in API-Football's round {$matchday} data.");

                    continue;
                }

                $fixtureId = $fixture['fixture']['id'];
                $match->update([
                    'api_football_fixture_id' => $fixtureId,
                    'referee' => $fixture['fixture']['referee'] ?? $match->referee,
                ]);
                $resolved++;

                if ($this->enrichMatch($match, $fixtureId, $client, $teamsByApiId)) {
                    $enriched++;
                }
            }
        }

        $this->info("Resolved {$resolved} fixture(s) to API-Football IDs, enriched {$enriched} with real stats.");
        if ($skippedUnmapped > 0) {
            $this->warn("{$skippedUnmapped} match(es) skipped - unmapped teams.");
        }

        return self::SUCCESS;
    }

    private function enrichMatch(MatchFixture $match, int $fixtureId, ApiFootballClient $client, $teamsByApiId): bool
    {
        $statsResponse = $client->getStatistics($fixtureId);
        $eventsResponse = $client->getEvents($fixtureId);
        $lineupsResponse = $client->getLineups($fixtureId);
        $playersResponse = $client->getPlayers($fixtureId);

        $stats = $this->parseStats($statsResponse, $match);
        $events = $this->parseEvents($eventsResponse);
        $lineups = $this->parseLineups($lineupsResponse);
        $motm = $this->parseMotm($playersResponse, $teamsByApiId);

        if (! $stats && ! $events && ! $lineups && ! $motm) {
            $this->warn("No real stats came back yet for {$match->homeTeam->name} vs {$match->awayTeam->name} (fixture {$fixtureId}) - API-Football may not have processed it yet.");

            return false;
        }

        $match->update([
            'stats' => $stats ?: $match->stats,
            'events' => $events,
            'lineups' => $lineups,
            'motm' => $motm,
        ]);

        // Now that real stats/MOTM exist, rewrite the report to weave them in naturally
        // instead of the score-only version written when the match first finished.
        if ($stats || $motm) {
            $report = app(AiMatchReportWriter::class)->write($match, $match->home_score, $match->away_score, $stats ?? [], $motm);
            if ($report) {
                $match->update(['match_report' => $report]);
            }
        }

        $this->info("Enriched {$match->homeTeam->name} vs {$match->awayTeam->name} (fixture {$fixtureId}).");

        return true;
    }

    private function parseStats(?array $response, MatchFixture $match): ?array
    {
        if (! $response || empty($response['response']) || count($response['response']) < 2) {
            return null;
        }

        $home = $this->flattenStats($response['response'], $match->homeTeam->api_football_id);
        $away = $this->flattenStats($response['response'], $match->awayTeam->api_football_id);

        if (! $home || ! $away) {
            return null;
        }

        $result = [];

        if (isset($home['Ball Possession'], $away['Ball Possession'])) {
            $result['possession'] = [
                'home' => (int) rtrim($home['Ball Possession'], '%'),
                'away' => (int) rtrim($away['Ball Possession'], '%'),
            ];
        }

        $map = [
            'Total Shots' => 'shots',
            'Shots on Goal' => 'shots_on_target',
            'Corner Kicks' => 'corners',
            'Fouls' => 'fouls',
            'Yellow Cards' => 'yellow_cards',
            'Red Cards' => 'red_cards',
            'Offsides' => 'offsides',
        ];

        foreach ($map as $apiKey => $ourKey) {
            if (isset($home[$apiKey]) && isset($away[$apiKey])) {
                $result[$ourKey] = ['home' => (int) $home[$apiKey], 'away' => (int) $away[$apiKey]];
            }
        }

        return $result ?: null;
    }

    private function flattenStats(array $teamsBlocks, ?int $apiTeamId): ?array
    {
        if (! $apiTeamId) {
            return null;
        }

        foreach ($teamsBlocks as $block) {
            if ($block['team']['id'] === $apiTeamId) {
                $flat = [];
                foreach ($block['statistics'] as $stat) {
                    $flat[$stat['type']] = $stat['value'];
                }

                return $flat;
            }
        }

        return null;
    }

    private function parseEvents(?array $response): ?array
    {
        if (! $response || empty($response['response'])) {
            return null;
        }

        return collect($response['response'])->map(fn ($e) => [
            'minute' => $e['time']['elapsed'].($e['time']['extra'] ? '+'.$e['time']['extra'] : ''),
            'type' => $e['type'],
            'detail' => $e['detail'],
            'player' => $e['player']['name'] ?? null,
            'assist' => $e['assist']['name'] ?? null,
            'team' => $e['team']['name'],
            'team_id' => $e['team']['id'],
        ])->values()->all();
    }

    private function parseLineups(?array $response): ?array
    {
        if (! $response || empty($response['response'])) {
            return null;
        }

        return collect($response['response'])->map(fn ($t) => [
            'team' => $t['team']['name'],
            'team_id' => $t['team']['id'],
            'formation' => $t['formation'],
            'coach' => $t['coach']['name'] ?? null,
            'start_xi' => collect($t['startXI'])->map(fn ($p) => [
                'name' => $p['player']['name'],
                'number' => $p['player']['number'],
                'position' => $p['player']['pos'],
            ])->values()->all(),
        ])->values()->all();
    }

    private function parseMotm(?array $response, $teamsByApiId): ?array
    {
        if (! $response || empty($response['response'])) {
            return null;
        }

        $best = null;
        $bestRating = 0;

        foreach ($response['response'] as $teamBlock) {
            foreach ($teamBlock['players'] as $p) {
                $rating = (float) ($p['statistics'][0]['games']['rating'] ?? 0);
                if ($rating > $bestRating) {
                    $bestRating = $rating;
                    $best = [
                        'name' => $p['player']['name'],
                        'photo' => $p['player']['photo'],
                        'rating' => $rating,
                        'team_id' => $teamBlock['team']['id'],
                        'team_name' => $teamBlock['team']['name'],
                        'position' => $p['statistics'][0]['games']['position'] ?? null,
                    ];
                }
            }
        }

        if (! $best || $bestRating <= 0) {
            return null;
        }

        $ourTeam = $teamsByApiId->get($best['team_id']);
        $best['our_team_id'] = $ourTeam?->id;

        return $best;
    }
}
