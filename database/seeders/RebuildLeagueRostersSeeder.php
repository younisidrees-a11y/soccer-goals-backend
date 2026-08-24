<?php

namespace Database\Seeders;

use App\Models\League;
use App\Models\MatchFixture;
use App\Models\Standing;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Corrects the Premier League and La Liga rosters to match the real
 * 2026-27 season - the site's original seed data had drifted stale
 * (verified independently against Wikipedia season articles, the official
 * laliga.com table, and ESPN's live Premier League table, all agreeing).
 *
 * Creates the 8 real clubs the site was missing, then fully regenerates
 * both leagues' fixture schedules (delete + rebuild via the same double
 * round-robin generator used for Saudi Pro League) using the corrected
 * 20-club rosters. The 8 outgoing clubs are NOT deleted, only unpublished
 * separately - their historical data stays intact.
 *
 * Deliberately wipes existing results too: the automated matches:progress
 * pipeline will naturally re-simulate and re-publish every matchday whose
 * kickoff date has already passed, using the corrected rosters, the next
 * time it runs - no manually fabricated history needed.
 */
class RebuildLeagueRostersSeeder extends Seeder
{
    private const NEW_CLUBS = [
        'hull-city' => [
            'league_slug' => 'premier-league',
            'name' => 'Hull City',
            'full_name' => 'Hull City Association Football Club',
            'crest_code' => 'hul',
            'color_hex' => '#F18A01',
            'stadium' => 'MKM Stadium',
            'stadium_capacity' => '25,586',
            'manager' => 'Sergej Jakirović',
            'manager_facts' => 'Bosnian nationality (also holds Croatian citizenship). Appointed Hull City head coach in June 2025. Previously managed Kayserispor in Turkey\'s Süper Lig (January-June 2025), guiding them to safety from relegation before departing by mutual consent.',
            'manager_photo_path' => 'assets/img/managers/hull-city.png',
            'founded_year' => 1904,
            'honours_facts' => 'Third Division North / Third Division / League One title: 4 (1932-33, 1948-49, 1965-66, 2020-21)
Championship play-off winners: 3 (2008, 2016, 2026)',
        ],
        'ipswich-town' => [
            'league_slug' => 'premier-league',
            'name' => 'Ipswich Town',
            'full_name' => 'Ipswich Town Football Club',
            'crest_code' => 'ips',
            'color_hex' => '#0333A0',
            'stadium' => 'Portman Road',
            'stadium_capacity' => '30,056',
            'manager' => 'Gary O\'Neil',
            'manager_facts' => 'English nationality. Appointed Ipswich Town head coach in June 2026 on a 3-year deal, succeeding Kieran McKenna, who stepped down after four-and-a-half years. Previously managed RC Strasbourg in France\'s Ligue 1.',
            'founded_year' => 1878,
            'honours_facts' => 'First Division title: 1 (1961-62)
Second Division title: 3 (1960-61, 1967-68, 1991-92)
Third Division South title: 2 (1953-54, 1956-57)
FA Cup: 1 (1977-78)
UEFA Cup: 1 (1980-81)
Texaco Cup: 1 (1972-73)',
        ],
        'coventry-city' => [
            'league_slug' => 'premier-league',
            'name' => 'Coventry City',
            'full_name' => 'Coventry City Football Club',
            'crest_code' => 'cov',
            'color_hex' => '#62B5E5',
            'stadium' => 'Coventry Building Society Arena',
            'stadium_capacity' => '32,609',
            'manager' => 'Frank Lampard',
            'manager_facts' => 'English nationality. Appointed Coventry City head coach in November 2024. Previously managed Everton (2022-2023), with a brief spell as Chelsea caretaker manager in between (2023).',
            'manager_photo_path' => 'assets/img/managers/coventry-city.jpg',
            'founded_year' => 1883,
            'honours_facts' => 'Second Division / Championship title: 2 (1966-67, 2025-26)
Third Division South / Third Division / League One title: 3 (1935-36, 1963-64, 2019-20)
FA Cup: 1 (1986-87)
EFL Trophy: 1 (2016-17)',
        ],
        'deportivo-de-la-coruna' => [
            'league_slug' => 'la-liga',
            'name' => 'Deportivo',
            'full_name' => 'Real Club Deportivo de La Coruña, S.A.D.',
            'crest_code' => 'dep',
            'color_hex' => '#005EB8',
            'stadium' => 'Estadio Riazor (Abanca-Riazor)',
            'stadium_capacity' => '32,490',
            'manager' => 'Antonio Hidalgo',
            'manager_facts' => 'Spanish nationality. Appointed Deportivo de La Coruña head coach in June 2025. Previously managed SD Huesca (2023-2025), leaving ten days before joining Deportivo.',
            'manager_photo_path' => 'assets/img/managers/deportivo-de-la-coruna.jpg',
            'founded_year' => 1906,
            'honours_facts' => 'La Liga title: 1 (1999-2000)
Segunda División title: 5 (1961-62, 1963-64, 1965-66, 1967-68, 2011-12)
Copa del Rey: 2 (1994-95, 2001-02)
Supercopa de España: 3 (1995, 2000, 2002)',
        ],
        'racing-santander' => [
            'league_slug' => 'la-liga',
            'name' => 'Racing Santander',
            'full_name' => 'Real Racing Club de Santander, S.A.D.',
            'crest_code' => 'rac',
            'color_hex' => '#369028',
            'stadium' => 'Campos de Sport de El Sardinero',
            'stadium_capacity' => '22,222',
            'manager' => 'José Alberto López',
            'manager_facts' => 'Spanish nationality. Appointed Racing de Santander head coach in December 2022. Previously managed Málaga CF (2021-2022), until his dismissal after a 0-5 defeat to UD Ibiza. Led Racing to the 2025-26 Segunda División title and promotion to La Liga.',
            'manager_photo_path' => 'assets/img/managers/racing-santander.png',
            'founded_year' => 1913,
            'honours_facts' => 'Segunda División title: 3 (1949-50, 1959-60, 2025-26)
Tercera División / Primera División RFEF title: 4 (1943-44, 1947-48, 1969-70, 2021-22)',
        ],
        'elche' => [
            'league_slug' => 'la-liga',
            'name' => 'Elche',
            'full_name' => 'Elche Club de Fútbol, S.A.D.',
            'crest_code' => 'elc',
            'color_hex' => '#007A3D',
            'stadium' => 'Estadio Manuel Martínez Valero',
            'stadium_capacity' => '31,388',
            'manager' => 'Martín Anselmi',
            'manager_facts' => 'Argentine nationality. Appointed Elche CF head coach in June 2026. Previously managed Botafogo in Brazil, leaving in March 2026.',
            'manager_photo_path' => 'assets/img/managers/elche.png',
            'founded_year' => 1923,
            'honours_facts' => 'Segunda División title: 2 (1958-59, 2012-13)',
        ],
        'malaga' => [
            'league_slug' => 'la-liga',
            'name' => 'Malaga',
            'full_name' => 'Málaga Club de Fútbol, S.A.D.',
            'crest_code' => 'mal',
            'color_hex' => '#103E9E',
            'stadium' => 'Estadio La Rosaleda',
            'stadium_capacity' => '30,044',
            'manager' => 'Juan Francisco Funes',
            'manager_facts' => 'Spanish nationality. Appointed Málaga CF first-team head coach in November 2025, promoted from within the club after leading the reserve side, Atlético Malagueño (2020-2025), to promotion to Segunda Federación.',
            'founded_year' => 1948,
            'honours_facts' => 'Segunda División title: 1 (1998-99)
Segunda División B title: 1 (1997-98)
Tercera División title: 3 (1963-64, 1992-93, 1994-95)',
        ],
        'levante' => [
            'league_slug' => 'la-liga',
            'name' => 'Levante',
            'full_name' => 'Levante Unión Deportiva, S.A.D.',
            'crest_code' => 'lev',
            'color_hex' => '#661424',
            'stadium' => 'Estadi Ciutat de València',
            'stadium_capacity' => '26,354',
            'manager' => 'Luís Castro',
            'manager_facts' => 'Portuguese nationality. Appointed Levante UD head coach in December 2025. Previously managed FC Nantes in France\'s Ligue 1 (June-December 2025).',
            'founded_year' => 1909,
            'honours_facts' => 'Segunda División title: 3 (2003-04, 2016-17, 2024-25)
Segunda División B title: 5 (1978-79, 1988-89, 1994-95, 1995-96, 1998-99)
Tercera División title: 7 (1931-32, 1943-44, 1945-46, 1953-54, 1955-56, 1972-73, 1975-76)
Copa de la España Libre: 1 (1937)',
        ],
    ];

