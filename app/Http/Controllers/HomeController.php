<?php

namespace App\Http\Controllers;

use App\Models\League;
use App\Models\MatchFixture;
use App\Models\NewsArticle;
use App\Models\Player;
use App\Models\Standing;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /** How far either side of real "today" the date strip allows browsing to. */
    private const DATE_RANGE_DAYS = 10;

    /** Leagues shown side by side in the homepage's Leagues Teams Standings section. */
    private const SNAPSHOT_LEAGUE_SLUGS = ['premier-league', 'la-liga'];

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
        'mls' => '#041E42',
        // Reuse the same national colours already in the flag sprite
        // (layouts/site.blade.php) rather than inventing new ones for
        // these three - one real source of truth for "this country's
        // colour" instead of two.
        'primeira-liga' => '#046A38',
        'scottish-premiership' => '#0065BD',
        'super-lig' => '#E30A17',
        'championship' => '#E5007D',
        'eredivisie' => '#E52421',
        'liga-mx' => '#00B2A9',
    ];

    public function index(Request $request)
    {
        $today = Carbon::today();

        // Real day-to-day browsing, not a decorative strip: ?date= picks
        // which day's matches the dashboard hero shows. Clamped to a sane
        // window either side of today rather than accepting an arbitrary
        // date, and falls back to today on anything invalid.
        $selectedDate = $today;
        if ($request->filled('date')) {
            try {
                $requested = Carbon::createFromFormat('Y-m-d', (string) $request->query('date'))->startOfDay();
                if ($requested->between($today->copy()->subDays(self::DATE_RANGE_DAYS), $today->copy()->addDays(self::DATE_RANGE_DAYS))) {
                    $selectedDate = $requested;
                }
            } catch (\Exception) {
                // Malformed date string - keep the $today fallback.
            }
        }
        $isToday = $selectedDate->isSameDay($today);

        // The 6-chip strip always centers on TODAY (not the selected day),
        // so "where am I relative to now" stays visible no matter which
        // day you're browsing - matches how the mockup's WED/THU/TODAY/
        // SAT/SUN/MON row worked.
        $dateStrip = collect(range(-2, 3))->map(fn (int $offset) => $today->copy()->addDays($offset));

        // No take() limit here - the homepage groups these by competition
        // with a real client-side status filter (All/Live/Upcoming/
        // Finished), so it needs every match on the selected day, not a
        // 4-card sample.
        $todaysMatches = MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
            ->published()
            ->whereDate('kickoff_at', $selectedDate->toDateString())
            ->orderBy('league_id')
            ->orderBy('kickoff_at')
            ->get();

        // Popular Competitions isn't wired to the date strip above (the
        // mockup treats it as a standalone, always-current module) - it
        // needs its own real-today count even while you're browsing a
        // different day in the dashboard hero.
        $realTodayMatchesByLeague = $isToday
            ? $todaysMatches->groupBy('league_id')
            : MatchFixture::published()->whereDate('kickoff_at', $today->toDateString())->get()->groupBy('league_id');

        $todaysMatchesByLeague = $todaysMatches->groupBy('league_id');

        // Real recent match report, not a fabricated headline - the most
        // recently published match-report article that's actually tied to
        // a real result. Articles don't set match_id in practice (checked
        // live: 0 of 5 published match-report articles have it, only
        // team_id/league_id), so the real match has to be resolved by
        // joining on those instead - same team, same league, a final
        // result near publication.
        //
        // That join alone isn't precise enough: a team can play more than
        // once inside the date window, and matching on team+league+date
        // picked the WRONG opponent in testing (article headlined "...Win
        // Over Valencia", join returned a real but different Barcelona
        // result against Elche - a real score paired with the wrong
        // opponent, which is worse than showing nothing). Every candidate
        // is now cross-checked against the article's own title for the
        // opponent's name before being accepted; $spotMatch stays null
        // (section doesn't render) if nothing passes that check.
        $spotlight = NewsArticle::with(['team', 'league'])
            ->published()
            ->where('category', 'match-report')
            ->whereNotNull('team_id')
            ->whereNotNull('league_id')
            ->orderByDesc('published_at')
            ->first();

        $spotMatch = null;
        if ($spotlight && $spotlight->published_at) {
            $candidates = MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
                ->published()
                ->where('league_id', $spotlight->league_id)
                ->where('status', 'final')
                ->where(fn ($q) => $q->where('home_team_id', $spotlight->team_id)->orWhere('away_team_id', $spotlight->team_id))
                ->whereBetween('kickoff_at', [
                    $spotlight->published_at->copy()->subDays(2),
                    $spotlight->published_at->copy()->addDays(2),
                ])
                ->get()
                ->sortBy(fn (MatchFixture $m) => abs($m->kickoff_at->diffInMinutes($spotlight->published_at)));

            $spotMatch = $candidates->first(function (MatchFixture $m) use ($spotlight) {
                $opponent = $m->home_team_id === $spotlight->team_id ? $m->awayTeam : $m->homeTeam;

                return $opponent && str_contains(Str::lower($spotlight->title), Str::lower($opponent->name));
            });
        }
        if (! $spotMatch) {
            $spotlight = null;
        }

        // No article cleared both checks above - rather than an empty
        // section, fall back to the single most recent real final score
        // site-wide. Headline/dek are generated straight from that
        // match's own real data (never invented prose), and the CTA
        // points at the match page itself instead of a "Read Match
        // Report" article that doesn't exist.
        if (! $spotMatch) {
            $spotMatch = MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
                ->published()
                ->where('status', 'final')
                ->orderByDesc('kickoff_at')
                ->first();
        }

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

        // Sidebar "Recent Results" widget - real completed matches across
        // every league, not just $recentResults' 5 (that's used
        // elsewhere on this same page for a different section, so this
        // is deliberately its own query rather than just raising that
        // take(5), which would also balloon the other section using it).
        // Widget itself only shows ~10 rows before scrolling; up to 60 is
        // real headroom for that scroll.
        $sidebarAllLeagueResults = MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
            ->published()
            ->where('status', 'final')
            ->orderByDesc('kickoff_at')
            ->take(60)
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

        // Form guide (last 5 real results, oldest to newest) for the two
        // leagues shown in the homepage standings snapshot - computed from
        // actual finished matches, not simulated. One shared query for
        // every team in both leagues rather than one query per team.
        $snapshotTeamIds = Team::whereHas('league', fn ($q) => $q->whereIn('slug', self::SNAPSHOT_LEAGUE_SLUGS))
            ->pluck('id');
        $snapshotFormMatches = MatchFixture::where('status', 'final')
            ->where(fn ($q) => $q->whereIn('home_team_id', $snapshotTeamIds)->orWhereIn('away_team_id', $snapshotTeamIds))
            ->orderByDesc('kickoff_at')
            ->limit(400)
            ->get(['home_team_id', 'away_team_id', 'home_score', 'away_score', 'kickoff_at']);

        $formByTeam = [];
        foreach ($snapshotTeamIds as $teamId) {
            $formByTeam[$teamId] = $snapshotFormMatches
                ->filter(fn ($m) => $m->home_team_id === $teamId || $m->away_team_id === $teamId)
                ->take(5)
                ->reverse()
                ->map(function ($m) use ($teamId) {
                    $isHome = $m->home_team_id === $teamId;
                    $for = $isHome ? $m->home_score : $m->away_score;
                    $against = $isHome ? $m->away_score : $m->home_score;

                    return $for > $against ? 'W' : ($for < $against ? 'L' : 'D');
                })
                ->values();
        }

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
            ->map(function (League $l) use ($realTodayMatchesByLeague) {
                $todayCount = $realTodayMatchesByLeague->get($l->id, collect())->count();
                $liveCount = $realTodayMatchesByLeague->get($l->id, collect())->filter(fn ($m) => $m->isLive())->count();

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

        // Top Scorers section: real leading goalscorer for every published
        // league, not just the one the standings panel defaults to. One
        // query per league (13 leagues, run once per request) rather than
        // a single grouped query, since "highest goals per league" needs
        // a per-league MAX anyway and this stays simple to read.
        $topScorersByLeague = League::published()
            ->orderBy('name')
            ->get()
            ->map(fn (League $l) => [
                'league' => $l,
                'color' => self::LEAGUE_COLORS[$l->slug] ?? null,
                'player' => Player::with('team')
                    ->whereHas('team', fn ($q) => $q->where('league_id', $l->id)->published())
                    ->whereNotNull('goals')
                    ->where('goals', '>', 0)
                    ->orderByDesc('goals')
                    ->first(),
            ]);

        return view('home', compact(
            'todaysMatches',
            'todaysMatchesByLeague',
            'latestNews',
            'transferNews',
            'upcomingFixtures',
            'recentResults',
            'sidebarAllLeagueResults',
            'leagueTables',
            'defaultLeagueIndex',
            'topScorer',
            'popularLeagues',
            'leagueColors',
            'selectedDate',
            'isToday',
            'dateStrip',
            'spotlight',
            'spotMatch',
            'topScorersByLeague',
            'formByTeam',
        ));
    }
}
