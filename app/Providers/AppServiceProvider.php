<?php

namespace App\Providers;

use App\Models\MatchFixture;
use App\Models\NewsArticle;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\URL;
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
        // Belt-and-braces alongside the host-canonicalization redirect in
        // public/.htaccess: every route()/url() call - canonical tags, the
        // sitemap, OG tags - always builds from the real domain, never
        // whatever Host header a request happened to arrive with. Without
        // this, a request that reached Laravel under a different hostname
        // (before a redirect propagates, a raw IP hit, etc.) would generate
        // self-referencing canonical URLs for that wrong host instead of
        // pointing back to the one true domain. Deliberately NOT also
        // calling forceScheme('https') here - APP_URL already encodes the
        // right scheme per environment (https in production, plain http
        // for local dev on :8000), and forceScheme('https') on top of that
        // breaks asset() locally by forcing CSS/JS requests to HTTPS
        // against a server that only speaks HTTP.
        URL::forceRootUrl(config('app.url'));

        View::composer('layouts.site', function ($view) {
            $view->with('hasLiveMatch', MatchFixture::where('status', 'live')->where('is_published', true)->exists());
            $view->with('siteSettings', SiteSetting::current());
            $view->with('tickerMatches', $this->buildTickerMatches());
            // Powers the "Just In" column of the News mega menu, which is
            // global (every page renders this layout) - previously this
            // was three headlines hand-typed straight into the Blade file
            // and frozen in time. Small limit since it's a nav dropdown,
            // not a feed.
            $view->with('megaLatestNews', NewsArticle::published()->latest('published_at')->take(3)->get());
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
