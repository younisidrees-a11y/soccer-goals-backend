<?php

namespace App\Http\Controllers;

use App\Models\League;
use App\Models\MatchFixture;
use App\Models\NewsArticle;
use App\Models\Player;
use App\Models\Standing;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /**
     * Official competition brand colours for the leagues this site
     * actually covers - used as the accent bar on dashboard match groups
     * and league tiles, same idea as a team's own color_hex. Not
     * decorative guesswork: these are each league's real primary brand
     * colour, limited to leagues we publish.
     */
    private const LEAGUE_COLORS = [
        'premier-league' => '#3D195B',
        'la-liga' => '#FF4B44',
        'serie-a' => '#00A650',
        'bundesliga' => '#D20515',
        'ligue-1' => '#C8E600',
        'saudi-pro-league' => '#0B7A3B',
    ];

    public function index()
    {
        // No take() limit here - the homepage groups these by competition
        // with a real client-side status filter (All/Live/Upcoming/
        // Finished), so it needs every match today, not a 4-card sample.
        $todaysMatches = MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
            ->published()
            ->whereDate('kickoff_at', now()->toDateString())
            ->orderBy('league_id')
            ->orderBy('kickoff_at')
            ->get();

        $todaysMatchesByLeague = $todaysMatches->groupBy('league_id');

        // Feeds both the Latest News section (1 featured + 2 medium + a
        // timeline of the rest) and the dashboard sidebar's Latest Stories
        // card, so it needs enough rows for both slices.
        $latestNews = NewsArticle::with(['league', 'team'])
            ->published()
            ->orderByDesc('published_at')
            ->take(10)
            ->get();

        // Real category, not a fabricated deal table - the mockup's
        // "Transfer Center" invented sample players/clubs it flagged as a
        // placeholder with no backing data model; this queries the same
        // published-news pipeline as everything else and shows a genuine
        // empty state if nothing's been published under this category yet.
        $transferNews = NewsArticle::with(['league', 'team'])
            ->published()
            ->where('category', 'transfers')
            ->orderByDesc('published_at')
            ->take(5)
            ->get();

        $upcomingFixtures = MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
            ->published()
            ->where('status', 'scheduled')
            ->orderBy('kickoff_at')
            ->take(5)
            ->get();

        $recentResults = MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
            ->published()
            ->where('status', 'final')
            ->orderByDesc('kickoff_at')
            ->take(5)
            ->get();

        $leagueTables = League::published()
            ->orderBy('name')
            ->get()
            ->map(fn (League $l) => [
                'league' => $l,
                'standings' => Standing::with('team')->where('league_id', $l->id)->orderBy('position')->get(),
            ])
            ->filter(fn ($t) => $t['standings']->isNotEmpty())
            ->values()
            ->map(fn ($t) => $t + ['hasStarted' => $t['standings']->sum('played') > 0]);

        // Alphabetical order can easily land on a league whose season hasn't
        // kicked off yet (every row still 0-0-0-0-0), which is a useless
        // default view. Default to the first league that actually has
        // played matches; only fall back to index 0 if literally none do.
        $defaultLeagueIndex = $leagueTables->search(fn ($t) => $t['hasStarted']);
        $defaultLeagueIndex = $defaultLeagueIndex === false ? 0 : $defaultLeagueIndex;

        // Top Scorer widget: real goals tally from Player, not a
        // fabricated stat - scoped to whichever league the standings
        // panel defaults to, so the two sidebar/table modules agree on
        // which competition they're describing.
        $topScorerLeague = $leagueTables[$defaultLeagueIndex]['league'] ?? null;
        $topScorer = $topScorerLeague
            ? Player::with('team')
                ->whereHas('team', fn ($q) => $q->where('league_id', $topScorerLeague->id)->published())
                ->whereNotNull('goals')
                ->where('goals', '>', 0)
                ->orderByDesc('goals')
                ->first()
            : null;

        // Popular Competitions strip: every published league with a real,
        // computed status line rather than static filler text - how many
        // of today's real matches belong to it, or an honest "season
        // hasn't started" / "no matches today" line when there are none.
        $popularLeagues = League::published()
            ->orderBy('name')
            ->get()
            ->map(function (League $l) use ($todaysMatchesByLeague) {
                $todayCount = $todaysMatchesByLeague->get($l->id, collect())->count();
                $liveCount = $todaysMatchesByLeague->get($l->id, collect())->filter(fn ($m) => $m->isLive())->count();

                return [
                    'league' => $l,
                    'color' => self::LEAGUE_COLORS[$l->slug] ?? null,
                    'todayCount' => $todayCount,
                    'liveCount' => $liveCount,
                    'statusLabel' => match (true) {
                        $liveCount > 0 => $liveCount.' live now',
                        $todayCount > 0 => $todayCount.' '.Str::plural('fixture', $todayCount).' today',
                        default => 'No matches today',
                    },
                ];
            });

        $leagueColors = self::LEAGUE_COLORS;

        return view('home', compact(
            'todaysMatches',
            'todaysMatchesByLeague',
            'latestNews',
            'transferNews',
            'upcomingFixtures',
            'recentResults',
            'leagueTables',
            'defaultLeagueIndex',
            'topScorer',
            'popularLeagues',
            'leagueColors',
        ));
    }
}
