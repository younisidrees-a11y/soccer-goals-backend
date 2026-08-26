<?php

namespace App\Console\Commands;

use App\Models\League;
use App\Models\MatchFixture;
use App\Models\Team;
use App\Services\AiMatchReportWriter;
use App\Services\FootballDataClient;
use App\Services\StandingsCalculator;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * The one command that keeps a league fully in sync with reality: pulls
 * the real fixture list from football-data.org (real dates, pairings,
 * matchday numbers - this site no longer generates its own fictional
 * schedule), writes an AI report for any newly-finished match that
 * doesn't have one yet (grounded only in the real score/stats actually
 * available), then refreshes standings. Safe to run on a schedule - only
 * matches genuinely due for something get touched.
 *
 * Run football-data:map-teams first, or matches for unmapped teams are
 * skipped (printed clearly, never silently dropped).
 */
#[Signature('football-data:sync {league : League slug, e.g. premier-league}')]
#[Description('Sync a league\'s real fixtures/results from football-data.org, write reports for newly-finished matches, refresh standings')]
class SyncFootballDataFixtures extends Command
{
    private const STATUS_MAP = [
        'SCHEDULED' => 'scheduled',
        'TIMED' => 'scheduled',
        'IN_PLAY' => 'live',
        'PAUSED' => 'live',
        'FINISHED' => 'final',
        'POSTPONED' => 'postponed',
        'SUSPENDED' => 'postponed',
        'CANCELLED' => 'postponed',
    ];

    public function handle(): int
    {
        $league = League::where('slug', $this->argument('league'))->first();

        if (! $league) {
            $this->error("No league found with slug \"{$this->argument('league')}\".");

            return self::FAILURE;
        }

        if (! $league->external_code) {
            $this->error("{$league->name} has no external_code set - run football-data:map-teams first (it also needs this) or set it manually.");

            return self::FAILURE;
        }

        $teamsByExternalId = Team::where('league_id', $league->id)
            ->whereNotNull('external_id')
            ->get()
            ->keyBy('external_id');

        if ($teamsByExternalId->isEmpty()) {
            $this->error("No teams mapped for {$league->name} - run football-data:map-teams {$this->argument('league')} <code> --apply first.");

            return self::FAILURE;
        }

        $response = app(FootballDataClient::class)->getMatches($league->external_code);

        if (! $response || empty($response['matches'])) {
            $this->error('Could not fetch matches from football-data.org.');

            return self::FAILURE;
        }

        $seenExternalIds = [];
        $skipped = 0;
        $synced = 0;

        foreach ($response['matches'] as $match) {
            $home = $teamsByExternalId->get($match['homeTeam']['id']);
            $away = $teamsByExternalId->get($match['awayTeam']['id']);

            if (! $home || ! $away) {
                $skipped++;
                $this->warn("Skipped match {$match['id']}: unmapped team (home={$match['homeTeam']['name']}, away={$match['awayTeam']['name']}).");

                continue;
            }

            $status = self::STATUS_MAP[$match['status']] ?? 'scheduled';

            $data = [
                'league_id' => $league->id,
                'home_team_id' => $home->id,
                'away_team_id' => $away->id,
                'matchday' => $match['matchday'],
                'kickoff_at' => Carbon::parse($match['utcDate']),
                'venue' => $home->stadium,
                'status' => $status,
                'is_published' => true,
            ];

            if ($match['score']['fullTime']['home'] !== null) {
                $data['home_score'] = $match['score']['fullTime']['home'];
                $data['away_score'] = $match['score']['fullTime']['away'];
            }

            if ($match['score']['halfTime']['home'] !== null) {
                $data['home_score_ht'] = $match['score']['halfTime']['home'];
                $data['away_score_ht'] = $match['score']['halfTime']['away'];
            }

            MatchFixture::updateOrCreate(['external_id' => $match['id']], $data);
            $seenExternalIds[] = $match['id'];
            $synced++;
        }

        // Anything left over from the old fictional schedule (no external_id
        // at all) no longer represents a real match - remove it.
        $removed = MatchFixture::where('league_id', $league->id)->whereNull('external_id')->delete();

        $this->info("Synced {$synced} real fixtures for {$league->name}.");
        if ($skipped > 0) {
            $this->warn("{$skipped} match(es) skipped - unmapped teams (see warnings above).");
        }
        if ($removed > 0) {
            $this->info("Removed {$removed} leftover fictional fixture(s).");
        }

        $this->writeReportsForNewlyFinishedMatches($league);

        StandingsCalculator::recalculate($league);
        $this->info('Standings recalculated.');

        return self::SUCCESS;
    }

    private function writeReportsForNewlyFinishedMatches(League $league): void
    {
        $matches = MatchFixture::with(['homeTeam', 'awayTeam', 'league'])
            ->where('league_id', $league->id)
            ->where('status', 'final')
            ->whereNull('match_report')
            ->get();

        if ($matches->isEmpty()) {
            return;
        }

        $writer = app(AiMatchReportWriter::class);

        foreach ($matches as $match) {
            // No possession/shots data available from football-data.org's
            // free tier - AiMatchReportWriter omits those lines entirely
            // when the stats array is empty, rather than inventing them.
            $report = $writer->write($match, $match->home_score, $match->away_score, []);

            if ($report) {
                $match->update(['match_report' => $report]);
                $this->info("Wrote report: {$match->homeTeam->name} {$match->home_score}-{$match->away_score} {$match->awayTeam->name}");
            } else {
                $this->warn("Report generation failed for match {$match->id} - check storage/logs/laravel.log.");
            }
        }
    }
}
