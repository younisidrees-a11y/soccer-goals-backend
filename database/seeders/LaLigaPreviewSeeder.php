<?php

namespace Database\Seeders;

use App\Models\League;
use App\Models\MatchFixture;
use Illuminate\Database\Seeder;

/**
 * Replaces the formulaic, repeated fixture preview text for every La Liga
 * match with varied, factual, plain-English blurbs built from real team
 * traits combined with a large pool of sentence templates.
 */
class LaLigaPreviewSeeder extends Seeder
{
    private const TEAMS = [
        'Real Madrid' => [
            'stadium' => 'Santiago Bernabeu',
            'traits' => [
                'the most successful club in European football history',
                'a squad built to compete for the biggest trophies every season',
                'one of the most recognisable clubs in the world game',
            ],
        ],
        'Barcelona' => [
            'stadium' => 'Estadi Olimpic Lluis Companys',
            'traits' => [
                'a club built around quick, passing football',
                'a side shaped by the famous La Masia academy',
                'one of the best-supported clubs anywhere in the world',
            ],
        ],
        'Atletico Madrid' => [
            'stadium' => 'Civitas Metropolitano',
            'traits' => [
                'a team known for its organisation and fighting spirit',
                'a side that makes life hard for anyone who visits',
                'one of the most disciplined defensive units in Spain',
            ],
        ],
        'Athletic Bilbao' => [
            'stadium' => 'San Mames',
            'traits' => [
                'a club that only signs players with Basque roots',
                'a side with one of the loudest, proudest crowds in Spain',
                'a team with a long history in Spanish cup football',
            ],
        ],
        'Villarreal' => [
            'stadium' => 'Estadio de la Ceramica',
            'traits' => [
                'a smaller club with a reputation for smart recruitment',
                'a side that has punched above its weight in Europe for years',
                'known to Spanish fans as the Yellow Submarine',
            ],
        ],
        'Sevilla' => [
            'stadium' => 'Ramon Sanchez Pizjuan',
            'traits' => [
                'the most successful club in Europa League history',
                'a side that raises its game in front of a fierce home crowd',
                'a club with a strong European pedigree',
            ],
        ],
        'Real Betis' => [
            'stadium' => 'Estadio Benito Villamarin',
            'traits' => [
                'a club with one of the most loyal fanbases in Spain',
                'a team capable of turning games in their favour at home',
                "Seville's other big club, with a proud local following",
            ],
        ],
        'Real Sociedad' => [
            'stadium' => 'Reale Arena',
            'traits' => [
                'a Basque club known for developing young talent',
                'a side that plays neat, patient football',
                'a team with a tight, noisy home ground',
            ],
        ],
        'Valencia' => [
            'stadium' => 'Mestalla',
            'traits' => [
                'a three-time Spanish champion with a rich history',
                'a club whose home ground is one of the oldest and loudest in Spain',
                'a side still chasing a return to the very top of the table',
            ],
        ],
        'Alaves' => [
            'stadium' => 'Mendizorroza',
            'traits' => [
                'a smaller Basque club known for organised, resilient defending',
                'a team that has made a habit of staying in the top flight',
                'a side that is always difficult to break down',
            ],
        ],
        'Celta Vigo' => [
            'stadium' => 'Estadio Abanca-Balaidos',
            'traits' => [
                'a Galician club known for attractive, technical football',
                'a team backed by one of the most loyal regional fanbases in Spain',
                'a side that likes to play out from the back',
            ],
        ],
        'Espanyol' => [
            'stadium' => 'RCDE Stadium',
            'traits' => [
                "Barcelona's other club, with a proud history of its own",
                'a side that takes pride in making life difficult for bigger clubs',
                'a team with a passionate, if often overlooked, fanbase',
            ],
        ],
        'Getafe' => [
            'stadium' => 'Coliseum Alfonso Perez',
            'traits' => [
                'a team known for being physical and hard to beat',
                'an unfashionable club that regularly outperforms expectations',
                'a side built on hard work rather than star names',
            ],
        ],
        'Girona' => [
            'stadium' => 'Estadi Montilivi',
            'traits' => [
                'a smaller Catalan club that has risen quickly in recent seasons',
                'a side known for bold, attacking football',
                'a team that has surprised bigger clubs in recent years',
            ],
        ],
        'Las Palmas' => [
            'stadium' => 'Estadio Gran Canaria',
            'traits' => [
                'a Canary Islands club known for patient, passing football',
                'a side backed by passionate island support',
                'a team that travels further than anyone else in the league',
            ],
        ],
        'Leganes' => [
            'stadium' => 'Estadio Municipal de Butarque',
            'traits' => [
                'a modest Madrid-area club known for punching above its weight',
                'a side that is tough to beat on its own ground',
                'a team that thrives on being the underdog',
            ],
        ],
        'Mallorca' => [
            'stadium' => 'Visit Mallorca Estadi',
            'traits' => [
                'an island club known for its resilience',
                'a side that is always tricky to face at home',
                'a team backed by a lively, tight-knit crowd',
            ],
        ],
        'Osasuna' => [
            'stadium' => 'El Sadar',
            'traits' => [
                'a Navarre club known for a fiercely loyal fanbase',
                'a side that plays with real physical intensity',
                'a team whose home ground is one of the toughest visits in Spain',
            ],
        ],
        'Rayo Vallecano' => [
            'stadium' => 'Campo de Futbol de Vallecas',
            'traits' => [
                'a working-class Madrid club with one of the most passionate crowds in Spain',
                'a side that plays with real energy in a compact, noisy stadium',
                'a team known for never giving up on a game',
            ],
        ],
        'Valladolid' => [
            'stadium' => 'Estadio Jose Zorrilla',
            'traits' => [
                'a Castilian club with a fighting history between divisions',
                'a side that relies on effort and organisation',
                'a team determined to establish itself back in the top flight',
            ],
        ],
    ];

