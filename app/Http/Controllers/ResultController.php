<?php

namespace App\Http\Controllers;

use App\Models\League;
use App\Models\MatchFixture;

class ResultController extends Controller
{
    public function index()
    {
        $leagues = League::withCount('teams')->orderBy('name')->get();

        return view('results.index', compact('leagues'));
    }

    public function show(string $slug)
    {
        $league = League::where('slug', $slug)->firstOrFail();

        $results = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->where('league_id', $league->id)
            ->where('status', 'final')
            ->orderByDesc('kickoff_at')
            ->paginate(20);

        $tickerMatches = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->where('status', 'final')
            ->orderByDesc('kickoff_at')
            ->take(7)
            ->get();

        return view('results.show', compact('league', 'results', 'tickerMatches'));
    }
}
