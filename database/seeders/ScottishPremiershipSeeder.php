<?php

namespace Database\Seeders;

use App\Models\League;
use App\Models\MatchFixture;
use App\Models\Standing;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Adds the Scottish Premiership: the league itself, its 12 real clubs,
 * and a placeholder fixture schedule until api-football:sync-fixtures
 * replaces it with the real season. Not covered by football-data.org
 * (confirmed - only BSA/ELC/PL/CL/EC/FL1/BL1/SA/DED/PPL/CLI/PD/WC are),
 * so API-Football is the primary fixture source here, same as Saudi Pro
 * League/Liga MX/Süper Lig/MLS.
 */
class ScottishPremiershipSeeder extends Seeder
{
    private const TEAMS = [
        ['name' => 'Celtic', 'full' => 'Celtic Football Club', 'slug' => 'celtic', 'code' => 'ctc', 'color' => '#009B48', 'stadium' => 'Celtic Park', 'capacity' => 60832, 'founded' => 1888, 'api_id' => 247],
        ['name' => 'Hibernian', 'full' => 'Hibernian Football Club', 'slug' => 'hibernian', 'code' => 'hib', 'color' => '#005A2B', 'stadium' => 'Easter Road Stadium', 'capacity' => 20421, 'founded' => 1875, 'api_id' => 249],
        ['name' => 'Kilmarnock', 'full' => 'Kilmarnock Football Club', 'slug' => 'kilmarnock', 'code' => 'kil', 'color' => '#1B3F94', 'stadium' => 'Rugby Park', 'capacity' => 18128, 'founded' => 1869, 'api_id' => 250],
        ['name' => 'St Mirren', 'full' => 'St Mirren Football Club', 'slug' => 'st-mirren', 'code' => 'stm', 'color' => '#1A1A1A', 'stadium' => 'The SMISA Stadium', 'capacity' => 8016, 'founded' => 1877, 'api_id' => 251],
        ['name' => 'Aberdeen', 'full' => 'Aberdeen Football Club', 'slug' => 'aberdeen', 'code' => 'abd', 'color' => '#D0102F', 'stadium' => 'Pittodrie Stadium', 'capacity' => 22199, 'founded' => 1903, 'api_id' => 252],
        ['name' => 'Dundee', 'full' => 'Dundee Football Club', 'slug' => 'dundee', 'code' => 'dun', 'color' => '#001C58', 'stadium' => 'Dens Park', 'capacity' => 12085, 'founded' => 1893, 'api_id' => 253],
        ['name' => 'Hearts', 'full' => 'Heart of Midlothian Football Club', 'slug' => 'heart-of-midlothian', 'code' => 'hea', 'color' => '#7D0A2E', 'stadium' => 'Tynecastle Park', 'capacity' => 20099, 'founded' => 1874, 'api_id' => 254],
        ['name' => 'Motherwell', 'full' => 'Motherwell Football Club', 'slug' => 'motherwell', 'code' => 'mow', 'color' => '#A6192E', 'stadium' => 'Fir Park', 'capacity' => 13742, 'founded' => 1886, 'api_id' => 256],
        ['name' => 'Rangers', 'full' => 'Rangers Football Club', 'slug' => 'rangers', 'code' => 'rfc', 'color' => '#0033A0', 'stadium' => 'Ibrox Stadium', 'capacity' => 51082, 'founded' => 1873, 'api_id' => 257],
        ['name' => 'St Johnstone', 'full' => 'St Johnstone Football Club', 'slug' => 'st-johnstone', 'code' => 'stj', 'color' => '#1E4A9E', 'stadium' => 'McDiarmid Park', 'capacity' => 10673, 'founded' => 1885, 'api_id' => 258],
        ['name' => 'Dundee United', 'full' => 'Dundee United Football Club', 'slug' => 'dundee-united', 'code' => 'dnu', 'color' => '#F47920', 'stadium' => 'Tannadice Park', 'capacity' => 14209, 'founded' => 1909, 'api_id' => 1386],
        ['name' => 'Falkirk', 'full' => 'Falkirk Football Club', 'slug' => 'falkirk', 'code' => 'flk', 'color' => '#00205B', 'stadium' => 'Falkirk Community Stadium', 'capacity' => 9008, 'founded' => 1876, 'api_id' => 1389],
    ];

    public function run(): void
    {
        $league = League::updateOrCreate(
            ['slug' => 'scottish-premiership'],
            [
                'name' => 'Scottish Premiership',
                'country' => 'Scotland',
                'flag_code' => 'sct',
                'season' => '2026-27',
                'total_matchdays' => 38,
                'api_football_id' => 179,
                'about_text' => "The Scottish Premiership is dominated by one of football's fiercest and most storied rivalries: the Old Firm derby between Celtic and Rangers, two Glasgow clubs whose combined trophy haul dwarfs every other side in the division. Twelve clubs play each other three times in the regular season before the table splits into top-six and bottom-six sections for a final round of games.\nCeltic's recent run of domestic dominance has been challenged hardest by Rangers, while Aberdeen, Hearts and Hibernian make up a competitive chasing pack known collectively as Scottish football's 'New Firm' and traditional big clubs.",
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

        $kickoff = Carbon::create(2026, 8, 29, 15, 0, 0);
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
