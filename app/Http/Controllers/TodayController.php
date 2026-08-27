<?php

namespace App\Http\Controllers;

use App\Models\MatchFixture;

class TodayController extends Controller
{
    public function index()
    {
        $matches = MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
            ->published()
            ->whereDate('kickoff_at', now()->toDateString())
            ->orderBy('kickoff_at')
            ->paginate(20);

        return view('today.index', compact('matches'));
    }
}
