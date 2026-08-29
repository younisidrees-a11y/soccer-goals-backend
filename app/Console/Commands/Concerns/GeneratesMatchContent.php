<?php

namespace App\Console\Commands\Concerns;

use App\Models\MatchFixture;
use App\Services\AiFactChecker;
use App\Services\AiMatchReportWriter;

/**
 * Shared scoreline/stats/report generation used by both results:update
 * (converts a due fixture into a result) and league:publish-results
 * (publishes already-final matches and fills in a report if missing).
 */
trait GeneratesMatchContent
{
    private const DRAW_BLANK = [
        "{home} and {away} played out a goalless draw at {venue}, with both defences on top for the full ninety minutes. Chances were rare, and neither goalkeeper was seriously troubled for long spells of the game.\n\nIt was the kind of result that will please the two managers watching their defensive shape more than the neutrals in the stands. A point apiece, with little to separate the sides.",
        "Neither {home} nor {away} could find a way through at {venue}, and the game finished goalless. Both sides had half-chances but lacked the final touch to make the breakthrough.\n\nIt was a cagey, tightly-contested afternoon, and a draw was a fair reflection of a game with few clear openings.",
        "{home} and {away} shared a stalemate at {venue} in a game short on quality chances. Both teams pressed for a winner in the closing stages but could not force the issue.\n\nA point each keeps both sides ticking over, even if the football on show will not live long in the memory.",
    ];

    private const DRAW_SCORING = [
        "{home} and {away} shared the points in a {homeScore}-{awayScore} draw at {venue}, with the lead changing hands before the game settled into a fair result. Both sides showed enough going forward to suggest this will not be their last high-scoring afternoon this season.\n\nNeither manager will be entirely satisfied, but there was plenty for both sets of fans to enjoy along the way.",
        "It finished {homeScore}-{awayScore} between {home} and {away} at {venue}, honours even after a game played at a good tempo throughout. Both teams had spells on top, and neither could find the extra goal to settle it.\n\nA draw was probably the right outcome on the balance of the chances created.",
        "{home} and {away} could not be separated at {venue}, drawing {homeScore}-{awayScore} in an entertaining contest. Both sides will have taken something from the performance, even if neither will be fully content with a point.\n\nIt was the kind of game that could easily have gone either way in the final stages.",
    ];

    private const HOME_WIN_NARROW = [
        "{home} edged out {away} {homeScore}-{awayScore} at {venue} in a tight contest that was not settled until late on. It was a narrow margin, and {away} will feel they had enough of the game to take something from it.\n\nFor {home}, though, three points are three points, however hard-fought they were.",
        "{home} did just enough to beat {away} {homeScore}-{awayScore} at {venue}, in a game that could easily have finished differently. {away} pushed for an equaliser in the closing stages but could not find a way through.\n\nA narrow, nervy win for the home side, who will not mind that it did not come easily.",
        "It was a tense afternoon at {venue}, where {home} saw off {away} by a score of {homeScore}-{awayScore}. The home side had to work hard for the win, with {away} competitive from start to finish.\n\n{home} will take the three points without complaint, even if the performance leaves room for improvement.",
    ];

    private const HOME_WIN_BIG = [
        "{home} were much the better side at {venue}, beating {away} {homeScore}-{awayScore} in a one-sided contest. The home side were ahead early and controlled the game from that point on, rarely looking troubled.\n\n{away} will have to regroup quickly after a difficult afternoon, while {home} can be pleased with a comfortable, convincing win.",
        "{home} put on a strong display at {venue} to beat {away} {homeScore}-{awayScore}. It was clear early on that the home side had the edge, and they never let {away} back into the game.\n\nA statement result for {home}, and a day to forget for the visitors.",
        "There was only one team in it at {venue}, as {home} cruised past {away} {homeScore}-{awayScore}. The home side moved the ball with confidence throughout and were good value for a comfortable win.\n\n{away} rarely threatened and will need to respond quickly to a chastening afternoon.",
    ];

    private const AWAY_WIN_NARROW = [
        "{away} picked up an away win on the road, edging out {home} {awayScore}-{homeScore} at {venue}. It was a tight game throughout, and {home} will feel they should have taken something from it.\n\nFor {away}, though, three points away from home is a good return, however narrow the margin.",
        "{away} did enough to see off {home} {awayScore}-{homeScore} at {venue}, in a contest that stayed close until the final whistle. {home} pushed hard for an equaliser but could not find the breakthrough.\n\nA hard-earned, narrow win for the visitors on their travels.",
        "It was a close-fought game at {venue}, where {away} came away with a {awayScore}-{homeScore} win over {home}. Neither side gave much away, and it took a fine margin to separate the two teams.\n\n{away} will be pleased to leave with all three points from a difficult away fixture.",
    ];

