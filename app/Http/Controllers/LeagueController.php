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

        $upcomingFixtures = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->published()
            ->where('league_id', $league->id)
            ->where('status', 'scheduled')
            ->orderBy('kickoff_at')
            ->take(10)
            ->get();

        $latestResults = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->published()
            ->where('league_id', $league->id)
            ->where('status', 'final')
            ->orderByDesc('kickoff_at')
            ->take(5)
            ->get();

        $nextFixture = MatchFixture::published()
            ->where('league_id', $league->id)
            ->where('status', 'scheduled')
            ->orderBy('kickoff_at')
            ->first();

        $achievements = $this->buildAchievements($league->id, $standings);

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
            'upcomingFixtures',
            'latestResults',
            'nextFixture',
            'topScorer',
            'matchesPlayed',
            'matchesTotal',
            'achievements',
            'news',
        ));
    }

    /**
     * Real, computed superlatives rather than a canned "top 5" stat
     * comparison - every figure here traces back to an actual standings
     * row or match result, so there's nothing to keep in sync by hand and
     * nothing that can go stale or contradict the table above it.
     *
     * Returns null for any award a league can't yet support (e.g. no
     * final matches played), so the view can skip it gracefully instead
     * of showing a hollow "0-0" achievement.
     */
    private function buildAchievements(int $leagueId, $standings): array
    {
        $played = $standings->filter(fn ($s) => $s->played > 0);

        $bestAttack = $played->sortByDesc('goals_for')->first();
        $bestDefense = $played->sortBy('goals_against')->first();
        $mostWins = $played->sortByDesc('won')->first();

        $finalMatches = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->published()
            ->where('league_id', $leagueId)
            ->where('status', 'final')
            ->orderBy('kickoff_at')
            ->get();

        $biggestWin = $finalMatches
            ->filter(fn ($m) => $m->home_score !== $m->away_score)
            ->sortByDesc(fn ($m) => abs($m->home_score - $m->away_score))
            ->first();

        // Longest unbeaten run: walk every team's results in chronological
        // order, tracking the current streak of wins/draws and the best
        // one seen so far. A loss resets the counter to zero.
        $streaks = [];
        foreach ($finalMatches as $m) {
            foreach ([
                ['team' => $m->homeTeam, 'gf' => $m->home_score, 'ga' => $m->away_score],
                ['team' => $m->awayTeam, 'gf' => $m->away_score, 'ga' => $m->home_score],
            ] as $side) {
                $teamId = $side['team']->id;
                $streaks[$teamId] ??= ['team' => $side['team'], 'current' => 0, 'best' => 0];

                $streaks[$teamId]['current'] = $side['gf'] >= $side['ga'] ? $streaks[$teamId]['current'] + 1 : 0;
                $streaks[$teamId]['best'] = max($streaks[$teamId]['best'], $streaks[$teamId]['current']);
            }
        }
        $longestUnbeaten = collect($streaks)->sortByDesc('best')->first();

        return [
            'bestAttack' => $bestAttack,
            'bestDefense' => $bestDefense,
            'mostWins' => $mostWins,
            'biggestWin' => $biggestWin,
            'longestUnbeaten' => ($longestUnbeaten && $longestUnbeaten['best'] > 0) ? $longestUnbeaten : null,
        ];
    }
}
