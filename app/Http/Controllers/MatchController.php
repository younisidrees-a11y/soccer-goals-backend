<?php

namespace App\Http\Controllers;

use App\Models\MatchFixture;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public function show(Request $request, MatchFixture $match)
    {
        abort_unless($match->is_published, 404);

        $match->load(['league', 'homeTeam', 'awayTeam']);

        // The bare /matches/{id} link (still what every other page on the
        // site generates via route('matches.show', $id) - unchanged
        // deliberately, so this redirect is the only place that needs to
        // know about the pretty format) and any stale/wrong month or slug
        // both 301 here, so there's exactly one indexable URL per match.
        $canonicalMonth = $match->kickoff_at->format('m');
        $canonicalSlug = $match->seoSlug();

        if ($request->route('month') !== $canonicalMonth || $request->route('slug') !== $canonicalSlug) {
            return redirect()->route('matches.show', [
                'match' => $match->id,
                'month' => $canonicalMonth,
                'slug' => $canonicalSlug,
            ], 301);
        }

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
            'match', 'homeStanding', 'awayStanding', 'homeNext', 'awayNext',
            'homeLastMatch', 'awayLastMatch', 'homeNextTwo', 'awayNextTwo'
        ));
    }
}
