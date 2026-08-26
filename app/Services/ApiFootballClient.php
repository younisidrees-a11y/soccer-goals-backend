<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around API-Football (api-sports.io) v3 - real match
 * statistics, lineups, event timelines, and per-player ratings that
 * football-data.org's tier doesn't provide. Throttled to the account's
 * real limits only - both the per-minute and daily figures are read
 * live from the API (the response headers and /status) and cached,
 * rather than a hardcoded number that would either waste a paid plan or
 * go stale the moment the plan changes.
 *
 * Every method returns null on any failure (no key, request over quota,
 * network error, bad response) so callers skip a sync cycle instead of
 * crashing or writing broken data.
 */
class ApiFootballClient
{
    private const BASE_URL = 'https://v3.football.api-sports.io';

    public function getTeams(int $leagueId, int $season): ?array
    {
        return $this->request('/teams', ['league' => $leagueId, 'season' => $season]);
    }

    public function getFixturesByRound(int $leagueId, int $season, string $round): ?array
    {
        return $this->request('/fixtures', ['league' => $leagueId, 'season' => $season, 'round' => $round]);
    }

    /** The whole season's fixtures in one call - for leagues football-data.org doesn't cover, where API-Football is the primary source, not just enrichment. */
    public function getSeasonFixtures(int $leagueId, int $season): ?array
    {
        return $this->request('/fixtures', ['league' => $leagueId, 'season' => $season]);
    }

    public function getStatistics(int $fixtureId): ?array
    {
        return $this->request('/fixtures/statistics', ['fixture' => $fixtureId]);
    }

    public function getEvents(int $fixtureId): ?array
    {
        return $this->request('/fixtures/events', ['fixture' => $fixtureId]);
    }

    public function getLineups(int $fixtureId): ?array
    {
        return $this->request('/fixtures/lineups', ['fixture' => $fixtureId]);
    }

    public function getPlayers(int $fixtureId): ?array
    {
        return $this->request('/fixtures/players', ['fixture' => $fixtureId]);
    }

    public function getPredictions(int $fixtureId): ?array
    {
        return $this->request('/predictions', ['fixture' => $fixtureId]);
    }

    public function getCoach(int $teamId): ?array
    {
        return $this->request('/coachs', ['team' => $teamId]);
    }

    private function request(string $path, array $query = []): ?array
    {
        $key = config('services.api_football.key');

        if (! $key) {
            return null;
        }

        if (! $this->throttle()) {
            return null;
        }

        try {
            $response = Http::withHeaders(['x-apisports-key' => $key])
                ->timeout(30)
                ->retry(2, 1000)
                ->get(self::BASE_URL.$path, $query);

            if ($response->failed()) {
                Log::warning('API-Football request failed', [
                    'path' => $path,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            if ($response->hasHeader('x-ratelimit-limit')) {
                Cache::put('api_football:per_minute_limit', (int) $response->header('x-ratelimit-limit'), now()->addHours(6));
            }

            $body = $response->json();

            if (! empty($body['errors'])) {
                Log::warning('API-Football returned an error payload', ['path' => $path, 'errors' => $body['errors']]);

                return null;
            }

            return $body;
        } catch (\Throwable $e) {
            Log::warning('API-Football request errored: '.$e->getMessage(), ['path' => $path]);

            return null;
        }
    }

    /** Blocks briefly to stay under 10 req/min, and refuses outright once the account's real daily quota is hit. */
    private function throttle(): bool
    {
        $dayKey = 'api_football:calls:'.now()->format('Y-m-d');
        $dailyLimit = $this->dailyLimit();
        $dayCount = Cache::get($dayKey, 0);

        if ($dayCount >= $dailyLimit) {
            Log::warning("API-Football daily quota ({$dailyLimit}) reached - skipping request until tomorrow.");

            return false;
        }

        $minuteKey = 'api_football:calls_minute:'.intdiv(time(), 60);
        $minuteCount = Cache::get($minuteKey, 0);
        $perMinuteLimit = Cache::get('api_football:per_minute_limit', 300);

        if ($minuteCount >= $perMinuteLimit) {
            sleep(max(1, 61 - (time() % 60)));
        }

        Cache::put($dayKey, $dayCount + 1, now()->endOfDay());
        Cache::add($minuteKey, 0, 65);
        Cache::increment($minuteKey);

        return true;
    }

    private function dailyLimit(): int
    {
        return Cache::remember('api_football:daily_limit', now()->addHours(6), function () {
            $key = config('services.api_football.key');

            if (! $key) {
                return 100;
            }

            try {
                $response = Http::withHeaders(['x-apisports-key' => $key])->timeout(15)->get(self::BASE_URL.'/status');

                return (int) ($response->json('response.requests.limit_day') ?? 100);
            } catch (\Throwable) {
                return 100;
            }
        });
    }
}
