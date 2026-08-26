<?php

namespace Database\Seeders;

use App\Models\League;
use App\Models\MatchFixture;
use App\Models\Standing;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Adds the Primeira Liga: the league itself, its 18 real clubs, and a
 * placeholder fixture schedule until football-data:sync (external_code
 * PPL, confirmed covered) replaces it with the real season.
 *
 * Moreirense, Alverca and Estrela have no real founded_year in the API
 * (Estrela returns 0, which isn't a real year either) - left null
 * rather than guessed, same treatment as Al Diriyah and San Diego FC
 * earlier this session.
 */
class PrimeiraLigaSeeder extends Seeder
{
    private const TEAMS = [
        ['name' => 'Benfica', 'full' => 'Sport Lisboa e Benfica', 'slug' => 'benfica', 'code' => 'ben', 'color' => '#E31B23', 'stadium' => 'Estádio do Sport Lisboa e Benfica (da Luz)', 'capacity' => 65647, 'founded' => 1904, 'api_id' => 211],
        ['name' => 'FC Porto', 'full' => 'Futebol Clube do Porto', 'slug' => 'fc-porto', 'code' => 'por', 'color' => '#003DA5', 'stadium' => 'Estádio Do Dragão', 'capacity' => 50399, 'founded' => 1893, 'api_id' => 212],
        ['name' => 'Marítimo', 'full' => 'Clube Desportivo Nacional Marítimo', 'slug' => 'maritimo', 'code' => 'mar', 'color' => '#009640', 'stadium' => 'Estádio do Marítimo', 'capacity' => 10932, 'founded' => 1910, 'api_id' => 214],
        ['name' => 'Moreirense', 'full' => 'Moreirense Futebol Clube', 'slug' => 'moreirense', 'code' => 'mor', 'color' => '#00693E', 'stadium' => 'Parque Desportivo Comendador Joaquim de Almeida Freitas', 'capacity' => 13000, 'founded' => null, 'api_id' => 215],
        ['name' => 'SC Braga', 'full' => 'Sporting Clube de Braga', 'slug' => 'sc-braga', 'code' => 'bra', 'color' => '#C8102E', 'stadium' => 'Estádio Municipal de Braga', 'capacity' => 30286, 'founded' => 1921, 'api_id' => 217],
        ['name' => 'Vitória SC', 'full' => 'Vitória Sport Clube', 'slug' => 'vitoria-sc', 'code' => 'vit', 'color' => '#1A1A1A', 'stadium' => 'Estádio Dom Afonso Henriques', 'capacity' => 30165, 'founded' => 1922, 'api_id' => 224],
        ['name' => 'Nacional', 'full' => 'Clube Desportivo Nacional', 'slug' => 'cd-nacional', 'code' => 'nac', 'color' => '#F7D117', 'stadium' => 'Estádio da Madeira', 'capacity' => 5200, 'founded' => 1910, 'api_id' => 225],
        ['name' => 'Rio Ave', 'full' => 'Rio Ave Futebol Clube', 'slug' => 'rio-ave', 'code' => 'riv', 'color' => '#007A3D', 'stadium' => 'Estádio do Rio Ave Futebol Clube', 'capacity' => 12815, 'founded' => 1939, 'api_id' => 226],
        ['name' => 'Santa Clara', 'full' => 'Clube Desportivo Santa Clara', 'slug' => 'santa-clara', 'code' => 'stc', 'color' => '#D2122E', 'stadium' => 'Estádio de São Miguel', 'capacity' => 13277, 'founded' => 1921, 'api_id' => 227],
        ['name' => 'Sporting CP', 'full' => 'Sporting Clube de Portugal', 'slug' => 'sporting-cp', 'code' => 'spo', 'color' => '#00693E', 'stadium' => 'Estádio José Alvalade', 'capacity' => 50466, 'founded' => 1906, 'api_id' => 228],
        ['name' => 'Estoril', 'full' => 'Grupo Desportivo Estoril Praia', 'slug' => 'estoril', 'code' => 'est', 'color' => '#FFC72C', 'stadium' => 'Estádio António Coimbra da Mota', 'capacity' => 8015, 'founded' => 1939, 'api_id' => 230],
        ['name' => 'Académico Viseu', 'full' => 'Académico Clube de Viseu', 'slug' => 'academico-viseu', 'code' => 'acv', 'color' => '#1A1A1A', 'stadium' => 'Estádio Municipal do Fontelo', 'capacity' => 14368, 'founded' => 1914, 'api_id' => 238],
        ['name' => 'Arouca', 'full' => 'Futebol Clube de Arouca', 'slug' => 'arouca', 'code' => 'aro', 'color' => '#FFD100', 'stadium' => 'Estádio Municipal de Arouca', 'capacity' => 7000, 'founded' => 1951, 'api_id' => 240],
        ['name' => 'Famalicão', 'full' => 'Futebol Clube de Famalicão', 'slug' => 'famalicao', 'code' => 'fam', 'color' => '#F2C300', 'stadium' => 'Estádio Municipal 22 de Junho', 'capacity' => 8000, 'founded' => 1931, 'api_id' => 242],
        ['name' => 'Gil Vicente', 'full' => 'Gil Vicente Futebol Clube', 'slug' => 'gil-vicente', 'code' => 'gvc', 'color' => '#C8102E', 'stadium' => 'Estádio Cidade de Barcelos', 'capacity' => 12046, 'founded' => 1924, 'api_id' => 762],
        ['name' => 'Casa Pia', 'full' => 'Casa Pia Atlético Clube', 'slug' => 'casa-pia', 'code' => 'csp', 'color' => '#C8102E', 'stadium' => 'Estádio Nacional', 'capacity' => 38000, 'founded' => 1920, 'api_id' => 4716],
        ['name' => 'Alverca', 'full' => 'Futebol Clube de Alverca', 'slug' => 'alverca', 'code' => 'alv', 'color' => '#00693E', 'stadium' => 'Complexo Desportivo FC Alverca', 'capacity' => 7705, 'founded' => null, 'api_id' => 4724],
        ['name' => 'Estrela', 'full' => 'Clube de Futebol Estrela da Amadora', 'slug' => 'estrela-da-amadora', 'code' => 'etr', 'color' => '#C8102E', 'stadium' => 'Campo Municipal dos Prazeres', 'capacity' => 800, 'founded' => null, 'api_id' => 15130],
    ];

    public function run(): void
    {
        $league = League::updateOrCreate(
            ['slug' => 'primeira-liga'],
            [
                'name' => 'Primeira Liga',
                'country' => 'Portugal',
                'flag_code' => 'prt',
                'season' => '2026-27',
                'total_matchdays' => 34,
                'api_football_id' => 94,
                'about_text' => "The Primeira Liga has long been shaped by the 'Big Three' - Benfica, Porto and Sporting CP - who between them have won almost every league title in the competition's history, though Braga and Vitória SC have pushed into genuine title-challenger territory in recent seasons. Eighteen clubs play a full home-and-away season across a league renowned for developing and reselling talent at some of the highest profit margins in European football.\nPorto's Estádio Do Dragão and Benfica's Estádio da Luz regularly rank among the best-attended and loudest venues in the sport, and the league's clubs remain a reliable pipeline of players who go on to star at Europe's richest clubs.",
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

        $kickoff = Carbon::create(2026, 8, 29, 20, 30, 0);
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
