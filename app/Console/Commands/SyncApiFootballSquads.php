<?php

namespace App\Console\Commands;

use App\Models\League;
use App\Models\Player;
use App\Models\Team;
use App\Services\ApiFootballClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Pulls each team's real current squad from API-Football - name,
 * position, shirt number, nationality, real photo, goals/assists, and
 * whether they're the real current captain - for every published team
 * in a league that has an api_football_id but no players yet (or
 * --force to refresh everyone, e.g. after a transfer window).
 *
 * Position is mapped from API-Football's four broad categories
 * (Goalkeeper/Defender/Midfielder/Attacker) to this site's existing
 * squad grouping, which already expects 'Forward' rather than
 * 'Attacker' - no other mapping needed since the rest already match.
 *
 * Keyed on (team_id, api_football_id) so re-running never creates
 * duplicates, only refreshes stats/photo for players already known.
 */
#[Signature('api-football:sync-squads {league : League slug, e.g. saudi-pro-league} {--force : Refresh every team, including ones that already have players}')]
#[Description("Pull each team's real current squad (with real photos) from API-Football")]
class SyncApiFootballSquads extends Command
{
    private const POSITION_MAP = [
        'Goalkeeper' => 'Goalkeeper',
        'Defender' => 'Defender',
        'Midfielder' => 'Midfielder',
        'Attacker' => 'Forward',
    ];

    public function handle(): int
    {
        $league = League::where('slug', $this->argument('league'))->first();

        if (! $league) {
            $this->error("No league found with slug \"{$this->argument('league')}\".");

            return self::FAILURE;
        }

        $teams = Team::where('league_id', $league->id)
            ->where('is_published', true)
            ->whereNotNull('api_football_id')
            ->get();

        if (! $this->option('force')) {
            $teams = $teams->reject(fn (Team $t) => Player::where('team_id', $t->id)->exists());
        }

        if ($teams->isEmpty()) {
            $this->info('Nothing to sync - every team already has a squad (use --force to refresh).');

            return self::SUCCESS;
        }

        $client = app(ApiFootballClient::class);
        $totalPlayers = 0;

        foreach ($teams as $team) {
            $synced = $this->syncTeam($team, (int) $league->season, $client);
            $totalPlayers += $synced;
            $this->info("{$team->name}: {$synced} player(s) synced.");
        }

        $this->info("Done. {$totalPlayers} player(s) synced across {$teams->count()} team(s).");

        return self::SUCCESS;
    }

    private function syncTeam(Team $team, int $season, ApiFootballClient $client): int
    {
        // A handful of clubs (seen on FC Cincinnati) haven't had their squad
        // re-tagged to the current season yet on API-Football's side, even
        // though the club, fixtures, and everything else is current - real
        // player data just sits one season behind. Falling back to season-1
        // only when the current season comes back completely empty (not as
        // a general preference) keeps this from ever silently overwriting a
        // team's real current-season squad with an older one.
        $first = $client->getPlayersByTeam($team->api_football_id, $season, 1);

        if (! $first || empty($first['response'])) {
            $fallback = $client->getPlayersByTeam($team->api_football_id, $season - 1, 1);

            if ($fallback && ! empty($fallback['response'])) {
                $this->warn("{$team->name}: no squad tagged to season {$season} yet - using season ".($season - 1).' instead.');
                $season--;
                $first = $fallback;
            }
        }

        $synced = 0;
        $page = 1;
        $totalPages = 1;

        do {
            $response = $page === 1 ? $first : $client->getPlayersByTeam($team->api_football_id, $season, $page);

            if (! $response || empty($response['response'])) {
                break;
            }

            foreach ($response['response'] as $entry) {
                $stat = collect($entry['statistics'] ?? [])->first(fn ($s) => ($s['team']['id'] ?? null) === $team->api_football_id);

                if (! $stat) {
                    continue;
                }

                $apiPosition = $stat['games']['position'] ?? null;
                $position = self::POSITION_MAP[$apiPosition] ?? null;

                if (! $position) {
                    continue;
                }

                Player::updateOrCreate(
                    ['team_id' => $team->id, 'api_football_id' => $entry['player']['id']],
                    [
                        'name' => $entry['player']['name'],
                        'position' => $position,
                        'shirt_number' => $stat['games']['number'] ?? null,
                        'nationality' => $entry['player']['nationality'] ?? null,
                        'photo_url' => $entry['player']['photo'] ?? null,
                        'is_captain' => (bool) ($stat['games']['captain'] ?? false),
                        'goals' => $stat['goals']['total'] ?? 0,
                        'assists' => $stat['goals']['assists'] ?? 0,
                    ]
                );
                $synced++;
            }

            $totalPages = $response['paging']['total'] ?? 1;
            $page++;
        } while ($page <= $totalPages);

        return $synced;
    }
}
