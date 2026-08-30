<?php

namespace App\Providers;

use App\Models\League;
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
            $liveMatchCount = MatchFixture::where('status', 'live')->where('is_published', true)->count();
            $view->with('hasLiveMatch', $liveMatchCount > 0);
            $view->with('liveMatchCount', $liveMatchCount);
            $view->with('siteSettings', SiteSetting::current());
            $view->with('tickerMatches', $this->buildTickerMatches());
            // Powers the "Just In" column of the News mega menu, which is
            // global (every page renders this layout) - previously this
            // was three headlines hand-typed straight into the Blade file
            // and frozen in time. Small limit since it's a nav dropdown,
            // not a feed.
            $view->with('megaLatestNews', NewsArticle::published()->latest('published_at')->take(3)->get());
            $view->with('footerLeagues', $this->buildFooterLeagues());
        });
    }

    /**
     * Footer's "Leagues & Teams" sitemap - every real published team for
     * the five biggest leagues, so it's a genuine index rather than a
     * flat list of 13 league names with no way to reach an individual
     * club from the footer.
     */
    private function buildFooterLeagues()
    {
        return League::published()
            ->whereIn('slug', ['premier-league', 'la-liga', 'bundesliga', 'serie-a', 'saudi-pro-league'])
            ->with(['teams' => fn ($q) => $q->published()->orderBy('name')])
            ->get()
            ->sortBy(fn ($l) => array_search($l->slug, ['premier-league', 'la-liga', 'bundesliga', 'serie-a', 'saudi-pro-league']))
            ->values();
    }

    /**
     * The header ticker is styled and labelled as live coverage, so it
     * needs to actually be that: every one of today's real matches -
     * live, still to kick off, or already finished - in kickoff order,
     * with live matches pulled to the front so anything in progress is
     * visible without scrolling. Previously this queried "any recent
     * final" and "any upcoming fixture" with no date scoping at all, so
     * on a normal day the ticker was half yesterday's results and only
     * showed 6 of the day's fixtures even though scrolling was possible -
     * "today's matches" and "what the ticker contains" were different
     * sets. Only pads in matches from other days when today itself is
     * too thin to fill the strip.
     */
    private function buildTickerMatches()
    {
        // A real three-tier priority, not just "live vs. everything
        // else": that only pass sorted by kickoff_at first, then pulled
        // live matches to the front, but left scheduled and final
        // matches interleaved by kickoff time within the "not live"
        // group - and a finished match almost always kicked off earlier
        // in the day than one still upcoming, so finals quietly ended up
        // leading the ticker with upcoming matches pushed after them.
        $statusRank = fn (MatchFixture $m) => match ($m->status) {
            'live' => 0,
            'scheduled' => 1,
            default => 2, // final and anything else trail
        };

        $today = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->published()
            ->whereDate('kickoff_at', now()->toDateString())
            ->get()
            ->sort(function (MatchFixture $a, MatchFixture $b) use ($statusRank) {
                $rankA = $statusRank($a);
                $rankB = $statusRank($b);

                if ($rankA !== $rankB) {
                    return $rankA <=> $rankB;
                }

                // Within a tier: live/upcoming soonest-first, but an
                // already-finished match reads better newest-first - the
                // most recent result is the one worth seeing first.
                return $rankA === 2
                    ? $b->kickoff_at <=> $a->kickoff_at
                    : $a->kickoff_at <=> $b->kickoff_at;
            })
            ->values();

        $minimum = 8;

        if ($today->count() >= $minimum) {
            return $today->take(24);
        }

        $remaining = $minimum - $today->count();
        $recentCount = (int) ceil($remaining / 2);
        $upcomingCount = $remaining - $recentCount;
        $excludeIds = $today->pluck('id');

        $recent = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->published()
            ->where('status', 'final')
            ->whereNotIn('id', $excludeIds)
            ->orderByDesc('kickoff_at')
            ->limit($recentCount)
            ->get();

        $upcoming = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->published()
            ->where('status', 'scheduled')
            ->whereNotIn('id', $excludeIds)
            ->orderBy('kickoff_at')
            ->limit($upcomingCount)
            ->get();

        return $today->concat($upcoming)->concat($recent);
    }
}
