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
        $premierLeague = League::published()->where('slug', 'premier-league')->first();
        $laLiga = League::published()->where('slug', 'la-liga')->first();

        $todaysMatches = $premierLeague
            ? MatchFixture::with(['homeTeam', 'awayTeam'])
                ->published()
                ->where('league_id', $premierLeague->id)
                ->where('matchday', 1)
                ->orderBy('kickoff_at')
                ->take(4)
                ->get()
            : collect();

        $latestNews = NewsArticle::with(['league', 'team'])
            ->published()
            ->orderByDesc('published_at')
            ->take(6)
            ->get();

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

        $plStandings = $premierLeague
            ? Standing::with('team')->where('league_id', $premierLeague->id)->orderBy('position')->get()
            : collect();

        $laLigaStandings = $laLiga
            ? Standing::with('team')->where('league_id', $laLiga->id)->orderBy('position')->get()
            : collect();

        $tickerMatches = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->published()
            ->where('status', 'final')
            ->orderByDesc('kickoff_at')
            ->take(7)
            ->get();

        return view('home', compact(
            'todaysMatches',
            'latestNews',
            'upcomingFixtures',
            'recentResults',
            'plStandings',
            'laLigaStandings',
            'tickerMatches',
        ));
    }
}
