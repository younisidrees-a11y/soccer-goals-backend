<?php

namespace App\Console\Commands;

use App\Models\MatchFixture;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Defense in depth against a repeat of the 30 Aug 2026 incident (four
 * MLS matches stuck on "scheduled" for 13+ hours after kickoff because
 * an API-Football quota exhaustion silently stopped every sync that
 * would have updated them, and nothing surfaced that until a human
 * noticed stale data on the live site). api-football:status-check
 * catches the cause (quota climbing toward the limit); this catches the
 * symptom directly - any published match still "scheduled" well after
 * it should have kicked off, regardless of why. Two different failure
 * modes could produce the same symptom (quota exhaustion, a genuine
 * API-Football outage, a bug in a sync command) - this doesn't care
 * which one it is.
 */
class CheckStaleScheduledMatches extends Command
{
    protected $signature = 'matches:check-stale {--hours=3 : How many hours past kickoff before a still-scheduled match is flagged}';

    protected $description = 'Warn about published matches still "scheduled" long after their real kickoff time';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');

        $stale = MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
            ->where('status', 'scheduled')
            ->where('is_published', true)
            ->where('kickoff_at', '<', now()->subHours($hours))
            ->orderBy('kickoff_at')
            ->get();

        if ($stale->isEmpty()) {
            $this->info('None found - every published match is in a status consistent with its kickoff time.');

            return self::SUCCESS;
        }

        foreach ($stale as $match) {
            $hoursOverdue = round($match->kickoff_at->diffInMinutes(now()) / 60, 1);
            $label = "#{$match->id} {$match->homeTeam->name} vs {$match->awayTeam->name} ({$match->league->name}), kicked off {$hoursOverdue}h ago, still 'scheduled'";
            $this->warn($label);
        }

        Log::warning("{$stale->count()} published match(es) still 'scheduled' more than {$hours}h past kickoff - a sync is likely stuck or failing.", [
            'match_ids' => $stale->pluck('id')->all(),
        ]);

        return self::SUCCESS;
    }
}
