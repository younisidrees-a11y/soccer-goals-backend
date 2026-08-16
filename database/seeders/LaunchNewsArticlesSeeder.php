<?php

namespace Database\Seeders;

use App\Models\League;
use App\Models\NewsArticle;
use App\Models\Team;
use Illuminate\Database\Seeder;

/**
 * Three editorially-written news articles submitted for review, one per
 * requested league. Left as pending_review so an editor approves them
 * before they go live, rather than being auto-published.
 */
class LaunchNewsArticlesSeeder extends Seeder
{
    public function run(): void
    {
        $premierLeague = League::where('slug', 'premier-league')->first();
        $laLiga = League::where('slug', 'la-liga')->first();
        $saudiProLeague = League::where('slug', 'saudi-pro-league')->first();
        $alHilal = Team::where('slug', 'al-hilal')->first();

        NewsArticle::updateOrCreate(
            ['slug' => 'man-city-2-1-chelsea-opening-day-2026'],
            [
                'title' => 'Manchester City Edge Chelsea 2-1 in Opening-Day Thriller at the Etihad',
                'dek' => "Pep Guardiola's side survived a late Chelsea rally to open the season with a hard-fought win at home.",
                'body' => <<<'BODY'
Manchester City began the defence of their crown with a nervy 2-1 win over Chelsea at the Etihad Stadium, a result that flattered the champions on a day when their new-look attack looked sharp but their defence looked far from settled.

City took an early lead through a well-worked move down the right, and for the first half hour they looked every bit the team that has dominated English football for the better part of a decade. Chelsea, patient and disciplined, waited for their moment and got one just after the hour mark, pulling the score level and turning the Etihad quiet for the first time all afternoon.

The winning goal came with twenty minutes left, a scrappy finish from close range that owed more to persistence than precision. It was enough. City held on through a tense final stretch, with Chelsea pushing men forward and testing a home defence that will need to sharpen up if the champions want to go the distance again this season.

For City, the result is exactly what opening day is meant to deliver: three points, however untidy. There will have been enough in the performance to know there is work to do at the back, but a win is a win, and it sends City into the second week of the season top of the table on goal difference.

Chelsea, for their part, will leave Manchester with few regrets. They matched City for large parts of the game and were unlucky not to leave with a point. The second-half performance will give real encouragement that this side can compete with anyone in the league this season, even away from home against the champions.

Both teams are back in action next weekend, with City on the road and Chelsea returning to Stamford Bridge. Neither performance will have settled many arguments about who the real contenders are this season, but it was, at the very least, a proper opening-day football match.
BODY,
                'image_path' => 'assets/img/news/man-city-2-1-chelsea-opening-day-2026.svg',
                'category' => 'match-report',
                'league_id' => $premierLeague?->id,
                'team_id' => 55, // Man City
                'match_id' => 1,
                'source' => 'human',
                'status' => 'pending_review',
                'author' => 'Marcus Ferreira',
                'meta_title' => 'Man City 2-1 Chelsea: Premier League Match Report | The Soccer Goals',
                'meta_description' => 'Manchester City held off a second-half Chelsea fightback to win 2-1 at the Etihad Stadium on the opening weekend of the Premier League season.',
                'meta_keywords' => 'Manchester City, Chelsea, Premier League, match report, Etihad Stadium, opening day',
            ]
        );

        NewsArticle::updateOrCreate(
            ['slug' => 'barcelona-4-1-valencia-season-opener-2026'],
            [
                'title' => 'Barcelona Cruise to Four-Goal Win Over Valencia in Season Opener',
                'dek' => 'A dominant attacking display at home gives Barcelona an early statement result to open their title challenge.',
                'body' => <<<'BODY'
Barcelona opened their season in style, brushing aside Valencia 4-1 in a one-sided afternoon that showed exactly why they will be considered genuine title contenders again this year.

The home side were ahead inside the first twenty minutes and never looked back, moving the ball with the kind of speed and confidence that made this Barcelona side so difficult to contain for long spells last season. A second goal before half-time put the result beyond real doubt, and Valencia, to their credit, never stopped trying, pulling one back midway through the second half to give the scoreline a touch of respectability.

It did not matter. Barcelona added two more in the closing stages, the kind of goals that come from a team playing with total belief, and the final whistle drew warm applause from a crowd that left in no doubt about the level their side is capable of reaching this season.

For Valencia, there were signs of encouragement even in defeat. They created chances of their own, particularly in the second half once they stopped chasing the game and started playing with more freedom. Four goals conceded will concern their coaching staff, but the performance had enough about it to suggest this will not be a repeat of their toughest recent seasons.

Barcelona's front line looked sharp throughout, combining well in tight spaces and stretching Valencia's back line at will. If this is a sign of things to come, La Liga's other title hopefuls will already be taking note.

Both clubs return to action in a fortnight, with Barcelona facing a trip across the country and Valencia looking to bounce back on home soil at the Mestalla. On this evidence, Barcelona will start as favourites wherever they play this season.
BODY,
                'image_path' => 'assets/img/news/barcelona-4-1-valencia-season-opener-2026.svg',
                'category' => 'match-report',
                'league_id' => $laLiga?->id,
                'team_id' => 13, // Barcelona
                'match_id' => 382,
                'source' => 'human',
                'status' => 'pending_review',
                'author' => 'Marcus Ferreira',
                'meta_title' => 'Barcelona 4-1 Valencia: La Liga Match Report | The Soccer Goals',
                'meta_description' => 'Barcelona opened their La Liga season with a dominant 4-1 win over Valencia, signalling their intent as genuine title contenders.',
                'meta_keywords' => 'Barcelona, Valencia, La Liga, match report, season opener',
            ]
        );

        NewsArticle::updateOrCreate(
            ['slug' => 'al-hilal-saudi-pro-league-season-preview-2026'],
            [
                'title' => 'Al-Hilal Look to Defend Their Crown as the Saudi Pro League Season Kicks Off',
                'dek' => 'The most successful club in Saudi football history begins another title campaign at home to Neom this weekend.',
                'body' => <<<'BODY'
The Saudi Pro League returns this week, and once again all eyes will be on Al-Hilal, the club that has spent the last decade setting the standard for the rest of the league to chase.

Al-Hilal have been the dominant force in Saudi football for years, and their record speaks for itself: multiple league titles, a string of domestic cup wins, and continental success that few clubs in the region can match. Riyadh's biggest club open the new campaign at home against Neom, a fixture that on paper looks straightforward but one Al-Hilal will not want to take lightly given how quickly form can shift on opening weekend.

The Saudi Pro League has changed a great deal in recent years. What was once a competitive but largely regional league has become one of the most talked-about competitions outside Europe, with clubs investing heavily to attract experienced players and coaches from the continent's biggest leagues. That investment has raised the standard across the board, and Al-Hilal no longer have the league to themselves the way they once did. Al-Nassr, Al-Ittihad and a handful of ambitious clubs further down the table have all closed the gap.

Still, Al-Hilal enter the new season as favourites, and with good reason. Few clubs anywhere in the world have matched their consistency over the past several years, and their squad depth remains the envy of the league. The challenge, as always, will be balancing domestic form with continental ambitions, a juggling act that has occasionally caught even the strongest Saudi sides off guard in recent seasons.

Kingdom Arena will be full and loud for kick-off, and the pressure on the home side will be obvious from the first whistle. Neom, for their part, arrive with little to lose and every incentive to make a statement of their own against the league's biggest name.

Whatever happens this weekend, it is only the opening chapter of a long season. But in a league where opening-day form has a habit of setting the tone, Al-Hilal will want to start the way they mean to go on.
BODY,
                'image_path' => 'assets/img/news/al-hilal-saudi-pro-league-season-preview-2026.svg',
                'category' => 'analysis',
                'league_id' => $saudiProLeague?->id,
                'team_id' => $alHilal?->id,
                'match_id' => 1753,
                'source' => 'human',
                'status' => 'pending_review',
                'author' => 'Marcus Ferreira',
                'meta_title' => 'Al-Hilal Season Preview: Saudi Pro League 2026-27 | The Soccer Goals',
                'meta_description' => "Al-Hilal begin their Saudi Pro League title defence at home to Neom, as the league's dominant club looks to fend off growing competition.",
                'meta_keywords' => 'Al-Hilal, Saudi Pro League, season preview, Neom, Kingdom Arena',
            ]
        );
    }
}