    private const HOME_TEMPLATES = [
        '{home} welcome {away} to {stadium}, {trait}.',
        '{home} host {away} at {stadium}. They are {trait}, and will fancy their chances in front of their own fans.',
        "It's a home game for {home} against {away}. {traitCap}, and {stadium} has been a tough place to visit this season.",
        '{home} take on {away} at {stadium}, looking to make home advantage count. This is {trait}.',
        'At {stadium}, {home} face {away} knowing their supporters can make a real difference. {home} are {trait}.',
        '{home} are back at {stadium} to face {away}. As {trait}, they will want to set the tone early.',
        '{home} return to {stadium} for a home fixture against {away}, aiming to build on the fact they are {trait}.',
        'This is a big home test for {home} against {away}. Playing at {stadium}, they remain {trait}.',
    ];

    private const AWAY_TEMPLATES = [
        '{away} travel to {stadium} to face {home}, aware that away form has often decided results between these two.',
        "It's a trip on the road for {away}, who are {trait} and will need that quality to get something at {stadium}.",
        '{away} make the journey to {stadium}. As {trait}, they will believe they can cause problems for {home}.',
        '{away} head away from home to take on {home}. {traitCap}, which should help them in a tough away fixture.',
        'On the road again, {away} arrive at {stadium} knowing {home} will be difficult to beat on home soil.',
        '{away} face a stern away test at {stadium}. They go into the game as {trait}.',
        '{away} will need a disciplined away performance against {home}. They remain {trait}.',
        "For {away}, this is another chance to prove themselves on the road. As {trait}, they won't be overawed by {stadium}.",
    ];

    public function run(): void
    {
        $league = League::where('slug', 'la-liga')->first();

        if (! $league) {
            return;
        }

        $fixtures = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->where('league_id', $league->id)
            ->get();

        foreach ($fixtures as $fixture) {
            $home = $fixture->homeTeam->name;
            $away = $fixture->awayTeam->name;

            if (! isset(self::TEAMS[$home], self::TEAMS[$away])) {
                continue;
            }

            $venue = self::TEAMS[$home]['stadium'];

            $fixture->update([
                'home_preview_note' => $this->buildText(self::HOME_TEMPLATES, self::TEAMS[$home]['traits'], $venue, $home, $away, "home-{$fixture->id}"),
                'away_preview_note' => $this->buildText(self::AWAY_TEMPLATES, self::TEAMS[$away]['traits'], $venue, $home, $away, "away-{$fixture->id}"),
            ]);
        }
    }

    private function buildText(array $templates, array $traits, string $venue, string $home, string $away, string $seed): string
    {
        $template = $templates[$this->pick($seed.'-tpl', count($templates))];
        $trait = $traits[$this->pick($seed.'-trait', count($traits))];

        return str_replace(
            ['{home}', '{away}', '{stadium}', '{trait}', '{traitCap}'],
            [$home, $away, $venue, $trait, ucfirst($trait)],
            $template
        );
    }

    private function pick(string $seed, int $count): int
    {
        return hexdec(substr(md5($seed), 0, 8)) % $count;
    }
}
