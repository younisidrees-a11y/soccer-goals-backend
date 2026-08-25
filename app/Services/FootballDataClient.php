<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around the football-data.org v4 API - real fixtures,
 * results, and standings, replacing the fictional match simulation this
 * site started with. Free tier: 10 requests/minute, so callers should
 * batch/space out calls rather than hammering it.
 *
 * Every method returns null on any failure (no key, network error, bad
 * response) so callers can skip a sync cycle instead of crashing or
 * writing broken data.
 */
class FootballDataClient
{
    private const BASE_URL = 'https://api.football-data.org/v4';

    public function getTeams(string $competitionCode): ?array
    {
        return $this->request("/competitions/{$competitionCode}/teams");
    }

    public function getMatches(string $competitionCode, ?int $matchday = null): ?array
    {
        return $this->request("/competitions/{$competitionCode}/matches", $matchday ? ['matchday' => $matchday] : []);
    }

    public function getStandings(string $competitionCode): ?array
    {
        return $this->request("/competitions/{$competitionCode}/standings");
    }

    private function request(string $path, array $query = []): ?array
    {
        $key = config('services.football_data.key');

        if (! $key) {
            return null;
        }

        try {
            $response = Http::withHeaders(['X-Auth-Token' => $key])
                ->timeout(30)
                ->retry(2, 1000)
                ->get(self::BASE_URL.$path, $query);

            if ($response->failed()) {
                Log::warning('football-data.org request failed', [
                    'path' => $path,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::warning('football-data.org request errored: '.$e->getMessage(), ['path' => $path]);

            return null;
        }
    }
}
