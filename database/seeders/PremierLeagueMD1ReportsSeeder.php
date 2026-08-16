<?php

namespace Database\Seeders;

use App\Models\MatchFixture;
use Illuminate\Database\Seeder;

/**
 * Replaces the single-sentence, occasionally factually wrong (home/away
 * mixed up) Matchday 1 Premier League reports with proper, human-written
 * 1-2 paragraph reports.
 */
class PremierLeagueMD1ReportsSeeder extends Seeder
{
    private const REPORTS = [
        1 => "Manchester City opened the defence of their title with a hard-fought 2-1 win over Chelsea at the Etihad Stadium. City led for most of the afternoon but were made to work for it as Chelsea pulled level midway through the second half and pushed for an equaliser in the closing stages.\n\nCity held their nerve when it mattered, seeing out a tense finish to start the season with three points. Chelsea will be encouraged by their response after going behind, even if the result did not go their way on the day.",
        2 => "Liverpool made a statement on the opening weekend, brushing aside Bournemouth 3-0 in front of a roaring Anfield crowd. The champions were in control from early on, moving the ball quickly and rarely letting Bournemouth settle into the game.\n\nBy the time the third goal went in, the result was long since decided. It was the kind of opening-day performance that will have given Liverpool's rivals plenty to think about.",
        3 => "Arsenal were held to a 1-1 draw by Nottingham Forest at the Emirates Stadium, a result that will feel like two points dropped for the home side after they dominated large spells of the game. Forest defended stubbornly and always looked capable of nicking something on the counter-attack, which is exactly what happened.\n\nArsenal pushed hard for a winner in the final stages but could not find a way through a well-organised Forest backline. A fair result in the end, even if the home crowd will have wanted more.",
        4 => "Aston Villa and Newcastle shared the points in a tightly-fought 1-1 draw at Villa Park. Both sides had spells of control, but neither could find the extra edge needed to turn the game in their favour.\n\nIt was a cagey opening-day contest, short on clear chances but full of effort from both teams. A draw was probably the right result on the balance of play.",
        5 => "Brighton picked up an impressive away win on the opening weekend, beating Tottenham 1-0 at the Tottenham Hotspur Stadium. It was a disciplined performance from the visitors, who defended well as a unit and made their one big chance of the game count.\n\nTottenham had their moments and will feel they should have found a way back into the game, but Brighton held firm to take all three points on the road.",
        6 => "Everton made the trip to Turf Moor a productive one, beating Burnley 2-0 to start their season with a win. The visitors were the sharper side for large parts of the game and always looked the more likely to score.\n\nBurnley battled hard but could not find a way back into the contest once they fell behind. A solid, professional away performance from Everton to open the campaign.",
        7 => "Manchester United began their season with a narrow 1-0 win over Fulham at Old Trafford. It was not a vintage performance, but it was enough, with United doing just enough to see out a tight game against a well-drilled Fulham side.\n\nFulham will point to their away performance as a positive despite the defeat, having made life difficult for United from start to finish. For United, though, three points on the opening weekend is exactly what they wanted.",
        8 => "Crystal Palace and West Ham shared four goals in an entertaining 2-2 draw at Selhurst Park. The lead changed hands more than once as both sides went for the win rather than settling for a point.\n\nIt was end-to-end stuff for long stretches, and a draw felt like a fair outcome given how open the game was. Both sets of supporters will have left Selhurst Park having enjoyed the occasion, even if neither side could find a winner.",
        9 => "Wolves got their season up and running with a 1-0 win over newly-promoted Leeds United at Molineux. It was a tight, tense affair, with Wolves doing just enough to edge a game that could easily have gone either way.\n\nLeeds showed plenty of fight on their return to the top flight and will take confidence from how competitive they were, even in defeat. For Wolves, it was the perfect start: a home win to open the campaign.",
        10 => "Sunderland marked their return to Premier League football with a 2-1 win over Brentford at the Stadium of Light. The home crowd were in fine voice all afternoon, and the team gave them plenty to cheer about with an energetic, front-footed performance.\n\nBrentford pulled a goal back and pushed for an equaliser late on, but Sunderland held firm to secure a memorable opening-day win in front of their own fans.",
    ];

    public function run(): void
    {
        foreach (self::REPORTS as $matchId => $report) {
            MatchFixture::where('id', $matchId)->update(['match_report' => $report]);
        }
    }
}
