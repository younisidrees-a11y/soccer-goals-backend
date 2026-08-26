<?php

namespace Database\Seeders;

use App\Models\League;
use App\Models\MatchFixture;
use App\Models\Standing;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Adds the English Championship: the league itself, its 24 real clubs,
 * and a full double round-robin fixture schedule as placeholder
 * scheduling until football-data:sync (external_code ELC) replaces it
 * with the real fixtures.
 *
 * Three of these clubs - Wolves, Burnley, West Ham - already existed as
 * unpublished rows from the site's original static Premier League build,
 * before the real 2026-27 PL roster correction found they'd actually
 * been relegated. Those rows are re-parented to this league (same club,
 * same history, just moved divisions) rather than duplicated - their
 * crests are refreshed with freshly-verified real images under the same
 * crest codes (wol/bur/whu) they already had.
 */
class ChampionshipSeeder extends Seeder
{
    /** slug => api_football_id, for the 3 clubs already in the database under a different league. */
    private const REPARENT = [
        'wolverhampton-wanderers' => 39,
        'burnley' => 44,
        'west-ham-united' => 48,
    ];

    private const NEW_CLUBS = [
        ['name' => 'Watford', 'full' => 'Watford Football Club', 'slug' => 'watford', 'code' => 'wat', 'color' => '#FBEE23', 'stadium' => 'Vicarage Road', 'capacity' => 22200, 'founded' => 1881, 'api_id' => 38],
        ['name' => 'Southampton', 'full' => 'Southampton Football Club', 'slug' => 'southampton', 'code' => 'sou', 'color' => '#D71920', 'stadium' => "St. Mary's Stadium", 'capacity' => 32689, 'founded' => 1885, 'api_id' => 41],
        ['name' => 'Cardiff City', 'full' => 'Cardiff City Football Club', 'slug' => 'cardiff-city', 'code' => 'car', 'color' => '#0070B5', 'stadium' => 'Cardiff City Stadium', 'capacity' => 33280, 'founded' => 1889, 'api_id' => 43],
        ['name' => 'Birmingham City', 'full' => 'Birmingham City Football Club', 'slug' => 'birmingham-city', 'code' => 'brm', 'color' => '#1B458F', 'stadium' => "St Andrew's Stadium", 'capacity' => 30009, 'founded' => 1875, 'api_id' => 54],
        ['name' => 'Bristol City', 'full' => 'Bristol City Football Club', 'slug' => 'bristol-city', 'code' => 'brc', 'color' => '#E21C21', 'stadium' => 'Ashton Gate Stadium', 'capacity' => 27000, 'founded' => 1894, 'api_id' => 56],
        ['name' => 'Millwall', 'full' => 'Millwall Football Club', 'slug' => 'millwall', 'code' => 'mlw', 'color' => '#001C42', 'stadium' => 'The Den', 'capacity' => 20146, 'founded' => 1885, 'api_id' => 58],
        ['name' => 'Preston North End', 'full' => 'Preston North End Football Club', 'slug' => 'preston-north-end', 'code' => 'pne', 'color' => '#001C58', 'stadium' => 'Deepdale', 'capacity' => 23408, 'founded' => 1863, 'api_id' => 59],
        ['name' => 'West Brom', 'full' => 'West Bromwich Albion Football Club', 'slug' => 'west-bromwich-albion', 'code' => 'wba', 'color' => '#122F67', 'stadium' => 'The Hawthorns', 'capacity' => 28003, 'founded' => 1878, 'api_id' => 60],
        ['name' => 'Sheffield United', 'full' => 'Sheffield United Football Club', 'slug' => 'sheffield-united', 'code' => 'shu', 'color' => '#EE2737', 'stadium' => 'Bramall Lane', 'capacity' => 32702, 'founded' => 1889, 'api_id' => 62],
        ['name' => 'Blackburn Rovers', 'full' => 'Blackburn Rovers Football Club', 'slug' => 'blackburn-rovers', 'code' => 'blb', 'color' => '#009EE0', 'stadium' => 'Ewood Park', 'capacity' => 31367, 'founded' => 1875, 'api_id' => 67],
        ['name' => 'Bolton Wanderers', 'full' => 'Bolton Wanderers Football Club', 'slug' => 'bolton-wanderers', 'code' => 'blt', 'color' => '#002F5F', 'stadium' => 'Toughsheet Community Stadium', 'capacity' => 28723, 'founded' => 1874, 'api_id' => 68],
        ['name' => 'Derby County', 'full' => 'Derby County Football Club', 'slug' => 'derby-county', 'code' => 'drb', 'color' => '#14213D', 'stadium' => 'Pride Park Stadium', 'capacity' => 33597, 'founded' => 1884, 'api_id' => 69],
        ['name' => 'Middlesbrough', 'full' => 'Middlesbrough Football Club', 'slug' => 'middlesbrough', 'code' => 'mid', 'color' => '#DC0714', 'stadium' => 'Riverside Stadium', 'capacity' => 34988, 'founded' => 1876, 'api_id' => 70],
        ['name' => 'Norwich City', 'full' => 'Norwich City Football Club', 'slug' => 'norwich-city', 'code' => 'nor', 'color' => '#00A650', 'stadium' => 'Carrow Road', 'capacity' => 27606, 'founded' => 1902, 'api_id' => 71],
        ['name' => 'QPR', 'full' => 'Queens Park Rangers Football Club', 'slug' => 'queens-park-rangers', 'code' => 'qpr', 'color' => '#1D5BA4', 'stadium' => 'MATRADE Loftus Road', 'capacity' => 18360, 'founded' => 1885, 'api_id' => 72],
        ['name' => 'Stoke City', 'full' => 'Stoke City Football Club', 'slug' => 'stoke-city', 'code' => 'sto', 'color' => '#E03A3E', 'stadium' => 'bet365 Stadium', 'capacity' => 30089, 'founded' => 1868, 'api_id' => 75],
        ['name' => 'Swansea City', 'full' => 'Swansea City Association Football Club', 'slug' => 'swansea-city', 'code' => 'swa', 'color' => '#121212', 'stadium' => 'Swansea.com Stadium', 'capacity' => 21088, 'founded' => 1912, 'api_id' => 76],
        ['name' => 'Charlton Athletic', 'full' => 'Charlton Athletic Football Club', 'slug' => 'charlton-athletic', 'code' => 'cha', 'color' => '#D2122E', 'stadium' => 'The Valley', 'capacity' => 27111, 'founded' => 1905, 'api_id' => 1335],
        ['name' => 'Portsmouth', 'full' => 'Portsmouth Football Club', 'slug' => 'portsmouth', 'code' => 'pom', 'color' => '#001489', 'stadium' => 'Fratton Park', 'capacity' => 20821, 'founded' => 1898, 'api_id' => 1355],
        ['name' => 'Lincoln City', 'full' => 'Lincoln City Football Club', 'slug' => 'lincoln-city', 'code' => 'lin', 'color' => '#C8102E', 'stadium' => 'LNER Stadium', 'capacity' => 10780, 'founded' => 1884, 'api_id' => 1379],
        ['name' => 'Wrexham', 'full' => 'Wrexham Association Football Club', 'slug' => 'wrexham', 'code' => 'wxm', 'color' => '#C8102E', 'stadium' => 'STōK Cae Ras', 'capacity' => 19118, 'founded' => 1872, 'api_id' => 1837],
    ];

