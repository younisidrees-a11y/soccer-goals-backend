<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Reads the real plan/quota straight from API-Football's own /status
 * endpoint - not a guess, not a cached assumption. Built after the
 * schedule was found throttled for a 100/day quota that was actually
 * wrong (this account is on the Pro plan, 7,500/day) - use this instead
 * of re-deriving the number from memory or old code comments.
 */
class CheckApiFootballStatus extends Command
{
    protected $signature = 'api-football:status-check {--warn-threshold=85 : Log a warning once usage crosses this percent of the daily limit}';

    protected $description = "Show this account's real API-Football plan and today's request usage, warning early if it's approaching the daily limit";

    public function handle(): int
    {
        $key = config('services.api_football.key');

        if (! $key) {
            $this->error('No API_FOOTBALL_KEY configured.');

            return self::FAILURE;
        }

        $response = Http::withHeaders(['x-apisports-key' => $key])
            ->get('https://v3.football.api-sports.io/status');

        if ($response->failed()) {
            $this->error('Request to API-Football failed: '.$response->status());

            return self::FAILURE;
        }

        $account = $response->json('response');

        if (! $account) {
            $this->error('Unexpected response shape - check the API key.');

            return self::FAILURE;
        }

        $plan = $account['subscription']['plan'] ?? 'unknown';
        $active = $account['subscription']['active'] ?? false;
        $end = $account['subscription']['end'] ?? 'unknown';
        $used = $account['requests']['current'] ?? '?';
        $limit = $account['requests']['limit_day'] ?? '?';

        $this->line("Plan: {$plan} (".($active ? 'active' : 'INACTIVE').", renews/ends {$end})");
        $this->line("Requests today: {$used} / {$limit}");

        if (! $active) {
            Log::critical("API-Football subscription is not active (plan: {$plan}) - every sync depending on it will start failing.");
        }

        if (is_numeric($used) && is_numeric($limit) && $limit > 0) {
            $pct = round(($used / $limit) * 100, 1);
            $this->line("Used: {$pct}%");

            $threshold = (float) $this->option('warn-threshold');

            // This is the actual guard against a repeat of the "stuck for
            // 13 hours before anyone noticed" incident - a log line
            // written BEFORE the quota is actually gone, not a failure
            // discovered only after every sync has already started
            // silently no-op'ing for the rest of the day.
            if ($pct >= $threshold) {
                Log::warning("API-Football usage at {$pct}% of today's {$limit}-request limit ({$used} used) - approaching the daily quota.");
            }
        }

        return self::SUCCESS;
    }
}
