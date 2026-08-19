<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\GeneratesMatchContent;
use App\Models\MatchFixture;
use App\Services\AiMatchLiveWriter;
use App\Services\StandingsCalculator;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Meant to run on a schedule (every few minutes, via routes/console.php +
 * a real cron entry on the server) rather than by hand. Each run checks
 * every league's fixtures for four time-based transitions and only acts on
 * whichever ones just became due:
 *
 *   1. Preview   - published a couple of hours before kickoff
 *   2. Kickoff   - status flips to "live"
 *   3. Half-time - 45 minutes after kickoff, a partial score + short update
 *   4. Full-time - ~95 minutes after kickoff, the final score, full AI
 *                  report and stats (reusing the same logic as
 *                  league:publish-results), then standings refresh
 *
 * Only ever touches fixtures already marked is_published - this command
 * progresses matches through their lifecycle, it doesn't decide which
 * fixtures are public. That stays an editorial call via
 * league:publish-results.
 */
#[Signature('matches:progress')]
#[Description("Advances every league's due fixtures through preview, kickoff, half-time, and full-time")]
class ProgressLiveMatches extends Command
{
    use GeneratesMatchContent;

    private const HALFTIME_SCORELINES = [
        [0, 0, 150], [1, 0, 90], [0, 1, 60], [1, 1, 60],
        [2, 0, 25], [0, 2, 12], [2, 1, 20], [1, 2, 10], [2, 2, 6],
    ];

    public function handle(): int
    {
        $this->publishPreviews();
        $this->startKickoffs();
        $this->publishHalftimes();
        $this->publishFullTimes();

        return self::SUCCESS;
    }

    private function publishPreviews(): void
    {
        $fixtures = MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
            ->where('status', 'scheduled')
            ->where('is_published', true)
            ->whereNull('preview_published_at')
            ->where('kickoff_at', '>', now())
            ->where('kickoff_at', '<=', now()->addHours(2))
            ->get();

        foreach ($fixtures as $fixture) {
            $written = app(AiMatchLiveWriter::class)->writePreview($fixture);

            if (! $written) {
                continue;
            }

            $fixture->update([
                'home_preview_note' => $written['home'],
                'away_preview_note' => $written['away'],
                'preview_published_at' => now(),
            ]);

            $this->line("Preview published: {$fixture->homeTeam->name} vs {$fixture->awayTeam->name}");
        }
    }

    private function startKickoffs(): void
    {
        $count = MatchFixture::where('status', 'scheduled')
            ->where('is_published', true)
            ->where('kickoff_at', '<=', now())
            ->update(['status' => 'live']);

        if ($count > 0) {
            $this->line("{$count} match(es) kicked off.");
        }
    }

    private function publishHalftimes(): void
    {
        $fixtures = MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
            ->where('status', 'live')
            ->whereNull('halftime_published_at')
            ->where('kickoff_at', '<=', now()->subMinutes(45))
            ->get();

        foreach ($fixtures as $fixture) {
            [$homeHt, $awayHt] = $this->generateHalftimeScoreline();
            $update = app(AiMatchLiveWriter::class)->writeHalftime($fixture, $homeHt, $awayHt);

            $fixture->update([
                'home_score_ht' => $homeHt,
                'away_score_ht' => $awayHt,
                'halftime_report' => $update ?? "Half-time: {$fixture->homeTeam->name} {$homeHt}-{$awayHt} {$fixture->awayTeam->name}.",
                'halftime_published_at' => now(),
            ]);

            $this->line("Half-time: {$fixture->homeTeam->name} {$homeHt}-{$awayHt} {$fixture->awayTeam->name}");
        }
    }

    private function publishFullTimes(): void
    {
        $fixtures = MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
            ->where('status', 'live')
            ->where('kickoff_at', '<=', now()->subMinutes(95))
            ->get();

        $leaguesToRecalculate = [];

        foreach ($fixtures as $fixture) {
            [$finalHome, $finalAway] = $this->generateScoreline();
            $homeScore = max($finalHome, $fixture->home_score_ht ?? 0);
            $awayScore = max($finalAway, $fixture->away_score_ht ?? 0);
            $stats = $this->generateStats($homeScore, $awayScore);

            $fixture->update([
                'status' => 'final',
                'home_score' => $homeScore,
                'away_score' => $awayScore,
                'match_report' => $this->generateReport($fixture, $homeScore, $awayScore, $stats),
                'stats' => $stats,
            ]);

            $leaguesToRecalculate[$fixture->league_id] = $fixture->league;

            $this->line("Full-time: {$fixture->homeTeam->name} {$homeScore}-{$awayScore} {$fixture->awayTeam->name}");
        }

        foreach ($leaguesToRecalculate as $league) {
            StandingsCalculator::recalculate($league);
        }
    }

    /** @return array{0: int, 1: int} */
    private function generateHalftimeScoreline(): array
    {
        $total = array_sum(array_column(self::HALFTIME_SCORELINES, 2));
        $roll = random_int(1, $total);

        foreach (self::HALFTIME_SCORELINES as [$home, $away, $weight]) {
            if ($roll <= $weight) {
                return [$home, $away];
            }
            $roll -= $weight;
        }

        return [0, 0];
    }
}
