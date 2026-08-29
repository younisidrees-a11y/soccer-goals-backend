<?php

namespace App\Console\Commands;

use App\Models\NewsArticle;
use Illuminate\Console\Command;

/**
 * One-off fix: any NewsArticle that was marked status=published directly on
 * the form (bypassing the approve() action) before the NewsArticle::booted()
 * saving-hook existed ended up with a null published_at, which pushes it
 * out of homepage ordering and out of Match Spotlight eligibility. Backfills
 * published_at from created_at (the closest real proxy for when a
 * create-as-published article actually went live). Idempotent - only
 * touches rows that are still null, safe to run more than once.
 */
class BackfillMissingPublishedAt extends Command
{
    protected $signature = 'fix:backfill-published-at';

    protected $description = 'Set published_at on published articles that are missing it';

    public function handle(): int
    {
        $articles = NewsArticle::where('status', 'published')
            ->whereNull('published_at')
            ->get();

        if ($articles->isEmpty()) {
            $this->info('Nothing to fix - every published article already has published_at set.');

            return self::SUCCESS;
        }

        foreach ($articles as $article) {
            $article->published_at = $article->created_at;
            $article->save();
            $this->line("Fixed article {$article->id}: {$article->title} -> published_at={$article->published_at}");
        }

        $this->info("Backfilled {$articles->count()} article(s).");

        return self::SUCCESS;
    }
}
