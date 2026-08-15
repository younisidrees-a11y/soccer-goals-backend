<?php

namespace App\Http\Controllers;

use App\Models\League;
use App\Models\MatchFixture;

class FixtureController extends Controller
{
    public function index()
    {
        $leagues = League::published()
            ->withCount(['teams' => fn ($q) => $q->published()])
            ->orderBy('name')
            ->get();

        return view('fixtures.index', compact('leagues'));
    }

    public function show(string $slug)
    {
        $league = League::published()->where('slug', $slug)->firstOrFail();

        $fixtures = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->published()
            ->where('league_id', $league->id)
            ->where('status', '!=', 'final')
            ->orderBy('kickoff_at')
            ->paginate(20);

        $tickerMatches = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->published()
            ->where('status', 'final')
            ->orderByDesc('kickoff_at')
            ->take(7)
            ->get();

        return view('fixtures.show', compact('league', 'fixtures', 'tickerMatches'));
    }
}
