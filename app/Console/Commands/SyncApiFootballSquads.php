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

                $player = $entry['player'];
                $birthDate = $player['birth']['date'] ?? null;

                Player::updateOrCreate(
                    ['team_id' => $team->id, 'api_football_id' => $player['id']],
                    [
                        'name' => $player['name'],
                        'position' => $position,
                        'shirt_number' => $stat['games']['number'] ?? null,
                        'nationality' => $player['nationality'] ?? null,
                        'birth_date' => (is_string($birthDate) && $birthDate !== '') ? $birthDate : null,
                        'birth_place' => $player['birth']['place'] ?? null,
                        'birth_country' => $player['birth']['country'] ?? null,
                        'height' => $player['height'] ?? null,
                        'weight' => $player['weight'] ?? null,
                        'injured' => (bool) ($player['injured'] ?? false),
                        'photo_url' => $player['photo'] ?? null,
                        'is_captain' => (bool) ($stat['games']['captain'] ?? false),
                        'goals' => $stat['goals']['total'] ?? 0,
                        'assists' => $stat['goals']['assists'] ?? 0,
                        'stats' => $this->parseStats($stat),
                    ]
                );
                $synced++;
            }

            $totalPages = $response['paging']['total'] ?? 1;
            $page++;
        } while ($page <= $totalPages);

        return $synced;
    }

    /** All of this comes from the same statistics block already fetched for goals/assists/position - no extra API call. */
    private function parseStats(array $stat): array
    {
        $out = [];

        if (isset($stat['games']['appearences'])) {
            $out['appearances'] = $stat['games']['appearences'];
        }
        if (isset($stat['games']['minutes'])) {
            $out['minutes'] = $stat['games']['minutes'];
        }
        if (isset($stat['games']['rating']) && $stat['games']['rating'] !== null) {
            $out['rating'] = round((float) $stat['games']['rating'], 2);
        }
        if (isset($stat['shots']['total'])) {
            $out['shots_total'] = $stat['shots']['total'];
            $out['shots_on_target'] = $stat['shots']['on'] ?? null;
        }
        if (isset($stat['passes']['total'])) {
            $out['passes_total'] = $stat['passes']['total'];
            $out['passes_key'] = $stat['passes']['key'] ?? null;
            $out['passes_accuracy'] = $stat['passes']['accuracy'] ?? null;
        }
        if (isset($stat['tackles']['total'])) {
            $out['tackles_total'] = $stat['tackles']['total'];
            $out['interceptions'] = $stat['tackles']['interceptions'] ?? null;
        }
        if (isset($stat['duels']['total'])) {
            $out['duels_total'] = $stat['duels']['total'];
            $out['duels_won'] = $stat['duels']['won'] ?? null;
        }
        if (isset($stat['dribbles']['attempts'])) {
            $out['dribbles_attempts'] = $stat['dribbles']['attempts'];
            $out['dribbles_success'] = $stat['dribbles']['success'] ?? null;
        }
        if (isset($stat['fouls']['drawn'])) {
            $out['fouls_drawn'] = $stat['fouls']['drawn'];
            $out['fouls_committed'] = $stat['fouls']['committed'] ?? null;
        }
        if (isset($stat['cards']['yellow'])) {
            $out['yellow_cards'] = $stat['cards']['yellow'];
            $out['red_cards'] = $stat['cards']['red'] ?? 0;
        }
        if (isset($stat['goals']['saves'])) {
            $out['saves'] = $stat['goals']['saves'];
        }

        return $out;
    }
}
