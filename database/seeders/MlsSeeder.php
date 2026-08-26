<?php

namespace Database\Seeders;

use App\Models\League;
use App\Models\MatchFixture;
use App\Models\Standing;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Adds Major League Soccer: the league itself, its 30 real clubs
 * (sourced from API-Football's /teams endpoint - id, venue, capacity,
 * founded year, crest all real), and a full double round-robin fixture
 * schedule as placeholder scheduling. api-football:sync-fixtures mls
 * (after api-football:map-teams) replaces these with the real
 * conference-based schedule and removes the placeholders.
 *
 * San Diego FC (2025 expansion club) has no venue/capacity/founded data
 * in the API yet - left null rather than fabricated, same treatment as
 * Al Diriyah in the Saudi Pro League roster.
 */
class MlsSeeder extends Seeder
{
    private const TEAMS = [
        ['name' => 'Seattle Sounders', 'full' => 'Seattle Sounders FC', 'slug' => 'seattle-sounders', 'code' => 'sea', 'color' => '#5D9741', 'stadium' => 'Lumen Field', 'capacity' => 72000, 'founded' => 2007, 'api_id' => 1595],
        ['name' => 'San Jose Earthquakes', 'full' => 'San Jose Earthquakes', 'slug' => 'san-jose-earthquakes', 'code' => 'sje', 'color' => '#002B5C', 'stadium' => 'PayPal Park', 'capacity' => 18000, 'founded' => 1995, 'api_id' => 1596],
        ['name' => 'FC Dallas', 'full' => 'FC Dallas', 'slug' => 'fc-dallas', 'code' => 'dal', 'color' => '#E31837', 'stadium' => 'Toyota Stadium', 'capacity' => 22565, 'founded' => 1996, 'api_id' => 1597],
        ['name' => 'Orlando City SC', 'full' => 'Orlando City Soccer Club', 'slug' => 'orlando-city-sc', 'code' => 'orl', 'color' => '#612D91', 'stadium' => 'Inter&Co Stadium', 'capacity' => 25527, 'founded' => 2008, 'api_id' => 1598],
        ['name' => 'Philadelphia Union', 'full' => 'Philadelphia Union', 'slug' => 'philadelphia-union', 'code' => 'phi', 'color' => '#0A1F44', 'stadium' => 'Subaru Park', 'capacity' => 19778, 'founded' => 2008, 'api_id' => 1599],
        ['name' => 'Houston Dynamo', 'full' => 'Houston Dynamo FC', 'slug' => 'houston-dynamo', 'code' => 'hou', 'color' => '#F04E23', 'stadium' => 'Shell Energy Stadium', 'capacity' => 22661, 'founded' => 2005, 'api_id' => 1600],
        ['name' => 'Toronto FC', 'full' => 'Toronto FC', 'slug' => 'toronto-fc', 'code' => 'trt', 'color' => '#B81137', 'stadium' => 'BMO Field', 'capacity' => 36045, 'founded' => 2006, 'api_id' => 1601],
        ['name' => 'New York Red Bulls', 'full' => 'New York Red Bulls', 'slug' => 'new-york-red-bulls', 'code' => 'nyr', 'color' => '#E4292F', 'stadium' => 'Sports Illustrated Stadium', 'capacity' => 26765, 'founded' => 1995, 'api_id' => 1602],
        ['name' => 'Vancouver Whitecaps', 'full' => 'Vancouver Whitecaps FC', 'slug' => 'vancouver-whitecaps', 'code' => 'van', 'color' => '#00245D', 'stadium' => 'BC Place', 'capacity' => 54405, 'founded' => 1986, 'api_id' => 1603],
        ['name' => 'New York City FC', 'full' => 'New York City Football Club', 'slug' => 'new-york-city-fc', 'code' => 'nyc', 'color' => '#4B9CD3', 'stadium' => 'Yankee Stadium', 'capacity' => 54251, 'founded' => 2013, 'api_id' => 1604],
        ['name' => 'LA Galaxy', 'full' => 'Los Angeles Galaxy', 'slug' => 'la-galaxy', 'code' => 'lag', 'color' => '#00245D', 'stadium' => 'Dignity Health Sports Park', 'capacity' => 30510, 'founded' => 1995, 'api_id' => 1605],
        ['name' => 'Real Salt Lake', 'full' => 'Real Salt Lake', 'slug' => 'real-salt-lake', 'code' => 'rsl', 'color' => '#7A1231', 'stadium' => 'America First Field', 'capacity' => 21810, 'founded' => 2004, 'api_id' => 1606],
        ['name' => 'Chicago Fire', 'full' => 'Chicago Fire Football Club', 'slug' => 'chicago-fire', 'code' => 'chf', 'color' => '#A5122A', 'stadium' => 'Soldier Field', 'capacity' => 62493, 'founded' => 1997, 'api_id' => 1607],
        ['name' => 'Atlanta United FC', 'full' => 'Atlanta United Football Club', 'slug' => 'atlanta-united-fc', 'code' => 'atu', 'color' => '#A5000E', 'stadium' => 'Mercedes-Benz Stadium', 'capacity' => 73019, 'founded' => 2014, 'api_id' => 1608],
        ['name' => 'New England Revolution', 'full' => 'New England Revolution', 'slug' => 'new-england-revolution', 'code' => 'ner', 'color' => '#0A2351', 'stadium' => 'Gillette Stadium', 'capacity' => 68756, 'founded' => 1995, 'api_id' => 1609],
        ['name' => 'Colorado Rapids', 'full' => 'Colorado Rapids', 'slug' => 'colorado-rapids', 'code' => 'crp', 'color' => '#862633', 'stadium' => "Dick's Sporting Goods Park", 'capacity' => 19734, 'founded' => 1996, 'api_id' => 1610],
        ['name' => 'Sporting Kansas City', 'full' => 'Sporting Kansas City', 'slug' => 'sporting-kansas-city', 'code' => 'skc', 'color' => '#002F65', 'stadium' => "Children's Mercy Park", 'capacity' => 21650, 'founded' => 1995, 'api_id' => 1611],
        ['name' => 'Minnesota United FC', 'full' => 'Minnesota United Football Club', 'slug' => 'minnesota-united-fc', 'code' => 'mnu', 'color' => '#171A21', 'stadium' => 'Allianz Field', 'capacity' => 19954, 'founded' => 2010, 'api_id' => 1612],
        ['name' => 'Columbus Crew', 'full' => 'Columbus Crew', 'slug' => 'columbus-crew', 'code' => 'clc', 'color' => '#FEDD00', 'stadium' => 'Lower.com Field', 'capacity' => 20931, 'founded' => 1996, 'api_id' => 1613],
        ['name' => 'CF Montréal', 'full' => 'Club de Foot Montréal', 'slug' => 'cf-montreal', 'code' => 'cfm', 'color' => '#0033A0', 'stadium' => 'Stade Saputo', 'capacity' => 20801, 'founded' => 2010, 'api_id' => 1614],
        ['name' => 'DC United', 'full' => 'D.C. United', 'slug' => 'dc-united', 'code' => 'dcu', 'color' => '#E31B23', 'stadium' => 'Audi Field', 'capacity' => 20621, 'founded' => 1996, 'api_id' => 1615],
        ['name' => 'Los Angeles FC', 'full' => 'Los Angeles Football Club', 'slug' => 'los-angeles-fc', 'code' => 'laf', 'color' => '#C39E6D', 'stadium' => 'BMO Stadium', 'capacity' => 22921, 'founded' => 2014, 'api_id' => 1616],
        ['name' => 'Portland Timbers', 'full' => 'Portland Timbers', 'slug' => 'portland-timbers', 'code' => 'prt', 'color' => '#004812', 'stadium' => 'Providence Park', 'capacity' => 25518, 'founded' => 2009, 'api_id' => 1617],
        ['name' => 'FC Cincinnati', 'full' => 'FC Cincinnati', 'slug' => 'fc-cincinnati', 'code' => 'cin', 'color' => '#FE5000', 'stadium' => 'TQL Stadium', 'capacity' => 26000, 'founded' => null, 'api_id' => 2242],
        ['name' => 'Inter Miami', 'full' => 'Club Internacional de Fútbol Miami', 'slug' => 'inter-miami', 'code' => 'mia', 'color' => '#D6006C', 'stadium' => 'Chase Stadium', 'capacity' => 21550, 'founded' => 2018, 'api_id' => 9568],
        ['name' => 'Nashville SC', 'full' => 'Nashville Soccer Club', 'slug' => 'nashville-sc', 'code' => 'nsh', 'color' => '#ECE83A', 'stadium' => 'GEODIS Park', 'capacity' => 30109, 'founded' => 2020, 'api_id' => 9569],
        ['name' => 'Austin FC', 'full' => 'Austin Football Club', 'slug' => 'austin-fc', 'code' => 'aus', 'color' => '#1D9C6E', 'stadium' => 'Q2 Stadium', 'capacity' => 20738, 'founded' => 2021, 'api_id' => 16489],
        ['name' => 'Charlotte FC', 'full' => 'Charlotte Football Club', 'slug' => 'charlotte-fc', 'code' => 'clt', 'color' => '#1A85C8', 'stadium' => 'Bank of America Stadium', 'capacity' => 75412, 'founded' => 2019, 'api_id' => 18310],
        ['name' => 'St. Louis City SC', 'full' => 'St. Louis City Soccer Club', 'slug' => 'st-louis-city-sc', 'code' => 'stl', 'color' => '#D0202B', 'stadium' => 'CITYPARK', 'capacity' => 22500, 'founded' => 2023, 'api_id' => 20787],
        ['name' => 'San Diego FC', 'full' => 'San Diego Football Club', 'slug' => 'san-diego-fc', 'code' => 'sdg', 'color' => '#0C2340', 'stadium' => null, 'capacity' => null, 'founded' => null, 'api_id' => 25484],
    ];

