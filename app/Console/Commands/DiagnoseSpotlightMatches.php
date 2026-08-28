<?php

namespace App\Console\Commands;

use App\Models\MatchFixture;
use App\Models\NewsArticle;
use Illuminate\Console\Command;

/**
 * One-off diagnostic: for every published match-report article, list any
 * MatchFixture rows for that article's team+league within +/-5 days of
 * publication, so we can see whether the article's claimed result
 * actually exists in the real match data. Read-only - makes no changes.
 * Safe to delete once the spotlight data question is settled.
 */
class DiagnoseSpotlightMatches extends Command
{
    protected $signature = 'diagnose:spotlight-matches';

    protected $description = 'List candidate real matches for every published match-report article';

    public function handle(): int
    {
        $articles = NewsArticle::published()
            ->where('category', 'match-report')
            ->whereNotNull('team_id')
            ->whereNotNull('league_id')
            ->orderByDesc('published_at')
            ->get();

        if ($articles->isEmpty()) {
            $this->info('No published match-report articles with team_id/league_id set.');

            return self::SUCCESS;
        }

        foreach ($articles as $a) {
            $this->line("--- Article {$a->id}: {$a->title} ---");
            $this->line('team='.($a->team->name ?? 'NULL')." league_id={$a->league_id} published_at={$a->published_at}");

            if (! $a->published_at) {
                $this->warn('  no published_at - cannot search a date window');

                continue;
            }

            $matches = MatchFixture::with(['homeTeam', 'awayTeam'])
                ->where('league_id', $a->league_id)
                ->where(fn ($q) => $q->where('home_team_id', $a->team_id)->orWhere('away_team_id', $a->team_id))
                ->whereBetween('kickoff_at', [
                    $a->published_at->copy()->subDays(5),
                    $a->published_at->copy()->addDays(5),
                ])
                ->get();

            if ($matches->isEmpty()) {
                $this->warn('  NO candidate matches found within +/-5 days');

                continue;
            }

            foreach ($matches as $m) {
                $this->line("  {$m->id} | {$m->status} | published=".($m->is_published ? 'Y' : 'N')." | {$m->kickoff_at} | {$m->homeTeam->name} {$m->home_score}-{$m->away_score} {$m->awayTeam->name}");
            }
        }

        return self::SUCCESS;
    }
}
