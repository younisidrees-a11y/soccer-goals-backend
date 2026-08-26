<?php

namespace Database\Seeders;

use App\Models\League;
use App\Models\MatchFixture;
use App\Models\Standing;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Adds the Süper Lig: the league itself, its 18 real clubs (sourced from
 * API-Football's /teams endpoint - id, venue, capacity, founded year,
 * crest all real), and a full double round-robin fixture schedule as
 * placeholder scheduling. api-football:sync-fixtures super-lig (after
 * api-football:map-teams) replaces these with the real fixtures and
 * removes the placeholders.
 */
class SuperLigSeeder extends Seeder
{
    private const TEAMS = [
        ['name' => 'Beşiktaş', 'full' => 'Beşiktaş Jimnastik Kulübü', 'slug' => 'besiktas', 'code' => 'bjk', 'color' => '#1A1A1A', 'stadium' => 'Tüpraş Stadyumu', 'capacity' => 43500, 'founded' => 1903, 'api_id' => 549],
        ['name' => 'Başakşehir', 'full' => 'İstanbul Başakşehir Futbol Kulübü', 'slug' => 'basaksehir', 'code' => 'bas', 'color' => '#1B3F8B', 'stadium' => 'Başakşehir Fatih Terim Stadyumu', 'capacity' => 17319, 'founded' => 1990, 'api_id' => 564],
        ['name' => 'Konyaspor', 'full' => 'Konyaspor Kulübü', 'slug' => 'konyaspor', 'code' => 'kon', 'color' => '#00A650', 'stadium' => 'Medaş Konya Büyükşehir Stadyumu', 'capacity' => 42276, 'founded' => 1922, 'api_id' => 607],
        ['name' => 'Fenerbahçe', 'full' => 'Fenerbahçe Spor Kulübü', 'slug' => 'fenerbahce', 'code' => 'fen', 'color' => '#002E5D', 'stadium' => 'Chobani Stadyumu', 'capacity' => 47834, 'founded' => 1907, 'api_id' => 611],
        ['name' => 'Galatasaray', 'full' => 'Galatasaray Spor Kulübü', 'slug' => 'galatasaray', 'code' => 'gal', 'color' => '#B10041', 'stadium' => 'RAMS Park', 'capacity' => 53798, 'founded' => 1905, 'api_id' => 645],
        ['name' => 'Göztepe', 'full' => 'Göztepe Spor Kulübü', 'slug' => 'goztepe', 'code' => 'goz', 'color' => '#E4002B', 'stadium' => 'Gürsel Aksel Stadyumu', 'capacity' => 30035, 'founded' => 1925, 'api_id' => 994],
        ['name' => 'Alanyaspor', 'full' => 'Alanyaspor Kulübü', 'slug' => 'alanyaspor', 'code' => 'aly', 'color' => '#F7941D', 'stadium' => 'GAIN Park Stadium', 'capacity' => 15000, 'founded' => 1948, 'api_id' => 996],
        ['name' => 'Gençlerbirliği', 'full' => 'Gençlerbirliği Spor Kulübü', 'slug' => 'genclerbirligi', 'code' => 'gcb', 'color' => '#E30613', 'stadium' => 'Eryaman Stadyumu', 'capacity' => 20560, 'founded' => 1923, 'api_id' => 997],
        ['name' => 'Trabzonspor', 'full' => 'Trabzonspor Kulübü', 'slug' => 'trabzonspor', 'code' => 'tra', 'color' => '#7A0C2E', 'stadium' => 'Papara Park', 'capacity' => 41513, 'founded' => 1967, 'api_id' => 998],
        ['name' => 'Kasımpaşa', 'full' => 'Kasımpaşa Spor Kulübü', 'slug' => 'kasimpasa', 'code' => 'kas', 'color' => '#1D3461', 'stadium' => 'Recep Tayyip Erdoğan Stadyumu', 'capacity' => 14234, 'founded' => 1921, 'api_id' => 1004],
        ['name' => 'Çaykur Rizespor', 'full' => 'Çaykur Rizespor Kulübü', 'slug' => 'rizespor', 'code' => 'riz', 'color' => '#00A651', 'stadium' => 'Çaykur Didi Stadyumu', 'capacity' => 15558, 'founded' => 1953, 'api_id' => 1007],
        ['name' => 'Erzurumspor', 'full' => 'BB Erzurumspor Kulübü', 'slug' => 'erzurumspor', 'code' => 'erz', 'color' => '#0072BC', 'stadium' => 'Kazım Karabekir Stadyumu', 'capacity' => 23700, 'founded' => 2005, 'api_id' => 1009],
        ['name' => 'Gaziantep FK', 'full' => 'Gaziantep Futbol Kulübü', 'slug' => 'gaziantep-fk', 'code' => 'gzt', 'color' => '#C8102E', 'stadium' => 'Gaziantep Stadyumu', 'capacity' => 35558, 'founded' => 1988, 'api_id' => 3573],
        ['name' => 'Amed SK', 'full' => 'Amed Sportif Faaliyetler Kulübü', 'slug' => 'amed-sk', 'code' => 'amd', 'color' => '#007A3D', 'stadium' => 'Seyrantepe Spor Kompleksi Stadyumu', 'capacity' => 2500, 'founded' => 1990, 'api_id' => 3579],
        ['name' => 'Eyüpspor', 'full' => 'İstanbul Eyüpspor Kulübü', 'slug' => 'eyupspor', 'code' => 'eyu', 'color' => '#6A1B9A', 'stadium' => 'Recep Tayyip Erdoğan Stadyumu', 'capacity' => 14234, 'founded' => 1919, 'api_id' => 3588],
        ['name' => 'Samsunspor', 'full' => 'Samsunspor Kulübü', 'slug' => 'samsunspor', 'code' => 'sam', 'color' => '#D71920', 'stadium' => 'Samsun Yeni 19 Mayıs Stadyumu', 'capacity' => 34658, 'founded' => 1965, 'api_id' => 3603],
        ['name' => 'Çorum FK', 'full' => 'Çorum Futbol Kulübü', 'slug' => 'corum-fk', 'code' => 'cor', 'color' => '#C8102E', 'stadium' => 'Çorum Şehir Stadyumu', 'capacity' => 15000, 'founded' => 1997, 'api_id' => 6343],
        ['name' => 'Kocaelispor', 'full' => 'Kocaelispor Kulübü', 'slug' => 'kocaelispor', 'code' => 'koc', 'color' => '#1B5E20', 'stadium' => 'Yıldız Entegre Kocaeli Stadyumu', 'capacity' => 35000, 'founded' => 1966, 'api_id' => 7411],
    ];

    public function run(): void
    {
        // is_published is intentionally left out of these updateOrCreate
        // calls: it defaults to false on first insert, but must never be
        // touched on a re-run, or this seeder would silently unpublish
        // content an admin already approved.
        $league = League::updateOrCreate(
            ['slug' => 'super-lig'],
            [
                'name' => 'Süper Lig',
                'country' => 'Turkey',
                'flag_code' => 'tur',
                'season' => '2026-27',
                'total_matchdays' => 34,
                'api_football_id' => 203,
                'about_text' => "The Süper Lig is Turkey's top division and one of the most passionate football cultures in Europe, built on the fierce Istanbul rivalry between Galatasaray, Fenerbahçe and Beşiktaş. Eighteen clubs contest a full home-and-away season, with Trabzonspor the most consistent challenger from outside the big three in recent years.\nTurkish clubs have become increasingly active in the transfer market, drawing experienced players from across Europe and South America, and stadiums like RAMS Park and Chobani Stadyumu regularly produce some of the loudest atmospheres in the sport.",
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