    public function run(): void
    {
        // is_published is intentionally left out of these updateOrCreate
        // calls: it defaults to false on first insert, but must never be
        // touched on a re-run, or this seeder would silently unpublish
        // content an admin already approved.
        $league = League::updateOrCreate(
            ['slug' => 'mls'],
            [
                'name' => 'Major League Soccer',
                'country' => 'United States',
                'flag_code' => 'usa',
                'season' => '2026',
                'total_matchdays' => 34,
                'api_football_id' => 253,
                'about_text' => "Major League Soccer has grown from a modest 10-club start-up into one of the biggest domestic leagues in the world by attendance, spanning the United States and Canada across 30 clubs. The league runs on a single-table regular season split loosely into Eastern and Western Conferences, before the top sides advance into the MLS Cup Playoffs.\nStars like Lionel Messi at Inter Miami have pulled a global spotlight onto the league in recent years, while long-established clubs such as Seattle Sounders, LA Galaxy and Atlanta United continue to draw some of the biggest crowds in American sport.",
            ]
        );

        $teamModels = [];
        foreach (self::TEAMS as $t) {
            $teamModels[$t['slug']] = Team::updateOrCreate(
                ['slug' => $t['slug']],
                [
                    'league_id' => $league->id,
                    'name' => $t['name'],
                    'full_name' => $t['full'],
                    'crest_code' => $t['code'],
                    'color_hex' => $t['color'],
                    'stadium' => $t['stadium'],
                    'stadium_capacity' => $t['capacity'],
                    'founded_year' => $t['founded'],
                    'api_football_id' => $t['api_id'],
                ]
            );
        }

        $position = 1;
        foreach ($teamModels as $team) {
            Standing::updateOrCreate(
                ['league_id' => $league->id, 'team_id' => $team->id],
                ['position' => $position++, 'zone' => 'none']
            );
        }

        // Only rebuild the schedule if this league has no matches yet, so
        // re-running the seeder doesn't create duplicate fixtures.
        if (MatchFixture::where('league_id', $league->id)->exists()) {
            return;
        }

        $order = array_values($teamModels);
        $n = count($order);
        $rounds = $n - 1;
        $half = $n / 2;

        $firstLeg = [];
        $arr = $order;
        for ($round = 0; $round < $rounds; $round++) {
            $pairings = [];
            for ($i = 0; $i < $half; $i++) {
                $home = $arr[$i];
                $away = $arr[$n - 1 - $i];
                if ($round % 2 === 1) {
                    [$home, $away] = [$away, $home];
                }
                $pairings[] = [$home, $away];
            }
            $firstLeg[] = $pairings;

            $fixed = $arr[0];
            $rest = array_slice($arr, 1);
            array_unshift($rest, array_pop($rest));
            $arr = array_merge([$fixed], $rest);
        }

        $kickoff = Carbon::create(2026, 2, 21, 20, 0, 0);
        $venue = fn (Team $home) => $home->stadium;

        foreach ($firstLeg as $matchdayIndex => $pairings) {
            $matchday = $matchdayIndex + 1;
            $md1Time = $kickoff->copy()->addWeeks($matchdayIndex);

            foreach ($pairings as $pair) {
                [$home, $away] = $pair;
                MatchFixture::create([
                    'league_id' => $league->id,
                    'home_team_id' => $home->id,
                    'away_team_id' => $away->id,
                    'matchday' => $matchday,
                    'kickoff_at' => $md1Time,
                    'venue' => $venue($home),
                    'status' => 'scheduled',
                    'is_published' => false,
                ]);

                $md2Time = $kickoff->copy()->addWeeks($rounds + $matchdayIndex);
                MatchFixture::create([
                    'league_id' => $league->id,
                    'home_team_id' => $away->id,
                    'away_team_id' => $home->id,
                    'matchday' => $rounds + $matchday,
                    'kickoff_at' => $md2Time,
                    'venue' => $venue($away),
                    'status' => 'scheduled',
                    'is_published' => false,
                ]);
            }
        }
    }
}
