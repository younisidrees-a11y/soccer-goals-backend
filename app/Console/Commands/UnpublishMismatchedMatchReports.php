<?php

namespace App\Console\Commands;

use App\Models\NewsArticle;
use Illuminate\Console\Command;

/**
 * One-off content fix: five published match-report articles describe
 * results that don't match any real MatchFixture (confirmed via
 * diagnose:spotlight-matches - e.g. "Barcelona Routs Villarreal 4-1" but
 * Villarreal's only real match in that window is Atletico Madrid 2-2
 * Villarreal). Sends them back to pending_review and clears their
 * published_at/reviewed_at/reviewed_by so they're off the live site
 * until someone rewrites or verifies them against real results.
 * Idempotent - matches on id, safe to run more than once.
 */
class UnpublishMismatchedMatchReports extends Command
{
    protected $signature = 'content:unpublish-mismatched-match-reports';

    protected $description = 'Send match-report articles with no matching real result back to pending_review';

    /** IDs found by diagnose:spotlight-matches to have no corresponding real MatchFixture. */
    private const ARTICLE_IDS = [27, 9, 5, 4, 28];

    public function handle(): int
    {
        foreach (self::ARTICLE_IDS as $id) {
            $article = NewsArticle::find($id);

            if (! $article) {
                $this->warn("Skipped {$id}: article not found.");

                continue;
            }

            $article->update([
                'status' => 'pending_review',
                'published_at' => null,
                'reviewed_at' => null,
                'reviewed_by' => null,
            ]);

            $this->info("Unpublished {$id}: {$article->title}");
        }

        return self::SUCCESS;
    }
}
