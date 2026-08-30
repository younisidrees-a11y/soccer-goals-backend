<?php

namespace App\Http\Controllers;

use App\Models\MatchFixture;
use App\Models\Player;
use App\Models\Standing;
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

        // Real squad lists for both teams, shown on the fixture page
        // wherever the actual confirmed starting lineup isn't available
        // yet (i.e. before a match kicks off) - same grouping as a team's
        // own squad page, via the shared Player::groupByPosition() helper.
        $homeSquadByPosition = Player::groupByPosition($match->homeTeam->players()->orderBy('shirt_number')->get());
        $awaySquadByPosition = Player::groupByPosition($match->awayTeam->players()->orderBy('shirt_number')->get());

        // Sidebar points table for this match's own league - top 5, same
        // compact widget already used on a team's own page, with a link
        // through to the real full table.
        $sidebarTable = Standing::with('team')
            ->where('league_id', $match->league_id)
            ->orderBy('position')
            ->take(5)
            ->get();

        // Sidebar recent-results list for this match's own league - up to
        // 20 real completed matches, most recent first. The widget itself
        // only shows ~7 rows before scrolling, so this is real headroom
        // for that scroll rather than a list that runs out after 7.
        $sidebarRecentResults = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->published()
            ->where('league_id', $match->league_id)
            ->where('status', 'final')
            ->orderByDesc('kickoff_at')
            ->take(20)
            ->get();

        return view('matches.show', compact(
            'match', 'homeStanding', 'awayStanding', 'homeNext', 'awayNext',
            'homeLastMatch', 'awayLastMatch', 'homeNextTwo', 'awayNextTwo',
            'homeSquadByPosition', 'awaySquadByPosition',
            'sidebarTable', 'sidebarRecentResults'
        ));
    }
}
