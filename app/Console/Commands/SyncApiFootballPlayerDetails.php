<?php

namespace App\Console\Commands;

use App\Models\League;
use App\Models\Player;
use App\Services\ApiFootballClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Pulls real trophy history and real transfer history for every player in
 * a league - two extra API calls per player, so this is deliberately
 * separate from api-football:sync-squads (which covers everything else
 * for free from a single call). At ~3,150 players across all 9 leagues
 * this is genuinely expensive (6,000+ calls), so it's meant to be run
 * league by league, not all at once - safe to stop and resume any time,
 * since a player already checked (trophies/transfers not null, even if
 * empty - an empty result is a real "no major honours" answer, not a
 * skip marker) is never re-fetched without --force.
 */
#[Signature('api-football:sync-player-details {league : League slug, e.g. saudi-pro-league} {--force : Re-check every player, including ones already checked} {--limit=200 : Max players to process this run, so a run can be split across multiple invocations}')]
#[Description("Pull real trophy history and real transfer history per player from API-Football - expensive, run league by league")]
class SyncApiFootballPlayerDetails extends Command
{
    public function handle(): int
    {
        $league = League::where('slug', $this->argument('league'))->first();

        if (! $league) {
            $this->error("No league found with slug \"{$this->argument('league')}\".");

            return self::FAILURE;
        }

        $query = Player::whereHas('team', fn ($q) => $q->where('league_id', $league->id))
            ->whereNotNull('api_football_id');

        if (! $this->option('force')) {
            $query->whereNull('trophies');
        }

        $players = $query->limit((int) $this->option('limit'))->get();

        if ($players->isEmpty()) {
            $this->info('Nothing to do - every player already checked (use --force to re-check).');

            return self::SUCCESS;
        }

        $client = app(ApiFootballClient::class);
        $withTrophies = 0;
        $withTransfers = 0;

        foreach ($players as $player) {
            $trophiesResponse = $client->getPlayerTrophies($player->api_football_id);
            $transfersResponse = $client->getPlayerTransfers($player->api_football_id);

            $trophies = $this->parseTrophies($trophiesResponse);
            $transfers = $this->parseTransfers($transfersResponse);

            $player->update([
                'trophies' => $trophies,
                'transfers' => $transfers,
            ]);

            if (! empty($trophies)) {
                $withTrophies++;
            }
            if (! empty($transfers)) {
                $withTransfers++;
            }
        }

        $this->info("Checked {$players->count()} player(s): {$withTrophies} with real trophies, {$withTransfers} with real transfer history.");
        $this->info('Run again with the same command to continue with the next batch, until "Nothing to do" appears.');

        return self::SUCCESS;
    }

    private function parseTrophies(?array $response): array
    {
        if (! $response || empty($response['response'])) {
            return [];
        }

        return collect($response['response'])
            ->filter(fn ($t) => in_array($t['place'] ?? null, ['Winner', '1st Group Stage'], true) || ($t['place'] ?? null) === 'Winner')
            ->map(fn ($t) => [
                'league' => $t['league'],
                'country' => $t['country'],
                'season' => $t['season'],
                'place' => $t['place'],
            ])
            ->values()
            ->all();
    }

    private function parseTransfers(?array $response): array
    {
        $transfers = $response['response'][0]['transfers'] ?? [];

        if (empty($transfers)) {
            return [];
        }

        return collect($transfers)
            ->map(fn ($t) => [
                'date' => $t['date'],
                'from' => $t['teams']['out']['name'] ?? null,
                'to' => $t['teams']['in']['name'] ?? null,
                'type' => $t['type'],
            ])
            ->sortByDesc('date')
            ->values()
            ->all();
    }
}
