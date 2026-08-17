<?php

namespace Database\Seeders;

use App\Models\MatchFixture;
use Illuminate\Database\Seeder;

/**
 * Replaces the single-sentence, templated La Liga Matchday 1 reports with
 * proper, human-written reports that reference the real match stats
 * already stored on each fixture.
 */
class LaLigaMD1ReportsSeeder extends Seeder
{
    private const REPORTS = [
        381 => "Real Madrid made a winning start to the season, beating Real Sociedad 3-1 in front of a packed Santiago Bernabeu. The home side settled quickest, moved the ball with real purpose, and had enough control of the game to make the three points feel comfortable by the end.\n\nThe numbers backed up what the eye saw: Real Madrid had more of the ball (55% possession) and were the sharper side in front of goal, landing four shots on target to Real Sociedad's two. Real Sociedad did not fold, though, and pulled a goal back to give themselves something to build on for the weeks ahead.\n\nIt is early days, but a home win on opening weekend is exactly the platform Real Madrid wanted, and it leaves them right in the mix at the top of the table after round one.",
        382 => "Barcelona opened their campaign in style, brushing aside Valencia 4-1 in a game they controlled from start to finish. With 60% of the ball and twelve shots to Valencia's five, the home side were rarely troubled and always looked capable of adding to their tally.\n\nValencia never stopped competing and got a goal back to take a little shine off the scoreline, but four goals and seven shots on target tell their own story about how one-sided this game really was.\n\nA big win to start the season, and a real early marker from Barcelona to the rest of La Liga.",
        383 => "Atletico Madrid had to settle for a point after being held 1-1 by Alaves at home. It was a close contest throughout, with both teams getting good sights of goal and neither goalkeeper having a quiet afternoon.\n\nAlaves actually edged the shots-on-target column, a sign of how well they defended and countered against a strong Atletico Madrid side on their own patch. Atletico had a shade more of the ball, but it was not enough to find a winner.\n\nA fair result in the end, and a promising sign for Alaves that they can compete with one of the league's bigger sides away from home.",
        384 => "Athletic Bilbao made home advantage count, beating Villarreal 2-0 at a typically loud San Mames. The home side had the better of possession all afternoon (62%) and turned that control into a deserved two-goal cushion.\n\nVillarreal had their moments and were not without threat on the counter, but Athletic's extra sharpness in front of goal, five shots on target to three, made the difference in the end.\n\nA clean sheet and a comfortable win to open the season for Athletic Bilbao, who will be pleased with how well they controlled the game throughout.",
        385 => "Sevilla and Real Betis shared the points in a 1-1 draw at the Ramon Sanchez Pizjuan, in a game that had all the intensity you would expect from this fixture. Real Betis actually had more shots on the day, but neither side could find a way past a well-set defence for the winner.\n\nBoth teams managed two shots on target apiece, a sign of just how evenly matched this game turned out to be.\n\nA draw feels like the right result, and both sides will take some confidence from their performance into the weeks ahead.",
        386 => "Girona got their season up and running with a 2-1 win over Celta Vigo at the Estadi Montilivi. The home side had much the better of the possession (58%) and were the busier team in and around the box for most of the afternoon.\n\nCelta Vigo stayed in the contest with a goal of their own and were not far off drawing level, but Girona's extra control of the game was enough to see them over the line.\n\nA solid opening-day win for Girona, who will hope this is a sign of good things to come this season.",
        387 => "Osasuna made their notoriously tough home ground count once again, edging out Rayo Vallecano 1-0 at El Sadar. The home side had the better of the chances throughout, doubling up Rayo Vallecano's shot count over the course of the game.\n\nRayo Vallecano defended with real discipline and were unlucky not to take something from the game, but Osasuna's extra threat in front of goal proved decisive.\n\nA tight, low-scoring win, but exactly the kind of result Osasuna have built their reputation on at El Sadar.",
        388 => "Mallorca and Getafe played out a goalless draw at the Visit Mallorca Estadi, in a game that was tight and cagey from start to finish. Getafe had a little more of the ball, but it was Mallorca who created the better openings without finding a way through.\n\nNeither side wanted to take too many risks, and it showed in a game with few clear-cut chances for either goalkeeper to deal with.\n\nA point apiece to open the season, in a game that will not be remembered for its entertainment value but tells you plenty about how tight La Liga can be on any given weekend.",
        389 => "Espanyol made the long trip to the Canary Islands a successful one, beating Las Palmas 2-1 at the Estadio Gran Canaria. It was a tight game throughout, with Espanyol's extra share of possession (56%) helping them nose ahead when it mattered.\n\nLas Palmas pushed hard for an equaliser and were unlucky not to get it, having matched Espanyol for large parts of the game, but could not find a second goal of their own.\n\nA big early result for Espanyol on the road, while Las Palmas will feel there was more in the game for them on home soil.",
        390 => "Leganes made a winning start to life back in La Liga, beating Valladolid 2-1 at the Estadio Municipal de Butarque. The home side had the better of the ball (60% possession) and were the more threatening side for most of the afternoon.\n\nValladolid pulled a goal back and pushed for more from a string of late corners, but Leganes held on to see out a deserved opening-day win.\n\nA big three points for Leganes on their return to the top flight, and a result that will give their supporters plenty of belief for the season ahead.",
    ];

    public function run(): void
    {
        foreach (self::REPORTS as $matchId => $report) {
            MatchFixture::where('id', $matchId)->update(['match_report' => $report]);
        }
    }
}