    public function run(): void
    {
        $league = League::updateOrCreate(
            ['slug' => 'championship'],
            [
                'name' => 'Championship',
                'country' => 'England',
                'flag_code' => 'eng',
                'season' => '2026-27',
                'total_matchdays' => 46,
                'api_football_id' => 40,
                'about_text' => "The Championship is English football's second tier and one of the most competitive, financially significant divisions anywhere in the world - the prize for finishing in the automatic promotion places or winning the play-offs is a place in the Premier League, widely described as the richest single match in football. Twenty-four clubs play a full 46-game home-and-away season, a longer and more physically demanding campaign than almost any other major league.\nThe division regularly features clubs with genuine Premier League pedigree working their way back up, alongside historic names fighting to rediscover former glories, which keeps attendances and interest unusually high for a second-tier competition.",
            ]
        );

        $teamModels = [];

        foreach (self::REPARENT as $slug => $apiId) {
            $team = Team::where('slug', $slug)->first();

            if (! $team) {
                $this->command?->warn("Expected existing team not found: {$slug} - skipping reparent.");

                continue;
            }

            $team->update(['league_id' => $league->id, 'api_football_id' => $apiId, 'is_published' => true]);
            $teamModels[$slug] = $team;
        }

        foreach (self::NEW_CLUBS as $t) {
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

        $kickoff = Carbon::create(2026, 8, 28, 19, 45, 0);
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
