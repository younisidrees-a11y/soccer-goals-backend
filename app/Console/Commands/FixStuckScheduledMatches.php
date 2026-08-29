<?php

namespace App\Console\Commands;

use App\Models\MatchFixture;
use Illuminate\Console\Command;

/**
 * One-off fix, companion to diagnose:stuck-scheduled-matches: flips
 * status to "final" for any match that's still "scheduled" but already
 * has both scores recorded and a kickoff_at in the past - an editor
 * entered the result without flipping status. Deliberately narrow:
 * only touches scheduled matches (never live, which legitimately has a
 * partial score while still in progress) whose kickoff has already
 * passed (never a future scheduled match). Idempotent - re-running
 * finds nothing once fixed.
 */
class FixStuckScheduledMatches extends Command
{
    protected $signature = 'fix:stuck-scheduled-matches';

    protected $description = 'Flip scheduled matches with a recorded score and a past kickoff to final';

    public function handle(): int
    {
        $matches = MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
            ->where('status', 'scheduled')
            ->whereNotNull('home_score')
            ->whereNotNull('away_score')
            ->where('kickoff_at', '<', now())
            ->get();

        if ($matches->isEmpty()) {
            $this->info('Nothing to fix.');

            return self::SUCCESS;
        }

        foreach ($matches as $m) {
            $m->update(['status' => 'final']);
            $this->line("Fixed {$m->id}: {$m->homeTeam->name} {$m->home_score}-{$m->away_score} {$m->awayTeam->name} -> final");
        }

        $this->info("Fixed {$matches->count()} match(es).");

        return self::SUCCESS;
    }
}
