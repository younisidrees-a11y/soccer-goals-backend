<?php

namespace Database\Seeders;

use App\Models\League;
use App\Models\MatchFixture;
use App\Models\Standing;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Adds the Eredivisie: the league itself, its 18 real clubs, and a
 * placeholder fixture schedule until football-data:sync (external_code
 * DED, confirmed covered) replaces it with the real season.
 */
class EredivisieSeeder extends Seeder
{
    private const TEAMS = [
        ['name' => 'PEC Zwolle', 'full' => 'PEC Zwolle', 'slug' => 'pec-zwolle', 'code' => 'pec', 'color' => '#005BAA', 'stadium' => 'MAC³PARK Stadion', 'capacity' => 14000, 'founded' => 1910, 'api_id' => 193],
        ['name' => 'Ajax', 'full' => 'Amsterdamsche Football Club Ajax', 'slug' => 'ajax', 'code' => 'ajx', 'color' => '#D2122E', 'stadium' => 'Johan Cruijff Arena', 'capacity' => 55885, 'founded' => 1900, 'api_id' => 194],
        ['name' => 'Willem II', 'full' => 'Willem II Tilburg', 'slug' => 'willem-ii', 'code' => 'wlm', 'color' => '#001489', 'stadium' => 'Koning Willem II Stadion', 'capacity' => 14700, 'founded' => 1896, 'api_id' => 195],
        ['name' => 'Excelsior', 'full' => 'SBV Excelsior', 'slug' => 'excelsior', 'code' => 'exc', 'color' => '#E4002B', 'stadium' => 'Van Donge & De Roo Stadion', 'capacity' => 4500, 'founded' => 1902, 'api_id' => 196],
        ['name' => 'PSV Eindhoven', 'full' => 'Philips Sport Vereniging Eindhoven', 'slug' => 'psv-eindhoven', 'code' => 'psv', 'color' => '#ED1C24', 'stadium' => 'Philips Stadion', 'capacity' => 36500, 'founded' => 1913, 'api_id' => 197],
        ['name' => 'ADO Den Haag', 'full' => 'Alles Door Oefening Den Haag', 'slug' => 'ado-den-haag', 'code' => 'adh', 'color' => '#FFD100', 'stadium' => 'Bingoal Stadion', 'capacity' => 15000, 'founded' => 1905, 'api_id' => 198],
        ['name' => 'AZ Alkmaar', 'full' => 'Alkmaar Zaanstreek', 'slug' => 'az-alkmaar', 'code' => 'azk', 'color' => '#C8102E', 'stadium' => 'AFAS Stadion', 'capacity' => 19500, 'founded' => 1967, 'api_id' => 201],
        ['name' => 'Groningen', 'full' => 'Football Club Groningen', 'slug' => 'fc-groningen', 'code' => 'gro', 'color' => '#00A54F', 'stadium' => 'Euroborg', 'capacity' => 22579, 'founded' => 1971, 'api_id' => 202],
        ['name' => 'Fortuna Sittard', 'full' => 'Fortuna Sittard', 'slug' => 'fortuna-sittard', 'code' => 'frs', 'color' => '#F7A81B', 'stadium' => 'Fortuna Sittard Stadion', 'capacity' => 12500, 'founded' => 1968, 'api_id' => 205],
        ['name' => 'Utrecht', 'full' => 'Football Club Utrecht', 'slug' => 'fc-utrecht', 'code' => 'utr', 'color' => '#C8102E', 'stadium' => 'Stadion Galgenwaard', 'capacity' => 24426, 'founded' => 1970, 'api_id' => 207],
        ['name' => 'Feyenoord', 'full' => 'Feyenoord Rotterdam', 'slug' => 'feyenoord', 'code' => 'fey', 'color' => '#B0272D', 'stadium' => 'Stadion Feijenoord (De Kuip)', 'capacity' => 51117, 'founded' => 1908, 'api_id' => 209],
        ['name' => 'Heerenveen', 'full' => 'Sport Club Heerenveen', 'slug' => 'sc-heerenveen', 'code' => 'hee', 'color' => '#003DA5', 'stadium' => 'Abe Lenstra Stadion', 'capacity' => 27224, 'founded' => 1920, 'api_id' => 210],
        ['name' => 'Go Ahead Eagles', 'full' => 'Go Ahead Eagles', 'slug' => 'go-ahead-eagles', 'code' => 'gae', 'color' => '#F2A900', 'stadium' => 'De Adelaarshorst', 'capacity' => 10400, 'founded' => 1971, 'api_id' => 410],
        ['name' => 'NEC Nijmegen', 'full' => 'Nijmegen Eendracht Combinatie', 'slug' => 'nec-nijmegen', 'code' => 'nym', 'color' => '#E4002B', 'stadium' => 'Goffertstadion', 'capacity' => 12540, 'founded' => 1900, 'api_id' => 413],
        ['name' => 'Twente', 'full' => 'Football Club Twente', 'slug' => 'fc-twente', 'code' => 'twe', 'color' => '#D2122E', 'stadium' => 'De Grolsch Veste', 'capacity' => 30205, 'founded' => 1965, 'api_id' => 415],
        ['name' => 'Cambuur', 'full' => 'SC Cambuur Leeuwarden', 'slug' => 'cambuur', 'code' => 'cam', 'color' => '#FFD100', 'stadium' => 'Cambuur Stadion', 'capacity' => 11230, 'founded' => 1964, 'api_id' => 420],
        ['name' => 'Sparta Rotterdam', 'full' => 'Sparta Rotterdam', 'slug' => 'sparta-rotterdam', 'code' => 'spr', 'color' => '#D2122E', 'stadium' => 'Sparta-Stadion Het Kasteel', 'capacity' => 11026, 'founded' => 1888, 'api_id' => 426],
        ['name' => 'Telstar', 'full' => 'SC Telstar', 'slug' => 'telstar', 'code' => 'tel', 'color' => '#E4002B', 'stadium' => '711 Stadion', 'capacity' => 5200, 'founded' => 1912, 'api_id' => 427],
    ];

    public function run(): void
    {
        $league = League::updateOrCreate(
            ['slug' => 'eredivisie'],
            [
                'name' => 'Eredivisie',
                'country' => 'Netherlands',
                'flag_code' => 'nld',
                'season' => '2026-27',
                'total_matchdays' => 34,
                'api_football_id' => 88,
                'about_text' => "The Eredivisie has one of the strongest reputations in world football for developing young talent, with Ajax, PSV Eindhoven and Feyenoord - Dutch football's traditional 'big three' - regularly selling academy graduates on to Europe's biggest leagues for significant fees. Eighteen clubs play a full home-and-away season built around an attacking, technically-driven style the Dutch game has long been known for.\nAjax's European Cup pedigree from the 1970s and 1995 Champions League triumph still shapes the club's identity, while PSV and Feyenoord have combined to keep the title race genuinely competitive in recent seasons rather than a one-club procession.",
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

        $kickoff = Carbon::create(2026, 8, 29, 18, 30, 0);
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
