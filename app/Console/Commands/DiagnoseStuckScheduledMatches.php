<?php

namespace App\Console\Commands;

use App\Models\MatchFixture;
use Illuminate\Console\Command;

/**
 * One-off diagnostic: a MatchFixture with status still "scheduled" but a
 * real score already recorded (home_score/away_score not null) means an
 * editor entered the result in Filament without flipping status to
 * "final" - the match then gets excluded from every isFinal()-gated
 * feature (Match Spotlight, results pages, standings-final checks) even
 * though it already has a real result. Read-only - makes no changes.
 * Deliberately excludes status=live, since a live match legitimately has
 * a partial score while still in progress.
 */
class DiagnoseStuckScheduledMatches extends Command
{
    protected $signature = 'diagnose:stuck-scheduled-matches';

    protected $description = 'List scheduled matches that already have a score recorded';

    public function handle(): int
    {
        $matches = MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
            ->where('status', 'scheduled')
            ->whereNotNull('home_score')
            ->whereNotNull('away_score')
            ->orderByDesc('kickoff_at')
            ->get();

        if ($matches->isEmpty()) {
            $this->info('None found - every scheduled match has no score recorded, as expected.');

            return self::SUCCESS;
        }

        foreach ($matches as $m) {
            $this->line("{$m->id} | {$m->league->name} | {$m->kickoff_at} | {$m->homeTeam->name} {$m->home_score}-{$m->away_score} {$m->awayTeam->name}");
        }

        $this->warn("Found {$matches->count()} match(es) stuck as scheduled with a real score.");

        return self::SUCCESS;
    }
}
