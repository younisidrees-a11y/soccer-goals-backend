<?php

namespace App\Console\Commands;

use App\Models\MatchFixture;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * One-off cleanup: no database-level unique constraint ever existed on
 * api_football_fixture_id, so a race between an automatically-scheduled
 * sync and a manually-run one (both possible during this project's
 * various catch-up passes) could let two processes both fail to find an
 * existing row and both insert - confirmed live: 16 distinct fixtures,
 * 1,076 duplicate rows total, one match (Hibernian vs Hearts) alone had
 * 75 copies of itself. This consolidates each group of duplicates down
 * to one real row before add_unique_constraint_to_api_football_fixture_id
 * can apply (a unique index can't be added while duplicates exist).
 *
 * For each api_football_fixture_id with more than one row: keeps the
 * one with the most real data filled in (stats/events/lineups/motm/
 * match_report - whichever sync happened to enrich first), falling back
 * to the most recently updated row when none of the duplicates have any
 * enrichment yet. Deletes the rest. Makes no attempt to merge partial
 * data across duplicates - one of them already has the real answer for
 * any given field, picking the most-complete row is simpler and no less
 * accurate than trying to splice fields together.
 */
class DedupeApiFootballFixtures extends Command
{
    protected $signature = 'matches:dedupe-api-fixtures {--dry-run : List what would be removed without deleting anything}';

    protected $description = 'Consolidate duplicate match rows that share the same api_football_fixture_id';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $duplicateIds = MatchFixture::whereNotNull('api_football_fixture_id')
            ->select('api_football_fixture_id')
            ->groupBy('api_football_fixture_id')
            ->havingRaw('count(*) > 1')
            ->pluck('api_football_fixture_id');

        if ($duplicateIds->isEmpty()) {
            $this->info('No duplicates found - every api_football_fixture_id is unique.');

            return self::SUCCESS;
        }

        $this->info("Found {$duplicateIds->count()} fixture(s) with duplicate rows.".($dryRun ? ' (dry run)' : ''));

        $totalRemoved = 0;

        foreach ($duplicateIds as $fixtureId) {
            $rows = MatchFixture::with(['homeTeam', 'awayTeam'])
                ->where('api_football_fixture_id', $fixtureId)
                ->get()
                ->sortByDesc(function (MatchFixture $m) {
                    // Real enrichment present counts far more than recency -
                    // never throw away the one row that actually has real
                    // stats/events/lineups/motm/report just because a
                    // later, still-empty duplicate has a newer timestamp.
                    $completeness = (int) ($m->stats !== null)
                        + (int) ($m->events !== null)
                        + (int) ($m->lineups !== null)
                        + (int) ($m->motm !== null)
                        + (int) ($m->match_report !== null);

                    return $completeness * 1000000 + $m->updated_at->timestamp;
                });

            $keep = $rows->first();
            $remove = $rows->slice(1);

            $label = "{$keep->homeTeam->name} vs {$keep->awayTeam->name} (fixture {$fixtureId}): keeping #{$keep->id}, removing ".$remove->pluck('id')->implode(', ');
            $this->line($label);
            Log::info('matches:dedupe-api-fixtures - '.$label);

            if (! $dryRun) {
                MatchFixture::whereIn('id', $remove->pluck('id'))->delete();
            }

            $totalRemoved += $remove->count();
        }

        $this->info(($dryRun ? 'Would remove ' : 'Removed ')."{$totalRemoved} duplicate row(s) across {$duplicateIds->count()} fixture(s).");

        return self::SUCCESS;
    }
}
