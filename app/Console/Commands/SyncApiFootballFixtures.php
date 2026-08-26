<?php

namespace App\Console\Commands;

use App\Models\League;
use App\Models\MatchFixture;
use App\Models\Team;
use App\Services\AiMatchReportWriter;
use App\Services\ApiFootballClient;
use App\Services\StandingsCalculator;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * The Saudi Pro League equivalent of football-data:sync - football-data.org
 * doesn't cover this competition at all, so API-Football is the primary
 * fixture source here, not just enrichment. Keyed on
 * api_football_fixture_id instead of external_id since there's no
 * football-data.org id for these matches.
 *
 * Run api-football:map-teams first, or matches for unmapped teams are
 * skipped.
 */
#[Signature('api-football:sync-fixtures {league : League slug, e.g. saudi-pro-league}')]
#[Description('Sync a league\'s real fixtures/results directly from API-Football (for leagues football-data.org doesn\'t cover), write reports for newly-finished matches, refresh standings')]
class SyncApiFootballFixtures extends Command
{
    private const STATUS_MAP = [
        'NS' => 'scheduled', 'TBD' => 'scheduled',
        '1H' => 'live', 'HT' => 'live', '2H' => 'live', 'ET' => 'live', 'BT' => 'live', 'P' => 'live', 'INT' => 'live', 'LIVE' => 'live',
        'FT' => 'final', 'AET' => 'final', 'PEN' => 'final',
        'SUSP' => 'postponed', 'PST' => 'postponed', 'CANC' => 'postponed', 'ABD' => 'postponed', 'AWD' => 'postponed', 'WO' => 'postponed',
    ];

    public function handle(): int
    {
        $league = League::where('slug', $this->argument('league'))->first();

        if (! $league) {
            $this->error("No league found with slug \"{$this->argument('league')}\".");

            return self::FAILURE;
        }

        if (! $league->api_football_id) {
            $this->error("{$league->name} has no api_football_id set - run api-football:map-teams first.");

            return self::FAILURE;
        }

        $teamsByApiId = Team::where('league_id', $league->id)
            ->whereNotNull('api_football_id')
            ->get()
            ->keyBy('api_football_id');

        if ($teamsByApiId->isEmpty()) {
            $this->error("No teams mapped for {$league->name} - run api-football:map-teams first.");

            return self::FAILURE;
        }

        $response = app(ApiFootballClient::class)->getSeasonFixtures($league->api_football_id, (int) $league->season);

        if (! $response || empty($response['response'])) {
            $this->error('Could not fetch fixtures from API-Football.');

            return self::FAILURE;
        }

        $skipped = 0;
        $synced = 0;

        foreach ($response['response'] as $fixture) {
            $home = $teamsByApiId->get($fixture['teams']['home']['id']);
            $away = $teamsByApiId->get($fixture['teams']['away']['id']);

            if (! $home || ! $away) {
                $skipped++;

                continue;
            }

            $status = self::STATUS_MAP[$fixture['fixture']['status']['short']] ?? 'scheduled';
            preg_match('/(\d+)$/', $fixture['league']['round'], $roundMatch);

            $data = [
                'league_id' => $league->id,
                'home_team_id' => $home->id,
                'away_team_id' => $away->id,
                'matchday' => (int) ($roundMatch[1] ?? 1),
                'kickoff_at' => Carbon::parse($fixture['fixture']['date']),
                'venue' => $fixture['fixture']['venue']['name'] ?? $home->stadium,
                'referee' => $fixture['fixture']['referee'],
                'status' => $status,
                'is_published' => true,
            ];

            if ($status === 'final' && $fixture['goals']['home'] !== null) {
                $data['home_score'] = $fixture['goals']['home'];
                $data['away_score'] = $fixture['goals']['away'];
            }

            if ($fixture['score']['halftime']['home'] !== null) {
                $data['home_score_ht'] = $fixture['score']['halftime']['home'];
                $data['away_score_ht'] = $fixture['score']['halftime']['away'];
            }

            MatchFixture::updateOrCreate(['api_football_fixture_id' => $fixture['fixture']['id']], $data);
            $synced++;
        }

        $removed = MatchFixture::where('league_id', $league->id)
            ->whereNull('api_football_fixture_id')
            ->whereNull('external_id')
            ->delete();

        $this->info("Synced {$synced} real fixtures for {$league->name}.");
        if ($skipped > 0) {
            $this->warn("{$skipped} match(es) skipped - unmapped teams.");
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
