<?php

namespace App\Providers;

use App\Models\MatchFixture;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.site', function ($view) {
            $view->with('hasLiveMatch', MatchFixture::where('status', 'live')->where('is_published', true)->exists());
            $view->with('siteSettings', SiteSetting::current());
            $view->with('currentMatchday', MatchFixture::published()->where('status', 'final')->max('matchday') ?? 1);
            $view->with('tickerMatches', $this->buildTickerMatches());
        });
    }

    /**
     * The header ticker is styled and labelled as live coverage, so it
     * needs to actually be that: any match in progress right now, padded
     * out with the most recent final scores and the soonest upcoming
     * fixtures so it's never empty on a quiet day. Previously every
     * controller copy-pasted an identical "last 7 final matches" query -
     * this is now the single source of truth.
     */
    private function buildTickerMatches()
    {
        $live = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->published()
            ->where('status', 'live')
            ->orderBy('kickoff_at')
            ->get();

        $remaining = max(0, 12 - $live->count());
        $recentCount = (int) ceil($remaining / 2);
        $upcomingCount = $remaining - $recentCount;

        $recent = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->published()
            ->where('status', 'final')
            ->orderByDesc('kickoff_at')
            ->limit($recentCount)
            ->get();

        $upcoming = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->published()
            ->where('status', 'scheduled')
            ->orderBy('kickoff_at')
            ->limit($upcomingCount)
            ->get();

        return $live->concat($recent)->concat($upcoming);
    }
}
