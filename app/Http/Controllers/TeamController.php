<?php

namespace App\Http\Controllers;

use App\Models\MatchFixture;
use App\Models\NewsArticle;
use App\Models\Player;
use App\Models\Standing;
use App\Models\Team;

class TeamController extends Controller
{
    public function show(string $slug)
    {
        $team = Team::with('league')->published()->where('slug', $slug)->firstOrFail();

        $leagueStandings = Standing::with('team')
            ->where('league_id', $team->league_id)
            ->orderBy('position')
            ->get();

        $standing = $leagueStandings->firstWhere('team_id', $team->id);

        $upcomingMatches = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->published()
            ->where(fn ($q) => $q->where('home_team_id', $team->id)->orWhere('away_team_id', $team->id))
            ->whereIn('status', ['scheduled', 'live'])
            ->orderBy('kickoff_at')
            ->get();

        $recentMatches = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->published()
            ->where(fn ($q) => $q->where('home_team_id', $team->id)->orWhere('away_team_id', $team->id))
            ->where('status', 'final')
            ->orderByDesc('kickoff_at')
            ->get();

        $nextFixture = $upcomingMatches->first();

        $squad = $team->players()->orderBy('shirt_number')->get();
        $squadByPosition = Player::groupByPosition($squad);

        $topScorers = $squad->filter(fn ($p) => $p->goals > 0 || $p->assists > 0)
            ->sortByDesc(fn ($p) => $p->goals * 1000 + $p->assists)
            ->values();

        $news = NewsArticle::published()
            ->where('team_id', $team->id)
            ->orderByDesc('published_at')
            ->get();

        return view('teams.show', compact(
            'team',
            'leagueStandings',
            'standing',
            'upcomingMatches',
            'recentMatches',
            'nextFixture',
            'squadByPosition',
            'topScorers',
            'news',
        ));
    }
}
