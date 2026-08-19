<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\GeneratesMatchContent;
use App\Models\League;
use App\Models\MatchFixture;
use App\Models\Team;
use App\Services\StandingsCalculator;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * The one command that covers everything asked for repeatedly: publish a
 * league and its teams, publish every match already played (writing a
 * proper human report and stats for any that only have the old one-line
 * placeholder or none at all), publish the next unplayed matchday so
 * there's always something to show under Fixtures and to link to from
 * "Looking Ahead", and refresh the points table.
 *
 * Safe to run repeatedly - already-published, already-well-written
 * matches are left untouched.
 */
#[Signature('league:publish-results {league : League slug, e.g. premier-league}')]
#[Description('Publish a league, its teams, every played match (writing a report/stats if missing), the next matchday of fixtures, and refresh the points table')]
class PublishLeagueResults extends Command
{
    use GeneratesMatchContent;

    /** Below this length, a match_report is treated as the old one-line placeholder text, not a real report. */
    private const MIN_QUALITY_REPORT_LENGTH = 200;

    public function handle(): int
    {
        $league = League::where('slug', $this->argument('league'))->first();

        if (! $league) {
            $this->error("No league found with slug \"{$this->argument('league')}\".");

            return self::FAILURE;
        }

        $league->update(['is_published' => true]);
        $teamsPublished = Team::where('league_id', $league->id)->update(['is_published' => true]);

        $playedMatches = MatchFixture::with(['homeTeam', 'awayTeam'])
            ->where('league_id', $league->id)
            ->where('status', 'final')
            ->get();

        $rewritten = 0;

        foreach ($playedMatches as $match) {
            $needsReport = blank($match->match_report) || strlen($match->match_report) < self::MIN_QUALITY_REPORT_LENGTH;
            $needsStats = blank($match->stats);

            if ($needsReport || $needsStats || ! $match->is_published) {
                $updates = ['is_published' => true];

                $stats = $needsStats ? $this->generateStats($match->home_score, $match->away_score) : $match->stats;

                if ($needsStats) {
                    $updates['stats'] = $stats;
                }

                if ($needsReport) {
                    $updates['match_report'] = $this->generateReport($match, $match->home_score, $match->away_score, $stats);
                }

                $match->update($updates);

                if ($needsReport || $needsStats) {
                    $rewritten++;
                }
            }
        }

        $nextMatchday = MatchFixture::where('league_id', $league->id)
            ->where('status', 'scheduled')
            ->min('matchday');

        $fixturesPublished = 0;

        if ($nextMatchday !== null) {
            $fixturesPublished = MatchFixture::where('league_id', $league->id)
                ->where('status', 'scheduled')
                ->where('matchday', $nextMatchday)
                ->update(['is_published' => true]);
        }

        StandingsCalculator::recalculate($league);

        $this->info("{$league->name}: published league + {$teamsPublished} teams.");
        $this->info("{$playedMatches->count()} played match(es) published ({$rewritten} given a fresh report/stats).");
        $this->info($nextMatchday !== null
            ? "Matchday {$nextMatchday} published as the next set of fixtures ({$fixturesPublished} matches)."
            : 'No further scheduled fixtures to publish yet.');
        $this->info('Points table refreshed.');

        return self::SUCCESS;
    }
}
