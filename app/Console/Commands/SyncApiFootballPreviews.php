<?php

namespace App\Console\Commands;

use App\Models\League;
use App\Models\MatchFixture;
use App\Models\Team;
use App\Services\ApiFootballClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Enriches upcoming (not yet played) fixtures for the match preview page:
 * referee, a statistical win/draw/win prediction, confirmed lineups (only
 * ever available shortly before kickoff - fetched but simply skipped if
 * empty, never guessed), and each team's current coach. Only looks at
 * fixtures within the next 7 days, since previewing something a month out
 * would mostly waste calls on data that doesn't exist yet.
 *
 * Run api-football:map-teams first, or matches for unmapped teams are
 * skipped.
 */
#[Signature('api-football:sync-previews {league : League slug, e.g. premier-league}')]
#[Description('Pull referee, prediction, lineups (if confirmed) and coach details for upcoming fixtures from API-Football')]
class SyncApiFootballPreviews extends Command
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
            ->where('status', 'scheduled')
            ->where('kickoff_at', '<=', now()->addDays(7))
            ->get();

        if ($pending->isEmpty()) {
            $this->info('No upcoming fixtures within the next 7 days to preview.');
        }

        $resolved = 0;
        $predicted = 0;
        $lineupsFetched = 0;
        $skippedUnmapped = 0;

        foreach ($pending->groupBy('matchday') as $matchday => $matches) {
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

                    continue;
                }

                $fixture = $fixturesByTeamPair->get("{$homeApiId}-{$awayApiId}");

                if (! $fixture) {
                    continue;
                }

                $fixtureId = $fixture['fixture']['id'];
                $match->update([
                    'api_football_fixture_id' => $fixtureId,
                    'referee' => $fixture['fixture']['referee'] ?? $match->referee,
                ]);
                $resolved++;

                if (! $match->prediction) {
                    $predResponse = $client->getPredictions($fixtureId);
                    $prediction = $this->parsePrediction($predResponse);

                    if ($prediction) {
                        $match->update(['prediction' => $prediction]);
                        $predicted++;
                    }
                }

                $closeToKickoff = now()->greaterThanOrEqualTo($match->kickoff_at->copy()->subHours(3));

                if (! $match->lineups && $closeToKickoff) {
                    $lineupResponse = $client->getLineups($fixtureId);
                    $lineups = $this->parseLineups($lineupResponse);

                    if ($lineups) {
                        $match->update(['lineups' => $lineups]);
                        $lineupsFetched++;
                    }
                }
            }
        }

        $this->syncCoaches($pending, $client);

        $this->info("Resolved {$resolved} fixture(s), added {$predicted} prediction(s), fetched {$lineupsFetched} confirmed lineup(s).");
        if ($skippedUnmapped > 0) {
            $this->warn("{$skippedUnmapped} match(es) skipped - unmapped teams.");
        }

        return self::SUCCESS;
    }

    private function syncCoaches($matches, ApiFootballClient $client): void
    {
        $teamIds = $matches->pluck('home_team_id')->merge($matches->pluck('away_team_id'))->unique();

        $teams = Team::whereIn('id', $teamIds)
            ->whereNotNull('api_football_id')
            ->where(fn ($q) => $q->whereNull('coach_synced_at')->orWhere('coach_synced_at', '<', now()->subDays(30)))
            ->get();

        $synced = 0;

        foreach ($teams as $team) {
            $response = $client->getCoach($team->api_football_id);
            $coach = $this->findCurrentCoach($response, $team->api_football_id);

            $team->update([
                'manager' => $coach['name'] ?? $team->manager,
                'manager_photo_path' => $coach['photo'] ?? $team->manager_photo_path,
                'coach_age' => $coach['age'] ?? $team->coach_age,
                'coach_nationality' => $coach['nationality'] ?? $team->coach_nationality,
                'coach_synced_at' => now(),
            ]);

            if ($coach) {
                $synced++;
            }
        }

        if ($synced > 0) {
            $this->info("Synced coach details for {$synced} team(s).");
        }
    }

    /** Finds the career entry with no end date matching this team - the array isn't guaranteed sorted, so the last element isn't reliably the current job. */
    private function findCurrentCoach(?array $response, int $teamId): ?array
    {
        $person = $response['response'][0] ?? null;

        if (! $person) {
            return null;
        }

        $current = collect($person['career'] ?? [])->first(
            fn ($job) => $job['team']['id'] === $teamId && $job['end'] === null
        );

        if (! $current) {
            return null;
        }

        return [
            'name' => $person['name'],
            'age' => $person['age'],
            'nationality' => $person['nationality'],
            'photo' => $person['photo'],
        ];
    }

    private function parsePrediction(?array $response): ?array
    {
        $p = $response['response'][0]['predictions'] ?? null;

        if (! $p || ! isset($p['percent']['home'], $p['percent']['draw'], $p['percent']['away'])) {
            return null;
        }

        return [
            'home_pct' => (int) rtrim($p['percent']['home'], '%'),
            'draw_pct' => (int) rtrim($p['percent']['draw'], '%'),
            'away_pct' => (int) rtrim($p['percent']['away'], '%'),
            'advice' => $p['advice'] ?? null,
        ];
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
                'grid' => $p['player']['grid'],
            ])->values()->all(),
        ])->values()->all();
    }
}
