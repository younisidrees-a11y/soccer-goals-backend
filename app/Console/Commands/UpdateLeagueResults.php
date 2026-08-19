<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\GeneratesMatchContent;
use App\Models\League;
use App\Models\MatchFixture;
use App\Services\StandingsCalculator;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Finds every fixture in a league whose kick-off time has passed but is
 * still marked "scheduled", and turns each into a result: a generated
 * scoreline, realistic match stats, and a human-written report. Run it
 * again any time - only fixtures still marked "scheduled" are touched, so
 * it's always safe to re-run.
 *
 * Nothing here changes is_published - a fixture that was already live
 * stays live as a result; one still pending review stays pending review.
 */
#[Signature('results:update {league : League slug, e.g. premier-league}')]
#[Description('Convert any past-due scheduled fixtures for a league into results with a generated score, stats, and a written report')]
class UpdateLeagueResults extends Command
{
    use GeneratesMatchContent;

    public function handle(): int
    {
        $league = League::where('slug', $this->argument('league'))->first();

        if (! $league) {
            $this->error("No league found with slug \"{$this->argument('league')}\".");

            return self::FAILURE;
        }

        $dueFixtures = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->where('league_id', $league->id)
            ->where('status', 'scheduled')
            ->where('kickoff_at', '<=', now())
            ->orderBy('kickoff_at')
            ->get();

        if ($dueFixtures->isEmpty()) {
            $this->info("Nothing to update for {$league->name} - no scheduled fixtures are due yet.");

            return self::SUCCESS;
        }

        foreach ($dueFixtures as $fixture) {
            [$homeScore, $awayScore] = $this->generateScoreline();
            $stats = $this->generateStats($homeScore, $awayScore);

            $fixture->update([
                'status' => 'final',
                'home_score' => $homeScore,
                'away_score' => $awayScore,
                'match_report' => $this->generateReport($fixture, $homeScore, $awayScore, $stats),
                'stats' => $stats,
            ]);

            $this->line("{$fixture->homeTeam->name} {$homeScore}-{$awayScore} {$fixture->awayTeam->name}");
        }

        StandingsCalculator::recalculate($league);

        $this->info("Updated {$dueFixtures->count()} fixture(s) to results for {$league->name}, and refreshed the points table.");

        return self::SUCCESS;
    }
}
