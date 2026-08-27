<?php

namespace App\Console\Commands;

use App\Models\League;
use App\Models\MatchFixture;
use App\Services\AiLiveCommentaryWriter;
use App\Services\ApiFootballClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Writes one new AI commentary line per live match, per minute, for
 * leagues piloting this feature (League::live_commentary_enabled).
 * Meant to run on a much tighter schedule than everything else on this
 * site (every minute, not every five) - but it's cheap to run often
 * since it's a no-op the instant there's no live match in the league,
 * and idempotent per real match-minute even if it somehow runs twice
 * before the clock ticks over.
 *
 * Data grounding: the real elapsed minute and score come from a live
 * fixture-status call (the only endpoint that carries the actual match
 * clock), new events since the last line come from the events endpoint,
 * and live possession comes from statistics - all real API-Football
 * data, nothing invented. AiLiveCommentaryWriter is the only thing
 * allowed to turn that into prose, and only from what's passed to it.
 */
#[Signature('api-football:sync-commentary {league : League slug, e.g. la-liga}')]
#[Description('Write one new AI live-commentary line per live match for leagues piloting this feature')]
class SyncApiFootballCommentary extends Command
{
    public function handle(): int
    {
        $league = League::where('slug', $this->argument('league'))->first();

        if (! $league) {
            $this->error("No league found with slug \"{$this->argument('league')}\".");

            return self::FAILURE;
        }

        if (! $league->live_commentary_enabled) {
            $this->error("{$league->name} doesn't have live_commentary_enabled - this command is only meant for piloting leagues.");

            return self::FAILURE;
        }

        if (! $league->api_football_id) {
            $this->error("{$league->name} has no api_football_id set.");

            return self::FAILURE;
        }

        $liveMatches = MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
            ->where('league_id', $league->id)
            ->where('status', 'live')
            ->get();

        if ($liveMatches->isEmpty()) {
            $this->info('Nothing live right now.');

            return self::SUCCESS;
        }

        $client = app(ApiFootballClient::class);
        $writer = app(AiLiveCommentaryWriter::class);
        $written = 0;

        foreach ($liveMatches as $match) {
            if (! $match->api_football_fixture_id && ! $this->resolveFixtureId($match, $league, $client)) {
                $this->warn("Skipped {$match->homeTeam->name} vs {$match->awayTeam->name}: could not resolve API-Football fixture id.");

                continue;
            }

            if ($this->writeCommentaryLine($match, $client, $writer)) {
                $written++;
            }
        }

        $this->info("Wrote {$written} new commentary line(s) across {$liveMatches->count()} live match(es).");

        return self::SUCCESS;
    }

    private function resolveFixtureId(MatchFixture $match, League $league, ApiFootballClient $client): bool
    {
        $homeApiId = $match->homeTeam->api_football_id;
        $awayApiId = $match->awayTeam->api_football_id;

        if (! $homeApiId || ! $awayApiId) {
            return false;
        }

        $round = "Regular Season - {$match->matchday}";
        $response = $client->getFixturesByRound($league->api_football_id, (int) $league->season, $round);

        if (! $response || empty($response['response'])) {
            return false;
        }

        $fixture = collect($response['response'])->first(
            fn ($f) => $f['teams']['home']['id'] === $homeApiId && $f['teams']['away']['id'] === $awayApiId
        );

        if (! $fixture) {
            return false;
        }

        $match->update(['api_football_fixture_id' => $fixture['fixture']['id']]);

        return true;
    }

    private function writeCommentaryLine(MatchFixture $match, ApiFootballClient $client, AiLiveCommentaryWriter $writer): bool
    {
        $statusResponse = $client->getFixtureById($match->api_football_fixture_id);
        $fixture = $statusResponse['response'][0] ?? null;

        if (! $fixture) {
            $this->warn("No live status came back yet for {$match->homeTeam->name} vs {$match->awayTeam->name}.");

            return false;
        }

        $elapsed = $fixture['fixture']['status']['elapsed'] ?? null;

        if ($elapsed === null) {
            $this->warn("No elapsed-minute clock yet for {$match->homeTeam->name} vs {$match->awayTeam->name} (status: {$fixture['fixture']['status']['long']}).");

            return false;
        }

        $existing = collect($match->commentary ?? []);
        $lastMinute = (int) ($existing->last()['minute'] ?? -1);

        // Idempotent per real match-minute: don't write a second line for a
        // minute we've already covered, even if this command runs twice
        // before the clock actually ticks over.
        if ($elapsed <= $lastMinute) {
            return false;
        }

        $homeScore = $fixture['goals']['home'] ?? $match->home_score ?? 0;
        $awayScore = $fixture['goals']['away'] ?? $match->away_score ?? 0;

        $eventsResponse = $client->getEvents($match->api_football_fixture_id);
        $allEvents = collect($eventsResponse['response'] ?? [])->map(fn ($e) => [
            'minute' => (int) $e['time']['elapsed'],
            'type' => $e['type'],
            'detail' => $e['detail'],
            'player' => $e['player']['name'] ?? null,
            'assist' => $e['assist']['name'] ?? null,
            'team' => $e['team']['name'],
        ]);
        $newEvents = $allEvents->filter(fn ($e) => $e['minute'] > $lastMinute && $e['minute'] <= $elapsed)->values()->all();

        $statsResponse = $client->getStatistics($match->api_football_fixture_id);
        $possession = $this->parsePossession($statsResponse, $match->homeTeam->api_football_id, $match->awayTeam->api_football_id);

        $recentLines = $existing->slice(-3)->pluck('text')->values()->all();

        $line = $writer->write(
            $match->homeTeam->name,
            $match->awayTeam->name,
            $match->league->name,
            (int) $elapsed,
            (int) $homeScore,
            (int) $awayScore,
            $newEvents,
            $possession,
            $recentLines,
        );

        if (! $line) {
            $this->warn("AI commentary generation failed for {$match->homeTeam->name} vs {$match->awayTeam->name} at minute {$elapsed}.");

            return false;
        }

        $existing->push(['minute' => (int) $elapsed, 'text' => $line, 'at' => now()->toIso8601String()]);
        $match->update(['commentary' => $existing->values()->all()]);

        $this->info("{$match->homeTeam->name} vs {$match->awayTeam->name}, {$elapsed}': {$line}");

        return true;
    }

    private function parsePossession(?array $response, ?int $homeApiId, ?int $awayApiId): ?array
    {
        if (! $response || empty($response['response']) || count($response['response']) < 2 || ! $homeApiId || ! $awayApiId) {
            return null;
        }

        $find = function (int $apiTeamId) use ($response) {
            foreach ($response['response'] as $block) {
                if ($block['team']['id'] === $apiTeamId) {
                    foreach ($block['statistics'] as $stat) {
                        if ($stat['type'] === 'Ball Possession' && $stat['value']) {
                            return (int) rtrim($stat['value'], '%');
                        }
                    }
                }
            }

            return null;
        };

        $home = $find($homeApiId);
        $away = $find($awayApiId);

        return ($home !== null && $away !== null) ? ['home' => $home, 'away' => $away] : null;
    }
}