    private const AWAY_WIN_BIG = [
        "{away} were excellent on their travels, beating {home} {awayScore}-{homeScore} at {venue} in a one-sided away performance. The visitors took control early and never let {home} back into the contest.\n\nA big result for {away} on the road, and a tough afternoon for the home side to take.",
        "{away} made light work of {home} at {venue}, winning {awayScore}-{homeScore} in a dominant away display. {home} struggled to get a foothold in the game from start to finish.\n\nIt was exactly the kind of away performance {away} will want to build on as the season goes on.",
        "There was a big gap between the two sides at {venue}, as {away} ran out {awayScore}-{homeScore} winners over {home}. The visitors were sharper, more direct, and always looked the more likely to score.\n\n{home} will need to reflect on a disappointing home performance.",
    ];

    /** @return array{0: int, 1: int} */
    private function generateScoreline(): array
    {
        $scorelines = [
            [1, 0, 120], [0, 1, 70], [1, 1, 100], [2, 0, 90], [0, 2, 55], [2, 1, 100], [1, 2, 60],
            [2, 2, 60], [0, 0, 70], [3, 0, 40], [0, 3, 20], [3, 1, 60], [1, 3, 30], [3, 2, 40], [2, 3, 25],
            [4, 0, 15], [0, 4, 8], [4, 1, 25], [1, 4, 12], [3, 3, 20], [4, 2, 15], [2, 4, 10], [4, 3, 8],
            [5, 0, 5], [5, 1, 6], [0, 5, 3],
        ];

        $total = array_sum(array_column($scorelines, 2));
        $roll = random_int(1, $total);

        foreach ($scorelines as [$home, $away, $weight]) {
            if ($roll <= $weight) {
                return [$home, $away];
            }
            $roll -= $weight;
        }

        return [1, 1];
    }

    private function generateStats(int $homeScore, int $awayScore): array
    {
        $homeShots = max($homeScore * 2 + random_int(3, 8), $homeScore + 2);
        $awayShots = max($awayScore * 2 + random_int(3, 8), $awayScore + 2);

        $possHome = match (true) {
            $homeScore > $awayScore => random_int(50, 63),
            $homeScore < $awayScore => random_int(37, 50),
            default => random_int(45, 55),
        };

        return [
            'possession' => ['home' => $possHome, 'away' => 100 - $possHome],
            'shots' => ['home' => $homeShots, 'away' => $awayShots],
            'shots_on_target' => [
                'home' => min($homeShots, $homeScore + random_int(1, 4)),
                'away' => min($awayShots, $awayScore + random_int(1, 4)),
            ],
            'corners' => ['home' => random_int(2, 9), 'away' => random_int(2, 9)],
            'fouls' => ['home' => random_int(6, 15), 'away' => random_int(6, 15)],
            'yellow_cards' => ['home' => random_int(0, 4), 'away' => random_int(0, 4)],
            'red_cards' => [
                'home' => random_int(1, 100) <= 3 ? 1 : 0,
                'away' => random_int(1, 100) <= 3 ? 1 : 0,
            ],
        ];
    }

    private function generateReport(MatchFixture $fixture, int $homeScore, int $awayScore, ?array $stats = null): string
    {
        $aiReport = app(AiMatchReportWriter::class)->write(
            $fixture,
            $homeScore,
            $awayScore,
            $stats ?? $this->generateStats($homeScore, $awayScore)
        );

        // Deterministic checks, not more prompt instructions the model
        // could still ignore. A report that doesn't state the real score,
        // or still uses a banned AI-tone word despite the prompt banning
        // it, is discarded outright (falls through to the
        // always-consistent template bank below) rather than risking a
        // wrong scoreline or leftover robotic phrasing going out - there's
        // no safe way to "correct" either of those without rewriting the
        // whole report. A wrong day of the week, once found, IS safely
        // correctable (it's always the same drop-in swap), so that gets
        // fixed rather than discarded.
        if ($aiReport
            && AiFactChecker::containsScore($aiReport, $homeScore, $awayScore)
            && ! AiFactChecker::findBannedTone($aiReport)
        ) {
            return AiFactChecker::fixDayOfWeek($aiReport, $fixture->kickoff_at);
        }

        $bank = match (true) {
            $homeScore === $awayScore && $homeScore === 0 => self::DRAW_BLANK,
            $homeScore === $awayScore => self::DRAW_SCORING,
            $homeScore > $awayScore && ($homeScore - $awayScore) === 1 => self::HOME_WIN_NARROW,
            $homeScore > $awayScore => self::HOME_WIN_BIG,
            ($awayScore - $homeScore) === 1 => self::AWAY_WIN_NARROW,
            default => self::AWAY_WIN_BIG,
        };

        $template = $bank[random_int(0, count($bank) - 1)];

        return str_replace(
            ['{home}', '{away}', '{venue}', '{homeScore}', '{awayScore}'],
            [$fixture->homeTeam->name, $fixture->awayTeam->name, $fixture->venue, $homeScore, $awayScore],
            $template
        );
    }
}
