<?php

namespace Database\Seeders;

use App\Models\League;
use App\Models\MatchFixture;
use App\Models\Standing;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Adds the Saudi Pro League: the league itself, its 18 real clubs, and a
 * full double round-robin fixture schedule (34 matchdays, 306 matches).
 * Nothing is scored yet - every match is seeded as an upcoming fixture.
 */
class SaudiProLeagueSeeder extends Seeder
{
    private const TEAMS = [
        ['name' => 'Al-Hilal', 'full' => 'Al-Hilal Saudi Football Club', 'slug' => 'al-hilal', 'code' => 'hil', 'color' => '#1E5FAA', 'stadium' => 'Kingdom Arena'],
        ['name' => 'Al-Nassr', 'full' => 'Al-Nassr Football Club', 'slug' => 'al-nassr', 'code' => 'nas', 'color' => '#FFD400', 'stadium' => 'Al-Awwal Park'],
        ['name' => 'Al-Ittihad', 'full' => 'Ittihad Club', 'slug' => 'al-ittihad', 'code' => 'itt', 'color' => '#C8A951', 'stadium' => 'King Abdullah Sports City Stadium'],
        ['name' => 'Al-Ahli', 'full' => 'Al-Ahli Saudi Football Club', 'slug' => 'al-ahli-saudi', 'code' => 'ahl', 'color' => '#00A651', 'stadium' => 'King Abdullah Sports City Stadium'],
        ['name' => 'Al-Shabab', 'full' => 'Al-Shabab Football Club', 'slug' => 'al-shabab', 'code' => 'shb', 'color' => '#151515', 'stadium' => 'SHG Arena'],
        ['name' => 'Al-Ettifaq', 'full' => 'Al-Ettifaq Football Club', 'slug' => 'al-ettifaq', 'code' => 'etf', 'color' => '#7A1F3D', 'stadium' => 'Stadion EGO'],
        ['name' => 'Al-Fateh', 'full' => 'Al-Fateh Saudi Club', 'slug' => 'al-fateh', 'code' => 'fat', 'color' => '#1E7A34', 'stadium' => 'Maydan Tamweel Aloula'],
        ['name' => 'Al-Taawoun', 'full' => 'Al-Taawoun Football Club', 'slug' => 'al-taawoun', 'code' => 'taa', 'color' => '#0B3D91', 'stadium' => 'King Abdullah Sports City Stadium Buraidah'],
        ['name' => 'Al-Fayha', 'full' => 'Al-Fayha Football Club', 'slug' => 'al-fayha', 'code' => 'fay', 'color' => '#0E8F6B', 'stadium' => 'Majmaah Sports City Stadium'],
        ['name' => 'Al-Hazem', 'full' => 'Al-Hazem Saudi Club', 'slug' => 'al-hazem', 'code' => 'haz', 'color' => '#2E7D32', 'stadium' => 'Al-Hazem Club Stadium'],
        ['name' => 'Al-Khaleej', 'full' => 'Al-Khaleej Club', 'slug' => 'al-khaleej', 'code' => 'kha', 'color' => '#1565C0', 'stadium' => 'Prince Mohamed bin Fahd Stadium'],
        ['name' => 'Al-Kholood', 'full' => 'Al-Kholood Club', 'slug' => 'al-kholood', 'code' => 'kho', 'color' => '#8E24AA', 'stadium' => 'Al-Hazem Club Stadium'],
        ['name' => 'Al-Najma', 'full' => 'Al-Najma Club', 'slug' => 'al-najma', 'code' => 'naj', 'color' => '#D4A017', 'stadium' => 'King Abdullah Sports City Stadium Buraidah'],
        ['name' => 'Al-Okhdood', 'full' => 'Al-Okhdood Club', 'slug' => 'al-okhdood', 'code' => 'okh', 'color' => '#0B6E4F', 'stadium' => 'Prince Hathloul bin Abdulaziz Sports City Stadium'],
        ['name' => 'Al-Qadsiah', 'full' => 'Al-Qadsiah Football Club', 'slug' => 'al-qadsiah', 'code' => 'qad', 'color' => '#C8102E', 'stadium' => 'Prince Mohamed bin Fahd Stadium'],
        ['name' => 'Al-Riyadh', 'full' => 'Al-Riyadh SC', 'slug' => 'al-riyadh', 'code' => 'riy', 'color' => '#3949AB', 'stadium' => 'SHG Arena'],
        ['name' => 'Damac', 'full' => 'Damac Football Club', 'slug' => 'damac', 'code' => 'dam', 'color' => '#5D1049', 'stadium' => 'Damac Club Stadium'],
        ['name' => 'Neom', 'full' => 'Neom Sports Club', 'slug' => 'neom', 'code' => 'neo', 'color' => '#0B4F4A', 'stadium' => 'King Khalid Sport City Stadium'],
    ];

    public function run(): void
    {
        // is_published is intentionally left out of these updateOrCreate
        // calls: it defaults to false on first insert (via the migration's
        // column default), but must never be touched on a re-run, or this
        // seeder would silently unpublish content an admin already approved.
        $league = League::updateOrCreate(
            ['slug' => 'saudi-pro-league'],
            [
                'name' => 'Saudi Pro League',
                'country' => 'Saudi Arabia',
                'flag_code' => 'sau',
                'season' => '2026-27',
                'total_matchdays' => 34,
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

        $kickoff = Carbon::create(2026, 8, 28, 20, 0, 0);
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
