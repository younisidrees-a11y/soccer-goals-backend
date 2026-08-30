<?php

namespace App\Http\Controllers;

use App\Models\League;
use App\Models\MatchFixture;

class ResultController extends Controller
{
    /** Leagues pinned to the front of the results league picker, in this exact order. */
    private const PINNED_FIRST = ['premier-league', 'la-liga', 'saudi-pro-league'];

    /** Leagues pushed to the back, after everything else. */
    private const PINNED_LAST = ['bundesliga'];

    public function index()
    {
        $leagues = League::published()
            ->withCount(['teams' => fn ($q) => $q->published()])
            ->orderBy('name')
            ->get()
            ->sortBy(function (League $league) {
                $firstPos = array_search($league->slug, self::PINNED_FIRST, true);

                if ($firstPos !== false) {
                    return $firstPos;
                }

                if (in_array($league->slug, self::PINNED_LAST, true)) {
                    return 100;
                }

                // Stable sort (PHP 8+) keeps the rest in the alphabetical
                // order they already arrived in from the query above.
                return 50;
            })
            ->values();

        return view('results.index', compact('leagues'));
    }

    public function show(string $slug)
    {
        $league = League::published()->where('slug', $slug)->firstOrFail();

        $results = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->published()
            ->where('league_id', $league->id)
            ->where('status', 'final')
            ->orderByDesc('kickoff_at')
            ->paginate(20);

        return view('results.show', compact('league', 'results'));
    }
}
