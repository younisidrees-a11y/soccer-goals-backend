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
        $premierLeague = League::where('slug', 'premier-league')->first();
        $laLiga = League::where('slug', 'la-liga')->first();

        $todaysMatches = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->where('league_id', $premierLeague->id)
            ->where('matchday', 1)
            ->orderBy('kickoff_at')
            ->take(4)
            ->get();

        $latestNews = NewsArticle::with(['league', 'team'])
            ->published()
            ->orderByDesc('published_at')
            ->take(6)
            ->get();

        $upcomingFixtures = MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
            ->where('status', 'scheduled')
            ->orderBy('kickoff_at')
            ->take(5)
            ->get();

        $recentResults = MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
            ->where('status', 'final')
            ->orderByDesc('kickoff_at')
            ->take(5)
            ->get();

        $plStandings = Standing::with('team')
            ->where('league_id', $premierLeague->id)
            ->orderBy('position')
            ->get();

        $laLigaStandings = Standing::with('team')
            ->where('league_id', $laLiga->id)
            ->orderBy('position')
            ->get();

        $tickerMatches = MatchFixture::with(['homeTeam', 'awayTeam'])
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
