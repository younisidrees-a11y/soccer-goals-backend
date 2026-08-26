<?php

namespace App\Console\Commands;

use App\Models\League;
use App\Models\Team;
use App\Services\ApiFootballClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Backfills real venue detail (city, address, surface, a real venue
 * photo) for every team in a league - one /teams call covers the whole
 * league's roster, since that's the same endpoint every team's crest and
 * stadium/capacity already came from. Cheap: ~9 calls total covers every
 * league on the site.
 */
#[Signature('api-football:sync-venues {league : League slug, e.g. premier-league}')]
#[Description("Backfill real venue city/address/surface/photo for every team in a league")]
class SyncApiFootballVenues extends Command
{
    public function handle(): int
    {
        $league = League::where('slug', $this->argument('league'))->first();

        if (! $league) {
            $this->error("No league found with slug \"{$this->argument('league')}\".");

            return self::FAILURE;
        }

        if (! $league->api_football_id) {
            $this->error("{$league->name} has no api_football_id set.");

            return self::FAILURE;
        }

        $response = app(ApiFootballClient::class)->getTeams($league->api_football_id, (int) $league->season);

        if (! $response || empty($response['response'])) {
            $this->error('Could not fetch teams from API-Football.');

            return self::FAILURE;
        }

        $byApiId = Team::where('league_id', $league->id)->whereNotNull('api_football_id')->get()->keyBy('api_football_id');
        $updated = 0;

        foreach ($response['response'] as $entry) {
            $team = $byApiId->get($entry['team']['id']);

            if (! $team) {
                continue;
            }

            $venue = $entry['venue'] ?? [];

            $team->update([
                'venue_city' => $venue['city'] ?? null,
                'venue_address' => $venue['address'] ?? null,
                'venue_surface' => $venue['surface'] ?? null,
                'venue_image_url' => $venue['image'] ?? null,
            ]);
            $updated++;
        }

        $this->info("Updated venue details for {$updated} team(s) in {$league->name}.");

        return self::SUCCESS;
    }
}
