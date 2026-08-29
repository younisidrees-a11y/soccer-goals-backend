<?php

namespace App\Console\Commands;

use App\Models\MatchFixture;
use App\Models\Team;
use App\Services\AiClubHistoryWriter;
use App\Services\AiFactChecker;
use Illuminate\Console\Command;

/**
 * Backfill companion to content:audit for the two smaller categories it
 * flags: a live-commentary line claiming an event with no matching real
 * event nearby, and a club history/manager bio still carrying a banned
 * AI-tone word. Both are handled differently from match reports on
 * purpose:
 * - A commentary line can't be safely regenerated after the fact (that
 *   would need reconstructing the exact match-state context it was
 *   written from), so a flagged line is simply removed rather than
 *   guessed at.
 * - A club bio IS safely regenerable, since AiClubHistoryWriter only ever
 *   uses admin-verified facts already stored on the team - regenerating
 *   it can't introduce a new invented fact, only better prose.
 */
class RepairMiscContent extends Command
{
    protected $signature = 'content:repair-misc {--dry-run : List what would change without saving anything}';

    protected $description = 'Remove phantom commentary lines and regenerate club bios still carrying a banned AI-tone word';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->repairCommentary($dryRun);
        $this->repairClubContent($dryRun);

        return self::SUCCESS;
    }

    private function repairCommentary(bool $dryRun): void
    {
        $this->info('=== Live commentary ===');
        $removed = 0;

        MatchFixture::with(['homeTeam', 'awayTeam'])
            ->whereNotNull('commentary')
            ->chunkById(100, function ($matches) use (&$removed, $dryRun) {
                foreach ($matches as $match) {
                    $lines = $match->commentary ?? [];

                    if (empty($lines)) {
                        continue;
                    }

                    $realEventMinutes = collect($match->events ?? [])->pluck('minute')->map(fn ($m) => (int) $m)->all();

                    $kept = collect($lines)->reject(function ($line) use ($realEventMinutes) {
                        $text = $line['text'] ?? '';
                        $minute = (int) ($line['minute'] ?? -1);
                        $hasNearbyRealEvent = collect($realEventMinutes)->contains(fn ($m) => abs($m - $minute) <= 1);

                        return $text !== '' && ! $hasNearbyRealEvent && AiFactChecker::containsUnverifiedEventClaim($text);
                    });

                    if ($kept->count() === count($lines)) {
                        continue;
                    }

                    $droppedCount = count($lines) - $kept->count();
                    $removed += $droppedCount;
                    $this->line(($dryRun ? 'Would remove ' : 'Removed ')."{$droppedCount} line(s) from match #{$match->id} ({$match->homeTeam->name} vs {$match->awayTeam->name})");

                    if (! $dryRun) {
                        $match->update(['commentary' => $kept->values()->all()]);
                    }
                }
            });

        $this->info($dryRun ? "Would remove {$removed} phantom-event line(s)." : "Removed {$removed} phantom-event line(s).");
    }

    private function repairClubContent(bool $dryRun): void
    {
        $this->line('');
        $this->info('=== Club history & manager bios ===');
        $repaired = 0;
        $failed = 0;

        Team::where(fn ($q) => $q->whereNotNull('history_essay')->orWhereNotNull('manager_bio'))
            ->chunkById(200, function ($teams) use (&$repaired, &$failed, $dryRun) {
                foreach ($teams as $team) {
                    $historyBad = $team->history_essay && AiFactChecker::findBannedTone($team->history_essay);
                    $bioBad = $team->manager_bio && AiFactChecker::findBannedTone($team->manager_bio);

                    if (! $historyBad && ! $bioBad) {
                        continue;
                    }

                    if ($dryRun) {
                        $this->line("Would regenerate for {$team->name}: ".implode(', ', array_filter([$historyBad ? 'history_essay' : null, $bioBad ? 'manager_bio' : null])));

                        continue;
                    }

                    $writer = app(AiClubHistoryWriter::class);
                    $ok = true;

                    if ($historyBad) {
                        $new = $writer->write($team);

                        if ($new && ! AiFactChecker::findBannedTone($new)) {
                            $team->history_essay = $new;
                        } else {
                            $ok = false;
                        }
                    }

                    if ($bioBad && $team->manager) {
                        $new = $writer->writeManagerBio($team);

                        if ($new && ! AiFactChecker::findBannedTone($new)) {
                            $team->manager_bio = $new;
                        } else {
                            $ok = false;
                        }
                    }

                    $team->save();

                    if ($ok) {
                        $repaired++;
                        $this->line("Repaired: {$team->name}");
                    } else {
                        $failed++;
                        $this->warn("Regeneration still had a banned word or failed for {$team->name} - left as-is, needs a manual look.");
                    }
                }
            });

        $this->info($dryRun ? 'Dry run complete.' : "Repaired {$repaired} team(s), {$failed} need a manual look.");
    }
}
