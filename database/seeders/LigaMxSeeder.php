<?php

namespace Database\Seeders;

use App\Models\League;
use App\Models\MatchFixture;
use App\Models\Standing;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Adds Liga MX: the league itself, its 18 real clubs (sourced from
 * API-Football's /teams endpoint - id, venue, capacity, founded year,
 * crest all real), and a full double round-robin fixture schedule as
 * placeholder scheduling. api-football:sync-fixtures liga-mx (after
 * api-football:map-teams) replaces these with the real Apertura/Clausura
 * fixtures and removes the placeholders.
 */
class LigaMxSeeder extends Seeder
{
    private const TEAMS = [
        ['name' => 'Chivas Guadalajara', 'full' => 'Club Deportivo Guadalajara', 'slug' => 'chivas-guadalajara', 'code' => 'chi', 'color' => '#B7093B', 'stadium' => 'Estadio AKRON', 'capacity' => 46609, 'founded' => 1906, 'api_id' => 2278],
        ['name' => 'Tigres UANL', 'full' => 'Club de Fútbol Tigres de la UANL', 'slug' => 'tigres-uanl', 'code' => 'tig', 'color' => '#FDB913', 'stadium' => 'Estadio Universitario de Nuevo León', 'capacity' => 42000, 'founded' => 1960, 'api_id' => 2279],
        ['name' => 'Club Tijuana', 'full' => 'Club Tijuana', 'slug' => 'club-tijuana', 'code' => 'tij', 'color' => '#C8102E', 'stadium' => 'Estadio Caliente', 'capacity' => 33333, 'founded' => 2006, 'api_id' => 2280],
        ['name' => 'Toluca', 'full' => 'Deportivo Toluca Fútbol Club', 'slug' => 'toluca', 'code' => 'tol', 'color' => '#C8102E', 'stadium' => 'Estadio Nemesio Díez', 'capacity' => 30000, 'founded' => 1917, 'api_id' => 2281],
        ['name' => 'Monterrey', 'full' => 'Club de Fútbol Monterrey', 'slug' => 'monterrey', 'code' => 'mty', 'color' => '#002D62', 'stadium' => 'Estadio BBVA', 'capacity' => 53500, 'founded' => 1945, 'api_id' => 2282],
        ['name' => 'Atlas', 'full' => 'Club Atlas de Guadalajara', 'slug' => 'atlas', 'code' => 'atl', 'color' => '#A6192E', 'stadium' => 'Estadio Jalisco', 'capacity' => 56713, 'founded' => 1916, 'api_id' => 2283],
        ['name' => 'Santos Laguna', 'full' => 'Club Santos Laguna', 'slug' => 'santos-laguna', 'code' => 'san', 'color' => '#007A33', 'stadium' => 'Estadio Corona', 'capacity' => 28914, 'founded' => 1983, 'api_id' => 2285],
        ['name' => 'Pumas UNAM', 'full' => 'Club Universidad Nacional', 'slug' => 'pumas-unam', 'code' => 'pum', 'color' => '#041E42', 'stadium' => 'Estadio Olímpico de Universitario', 'capacity' => 72449, 'founded' => 1954, 'api_id' => 2286],
        ['name' => 'Club América', 'full' => 'Club de Fútbol América', 'slug' => 'club-america', 'code' => 'ame', 'color' => '#0033A0', 'stadium' => 'Estadio Azteca', 'capacity' => 106187, 'founded' => 1916, 'api_id' => 2287],
        ['name' => 'Necaxa', 'full' => 'Club Necaxa', 'slug' => 'necaxa', 'code' => 'nec', 'color' => '#ED1C24', 'stadium' => 'Estadio Victoria', 'capacity' => 25500, 'founded' => 1923, 'api_id' => 2288],
        ['name' => 'León', 'full' => 'Club León', 'slug' => 'leon', 'code' => 'leo', 'color' => '#006341', 'stadium' => 'Estadio León (Nou Camp)', 'capacity' => 33943, 'founded' => 1944, 'api_id' => 2289],
        ['name' => 'Querétaro', 'full' => 'Club Querétaro', 'slug' => 'queretaro', 'code' => 'que', 'color' => '#002B5C', 'stadium' => 'Estadio Corregidora', 'capacity' => 34130, 'founded' => 1950, 'api_id' => 2290],
        ['name' => 'Puebla', 'full' => 'Club Puebla', 'slug' => 'puebla', 'code' => 'pue', 'color' => '#1D3E7C', 'stadium' => 'Estadio Cuauhtémoc', 'capacity' => 51726, 'founded' => 1944, 'api_id' => 2291],
        ['name' => 'Pachuca', 'full' => 'Club de Fútbol Pachuca', 'slug' => 'pachuca', 'code' => 'pac', 'color' => '#003DA5', 'stadium' => 'Estadio Hidalgo', 'capacity' => 30000, 'founded' => 1901, 'api_id' => 2292],
        ['name' => 'Cruz Azul', 'full' => 'Club Deportivo Social y Cultural Cruz Azul', 'slug' => 'cruz-azul', 'code' => 'cru', 'color' => '#0033A0', 'stadium' => 'Estadio Azteca', 'capacity' => 106187, 'founded' => 1927, 'api_id' => 2295],
        ['name' => 'FC Juárez', 'full' => 'Club de Fútbol Juárez', 'slug' => 'fc-juarez', 'code' => 'jua', 'color' => '#6CC24A', 'stadium' => 'Estadio Olímpico Benito Juárez', 'capacity' => 22300, 'founded' => 2015, 'api_id' => 2298],
        ['name' => 'Atlante', 'full' => 'Club de Fútbol Atlante', 'slug' => 'atlante', 'code' => 'atn', 'color' => '#0C2340', 'stadium' => 'Estadio Ciudad de los Deportes', 'capacity' => 32329, 'founded' => 1916, 'api_id' => 2312],
        ['name' => 'Atlético San Luis', 'full' => 'Club Atlético de San Luis', 'slug' => 'atletico-san-luis', 'code' => 'asl', 'color' => '#C8102E', 'stadium' => 'Estadio Alfonso Lastras Ramírez', 'capacity' => 26400, 'founded' => 2013, 'api_id' => 2314],
    ];

    public function run(): void
    {
        // is_published is intentionally left out of these updateOrCreate
        // calls: it defaults to false on first insert, but must never be
        // touched on a re-run, or this seeder would silently unpublish
        // content an admin already approved.
        $league = League::updateOrCreate(
            ['slug' => 'liga-mx'],
            [
                'name' => 'Liga MX',
                'country' => 'Mexico',
                'flag_code' => 'mex',
                'season' => '2026-27',
                'total_matchdays' => 34,
                'api_football_id' => 262,
                'about_text' => "Liga MX is Mexico's top professional division and one of the best-supported leagues anywhere in the world, with attendances that regularly rival Europe's biggest competitions. Eighteen clubs compete across two short, intense tournaments each season - the Apertura and the Clausura - each culminating in a knockout liguilla rather than a single champion crowned on points alone.\nClubs like Club América, Chivas Guadalajara, Cruz Azul and Tigres UANL carry some of the deepest rivalries in the sport, and giant venues such as the Estadio Azteca and Estadio BBVA give the league a scale most domestic competitions can't match.",
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
