<?php

namespace App\Console\Commands;

use App\Models\League;
use App\Models\Standing;
use App\Models\Team;
use App\Services\FootballDataClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Pulls the real, already-calculated points table from football-data.org
 * rather than deriving it from synced match results ourselves - the API
 * is the authoritative source, so there's no reason to recompute what it
 * already gives us correctly (and no risk of our own arithmetic drifting
 * from the real table).
 */
#[Signature('football-data:sync-standings {league : League slug, e.g. premier-league}')]
#[Description('Pull the real points table from football-data.org')]
class SyncFootballDataStandings extends Command
{
    public function handle(): int
    {
        $league = League::where('slug', $this->argument('league'))->first();

        if (! $league || ! $league->external_code) {
            $this->error('League not found or missing external_code.');

            return self::FAILURE;
        }

        $teamsByExternalId = Team::where('league_id', $league->id)
            ->whereNotNull('external_id')
            ->get()
            ->keyBy('external_id');

        $response = app(FootballDataClient::class)->getStandings($league->external_code);

        if (! $response || empty($response['standings'])) {
            $this->error('Could not fetch standings from football-data.org.');

            return self::FAILURE;
        }

        $table = collect($response['standings'])->firstWhere('type', 'TOTAL')['table'] ?? [];

        if (empty($table)) {
            $this->error('No TOTAL standings table in the response.');

            return self::FAILURE;
        }

        $seenTeamIds = [];
        $skipped = 0;

        foreach ($table as $row) {
            $team = $teamsByExternalId->get($row['team']['id']);

            if (! $team) {
                $skipped++;
                $this->warn("Skipped standings row for unmapped team: {$row['team']['name']}.");

                continue;
            }

            Standing::updateOrCreate(
                ['league_id' => $league->id, 'team_id' => $team->id],
                [
                    'position' => $row['position'],
                    'played' => $row['playedGames'],
                    'won' => $row['won'],
                    'drawn' => $row['draw'],
                    'lost' => $row['lost'],
                    'goals_for' => $row['goalsFor'],
                    'goals_against' => $row['goalsAgainst'],
                    'goal_difference' => $row['goalDifference'],
                    'points' => $row['points'],
                ]
            );
            $seenTeamIds[] = $team->id;
        }

        $removed = Standing::where('league_id', $league->id)->whereNotIn('team_id', $seenTeamIds)->delete();

        $this->info('Synced standings for '.count($seenTeamIds)." teams in {$league->name}.");
        if ($skipped > 0) {
            $this->warn("{$skipped} row(s) skipped - unmapped teams.");
        }
        if ($removed > 0) {
            $this->info("Removed {$removed} stale standings row(s).");
        }

        return self::SUCCESS;
    }
}
