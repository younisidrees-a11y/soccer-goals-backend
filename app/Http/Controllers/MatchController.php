<?php

namespace App\Http\Controllers;

use App\Models\MatchFixture;

class MatchController extends Controller
{
    public function show(MatchFixture $match)
    {
        abort_unless($match->is_published, 404);

        $match->load(['league', 'homeTeam', 'awayTeam']);

        $homeStanding = $match->homeTeam->standing()->where('league_id', $match->league_id)->first();
        $awayStanding = $match->awayTeam->standing()->where('league_id', $match->league_id)->first();

        $homeNext = MatchFixture::published()
            ->where('status', '!=', 'final')
            ->where(fn ($q) => $q->where('home_team_id', $match->home_team_id)->orWhere('away_team_id', $match->home_team_id))
            ->where('id', '!=', $match->id)
            ->orderBy('kickoff_at')
            ->first();

        $awayNext = MatchFixture::published()
            ->where('status', '!=', 'final')
            ->where(fn ($q) => $q->where('home_team_id', $match->away_team_id)->orWhere('away_team_id', $match->away_team_id))
            ->where('id', '!=', $match->id)
            ->orderBy('kickoff_at')
            ->first();

        $tickerMatches = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->published()
            ->where('status', 'final')
            ->orderByDesc('kickoff_at')
            ->take(7)
            ->get();

        $lastMatchFor = fn (int $teamId) => MatchFixture::with(['homeTeam', 'awayTeam'])
            ->published()
            ->where('status', 'final')
            ->where(fn ($q) => $q->where('home_team_id', $teamId)->orWhere('away_team_id', $teamId))
            ->orderByDesc('kickoff_at')
            ->first();

        $nextTwoFor = fn (int $teamId) => MatchFixture::with(['homeTeam', 'awayTeam'])
            ->published()
            ->where('status', '!=', 'final')
            ->where(fn ($q) => $q->where('home_team_id', $teamId)->orWhere('away_team_id', $teamId))
            ->where('id', '!=', $match->id)
            ->orderBy('kickoff_at')
            ->limit(2)
            ->get();

        $homeLastMatch = $lastMatchFor($match->home_team_id);
        $awayLastMatch = $lastMatchFor($match->away_team_id);
        $homeNextTwo = $nextTwoFor($match->home_team_id);
        $awayNextTwo = $nextTwoFor($match->away_team_id);

        return view('matches.show', compact(
            'match', 'homeStanding', 'awayStanding', 'homeNext', 'awayNext', 'tickerMatches',
            'homeLastMatch', 'awayLastMatch', 'homeNextTwo', 'awayNextTwo'
        ));
    }
}
