<?php

namespace App\Http\Controllers;

use App\Models\League;
use App\Models\MatchFixture;
use App\Models\NewsArticle;
use App\Models\Player;
use App\Models\Standing;
use Illuminate\Http\Request;

class LeagueController extends Controller
{
    public function index()
    {
        $leagues = League::published()
            ->withCount(['teams' => fn ($q) => $q->published()])
            ->with(['teams' => fn ($q) => $q->published()->orderBy('name')])
            ->orderBy('name')
            ->get();

        $todaysMatches = MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
            ->published()
            ->whereDate('kickoff_at', now()->toDateString())
            ->orderBy('kickoff_at')
            ->get();

        return view('leagues.index', compact('leagues', 'todaysMatches'));
    }

    public function show(Request $request, string $slug)
    {
        $league = League::published()
            ->withCount(['teams' => fn ($q) => $q->published()])
            ->where('slug', $slug)
            ->firstOrFail();

        $standings = Standing::with('team')
            ->where('league_id', $league->id)
            ->orderBy('position')
            ->get();

        $leader = $standings->first();

        $latestMatchday = MatchFixture::published()
            ->where('league_id', $league->id)
            ->where('status', 'final')
            ->max('matchday') ?? 1;

        $matchdayOneResults = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->published()
            ->where('league_id', $league->id)
            ->where('matchday', $latestMatchday)
            ->orderBy('kickoff_at')
            ->get();

        $nextFixture = MatchFixture::published()
            ->where('league_id', $league->id)
            ->where('status', 'scheduled')
            ->orderBy('kickoff_at')
            ->first();

        $topScorer = Player::with('team')
            ->whereHas('team', fn ($q) => $q->where('league_id', $league->id)->where('is_published', true))
            ->where('goals', '>', 0)
            ->orderByDesc('goals')
            ->orderByDesc('assists')
            ->first();

        $matchesPlayed = MatchFixture::published()->where('league_id', $league->id)->where('status', 'final')->count();
        $matchesTotal = MatchFixture::published()->where('league_id', $league->id)->count();

        $news = NewsArticle::with(['team'])
            ->published()
            ->where('league_id', $league->id)
            ->orderByDesc('published_at')
            ->get();

        return view('leagues.show', compact(
            'league',
            'standings',
            'leader',
            'matchdayOneResults',
            'nextFixture',
            'topScorer',
            'matchesPlayed',
            'matchesTotal',
            'news',
        ));
    }
}