    private const ROSTERS = [
        'premier-league' => [
            'arsenal', 'aston-villa', 'afc-bournemouth', 'brentford', 'brighton-hove-albion',
            'chelsea', 'crystal-palace', 'everton', 'fulham', 'leeds-united', 'liverpool',
            'manchester-city', 'manchester-united', 'newcastle-united', 'nottingham-forest',
            'sunderland', 'tottenham-hotspur',
            'hull-city', 'ipswich-town', 'coventry-city',
        ],
        'la-liga' => [
            'deportivo-alaves', 'athletic-bilbao', 'atletico-madrid', 'barcelona', 'celta-vigo',
            'espanyol', 'getafe', 'osasuna', 'rayo-vallecano', 'real-betis', 'real-madrid',
            'real-sociedad', 'sevilla', 'valencia', 'villarreal',
            'deportivo-de-la-coruna', 'racing-santander', 'elche', 'malaga', 'levante',
        ],
    ];

    public function run(): void
    {
        $this->createNewClubs();
        $this->rebuildSchedules();
    }

    private function createNewClubs(): void
    {
        foreach (self::NEW_CLUBS as $slug => $data) {
            $league = League::where('slug', $data['league_slug'])->first();

            if (! $league) {
                $this->command?->error("League {$data['league_slug']} not found - skipping {$slug}.");
                continue;
            }

            unset($data['league_slug']);
            $data['league_id'] = $league->id;
            $data['is_published'] = true;

            Team::updateOrCreate(['slug' => $slug], $data);
            $this->command?->info("Created/updated club: {$slug}");
        }
    }

