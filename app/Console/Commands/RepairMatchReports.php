<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\GeneratesMatchContent;
use App\Models\MatchFixture;
use App\Services\AiFactChecker;
use Illuminate\Console\Command;

/**
 * Backfill companion to content:audit: re-writes match_report only for
 * matches that actually fail a check (wrong score, wrong day, banned
 * AI-tone word) - everything already clean is left untouched. Reuses
 * GeneratesMatchContent::generateReport(), the exact same validated
 * regenerate-or-fall-back-to-template logic the live results pipeline
 * already runs on every new match, so a repaired report is held to
 * identical standards as a freshly written one: real score verified,
 * real day of the week verified/corrected, no banned tone - or it falls
 * back to the always-consistent template bank instead.
 */
class RepairMatchReports extends Command
{
    use GeneratesMatchContent;

    protected $signature = 'content:repair-match-reports {--limit=1000 : Maximum matches to repair in this run} {--dry-run : List what would be repaired without saving anything}';

    protected $description = 'Regenerate match_report only for matches that fail the score/day/tone checks';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');

        $candidates = MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
            ->where('status', 'final')
            ->where('is_published', true)
            ->whereNotNull('match_report')
            ->get()
            ->filter(fn (MatchFixture $match) => $this->needsRepair($match))
            ->take($limit);

        if ($candidates->isEmpty()) {
            $this->info('Nothing to repair - every match report already passes score/day/tone checks.');

            return self::SUCCESS;
        }

        $this->info("Found {$candidates->count()} match report(s) needing repair.".($dryRun ? ' (dry run - no changes will be saved)' : ''));

        $repaired = 0;
        $stillFailing = 0;

        foreach ($candidates as $match) {
            $label = "#{$match->id} {$match->homeTeam->name} {$match->home_score}-{$match->away_score} {$match->awayTeam->name} ({$match->league->name}, {$match->kickoff_at->format('j M Y')})";

            if ($dryRun) {
                $this->line("Would repair: {$label}");

                continue;
            }

            $newReport = $this->generateReport($match, $match->home_score, $match->away_score, $match->stats);
            $match->update(['match_report' => $newReport]);

            if ($this->needsRepair($match->fresh())) {
                $stillFailing++;
                $this->warn("Repaired but still flagged (unexpected): {$label}");
            } else {
                $repaired++;
                $this->line("Repaired: {$label}");
            }
        }

        if ($dryRun) {
            return self::SUCCESS;
        }

        $this->info("Repaired {$repaired} match report(s).".($stillFailing ? " {$stillFailing} still flagged after repair - check manually." : ''));

        return self::SUCCESS;
    }

    private function needsRepair(MatchFixture $match): bool
    {
        $report = $match->match_report;

        if (blank($report)) {
            return false;
        }

        if (! AiFactChecker::containsScore($report, $match->home_score, $match->away_score)) {
            return true;
        }

        if (AiFactChecker::findBannedTone($report)) {
            return true;
        }

        $realDay = $match->kickoff_at->format('l');

        foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day) {
            if ($day !== $realDay && preg_match('/\b'.$day.'\b/', $report)) {
                return true;
            }
        }

        return false;
    }
}
