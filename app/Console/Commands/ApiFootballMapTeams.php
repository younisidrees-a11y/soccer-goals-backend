<?php

namespace App\Console\Commands;

use App\Models\League;
use App\Models\Team;
use App\Services\ApiFootballClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Matches our Team rows to API-Football's team IDs by name, the same way
 * football-data:map-teams matches them to football-data.org's IDs - a
 * second, independent ID because API-Football is a different provider
 * with its own numbering. Every match is printed for review; nothing is
 * saved without --apply.
 */
#[Signature('api-football:map-teams {league : League slug, e.g. premier-league} {id : API-Football league ID, e.g. 39 for Premier League} {season : Season year, e.g. 2026} {--apply : Actually save the mappings; without this, only prints them for review}')]
#[Description('Match our teams to API-Football team IDs by name - review the printed table before re-running with --apply')]
class ApiFootballMapTeams extends Command
{
    public function handle(): int
    {
        $league = League::where('slug', $this->argument('league'))->first();

        if (! $league) {
            $this->error("No league found with slug \"{$this->argument('league')}\".");

            return self::FAILURE;
        }

        $id = (int) $this->argument('id');
        $season = (int) $this->argument('season');

        if ($this->option('apply') && $league->api_football_id !== $id) {
            $league->update(['api_football_id' => $id]);
            $this->info("Set {$league->name}'s api_football_id to {$id}.");
        }

        $response = app(ApiFootballClient::class)->getTeams($id, $season);

        if (! $response || empty($response['response'])) {
            $this->error('Could not fetch teams from API-Football - check the key, league ID, and season.');

            return self::FAILURE;
        }

        $theirTeams = collect($response['response'])->pluck('team');
        $ourTeams = Team::where('league_id', $league->id)->where('is_published', true)->get();

        $rows = [];
        $unmatched = [];

        foreach ($ourTeams as $team) {
            $best = null;
            $bestScore = -1;

            foreach ($theirTeams as $theirs) {
                similar_text($this->normalize($team->name), $this->normalize($theirs['name']), $percent);
                if ($percent > $bestScore) {
                    $bestScore = $percent;
                    $best = $theirs;
                }
            }

            $confident = $bestScore >= 70;
            $rows[] = [
                $team->name,
                $best['name'] ?? '-',
                $best['id'] ?? '-',
                round($bestScore).'%',
                $confident ? 'yes' : 'REVIEW',
            ];

            if (! $confident) {
                $unmatched[] = $team;
            } elseif ($this->option('apply')) {
                $team->update(['api_football_id' => $best['id']]);
            }
        }

        $this->table(['Our team', 'Matched to', 'API-Football ID', 'Confidence', 'Auto-matched'], $rows);

        if (! empty($unmatched)) {
            $this->warn(count($unmatched).' team(s) need manual review - low-confidence match, set api_football_id by hand for these:');
            foreach ($unmatched as $team) {
                $this->line("  {$team->slug}");
            }
        }

        if (! $this->option('apply')) {
            $this->info('Dry run - nothing saved. Re-run with --apply once the table above looks correct.');
        } else {
            $this->info('Saved api_football_id for all confidently-matched teams.');
        }

        return self::SUCCESS;
    }

    private function normalize(string $name): string
    {
        $name = strtolower($name);
        $name = str_replace(['fc', 'afc', 'cf', 'sad', '.', ',', '&'], '', $name);
        $name = preg_replace('/\s+/', ' ', $name);

        return trim($name);
    }
}