    private function rebuildSchedules(): void
    {
        foreach (self::ROSTERS as $leagueSlug => $slugs) {
            $league = League::where('slug', $leagueSlug)->first();

            if (! $league) {
                $this->command?->error("League {$leagueSlug} not found - aborting rebuild.");
                continue;
            }

            $teams = Team::whereIn('slug', $slugs)->get()->keyBy('slug');

            if ($teams->count() !== 20) {
                $missing = array_diff($slugs, $teams->keys()->all());
                $this->command?->error("{$leagueSlug}: expected 20 teams, found {$teams->count()}. Missing: ".implode(', ', $missing).'. Aborting rebuild for this league.');
                continue;
            }

            MatchFixture::where('league_id', $league->id)->delete();
            Standing::where('league_id', $league->id)->delete();

            $order = $slugs; // preserve a stable, deterministic order
            $order = array_map(fn ($s) => $teams[$s], $order);
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

            $md1Kickoff = Carbon::create(2026, 8, 13, 15, 0, 0);
            $md2Kickoff = Carbon::create(2026, 8, 16, 12, 30, 0);
            $venue = fn (Team $home) => $home->stadium;

            foreach ($firstLeg as $matchdayIndex => $pairings) {
                $matchday = $matchdayIndex + 1;
                $mdTime = $matchdayIndex === 0 ? $md1Kickoff : $md2Kickoff->copy()->addWeeks($matchdayIndex - 1);

                foreach ($pairings as [$home, $away]) {
                    MatchFixture::create([
                        'league_id' => $league->id,
                        'home_team_id' => $home->id,
                        'away_team_id' => $away->id,
                        'matchday' => $matchday,
                        'kickoff_at' => $mdTime,
                        'venue' => $venue($home),
                        'status' => 'scheduled',
                        'is_published' => true,
                    ]);

                    $matchday2 = $rounds + $matchday;
                    $md2Time = $md2Kickoff->copy()->addWeeks($rounds + $matchdayIndex - 1);
                    MatchFixture::create([
                        'league_id' => $league->id,
                        'home_team_id' => $away->id,
                        'away_team_id' => $home->id,
                        'matchday' => $matchday2,
                        'kickoff_at' => $md2Time,
                        'venue' => $venue($away),
                        'status' => 'scheduled',
                        'is_published' => true,
                    ]);
                }
            }

            $position = 1;
            foreach ($order as $team) {
                Standing::updateOrCreate(
                    ['league_id' => $league->id, 'team_id' => $team->id],
                    ['position' => $position++, 'zone' => 'none', 'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0, 'goals_for' => 0, 'goals_against' => 0, 'goal_difference' => 0, 'points' => 0]
                );
            }

            $this->command?->info("Rebuilt {$leagueSlug}: ".($rounds * $n)." fixtures for {$n} teams.");
        }
    }
}