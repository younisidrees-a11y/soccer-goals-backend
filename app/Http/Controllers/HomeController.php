<?php

namespace App\Http\Controllers;

use App\Models\League;
use App\Models\MatchFixture;
use App\Models\NewsArticle;
use App\Models\Standing;

class HomeController extends Controller
{
    public function index()
    {
        $todaysMatches = MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
            ->published()
            ->whereDate('kickoff_at', now()->toDateString())
            ->orderBy('kickoff_at')
            ->take(4)
            ->get();

        $latestNews = NewsArticle::with(['league', 'team'])
            ->published()
            ->orderByDesc('published_at')
            ->take(6)
            ->get();

        $heroArticles = NewsArticle::published()->pinned()->orderByDesc('published_at')->take(4)->get();

        if ($heroArticles->count() < 4) {
            $heroArticles = $heroArticles->concat(
                NewsArticle::published()
                    ->whereNotIn('id', $heroArticles->pluck('id'))
                    ->orderByDesc('published_at')
                    ->take(4 - $heroArticles->count())
                    ->get()
            );
        }

        $upcomingFixtures = MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
            ->published()
            ->where('status', 'scheduled')
            ->orderBy('kickoff_at')
            ->take(5)
            ->get();

        $recentResults = MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
            ->published()
            ->where('status', 'final')
            ->orderByDesc('kickoff_at')
            ->take(5)
            ->get();

        $leagueTables = League::published()
            ->orderBy('name')
            ->get()
            ->map(fn (League $l) => [
                'league' => $l,
                'standings' => Standing::with('team')->where('league_id', $l->id)->orderBy('position')->get(),
            ])
            ->filter(fn ($t) => $t['standings']->isNotEmpty())
            ->values()
            ->map(fn ($t) => $t + ['hasStarted' => $t['standings']->sum('played') > 0]);

        // Alphabetical order can easily land on a league whose season hasn't
        // kicked off yet (every row still 0-0-0-0-0), which is a useless
        // default view. Default to the first league that actually has
        // played matches; only fall back to index 0 if literally none do.
        $defaultLeagueIndex = $leagueTables->search(fn ($t) => $t['hasStarted']);
        $defaultLeagueIndex = $defaultLeagueIndex === false ? 0 : $defaultLeagueIndex;

        return view('home', compact(
            'todaysMatches',
            'latestNews',
            'heroArticles',
            'upcomingFixtures',
            'recentResults',
            'leagueTables',
            'defaultLeagueIndex',
        ));
    }
}
