<?php

namespace App\Console\Commands;

use App\Models\League;
use App\Models\Team;
use App\Services\FootballDataClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Matches our existing Team rows to football-data.org's team IDs by name,
 * so the fixture/results sync knows which real match belongs to which of
 * our teams. Every match - confident or not - is printed for review,
 * because a wrong mapping here silently corrupts every result synced for
 * that team afterward. Nothing is saved without --apply.
 */
#[Signature('football-data:map-teams {league : League slug, e.g. premier-league} {code : football-data.org competition code, e.g. PL} {--apply : Actually save the mappings; without this, only prints them for review}')]
#[Description('Match our teams to football-data.org team IDs by name - review the printed table before re-running with --apply')]
class MapFootballDataTeams extends Command
{
    public function handle(): int
    {
        $league = League::where('slug', $this->argument('league'))->first();

        if (! $league) {
            $this->error("No team found with slug \"{$this->argument('league')}\".");

            return self::FAILURE;
        }

        $code = strtoupper($this->argument('code'));
        $response = app(FootballDataClient::class)->getTeams($code);

        if (! $response || empty($response['teams'])) {
            $this->error('Could not fetch teams from football-data.org - check the API key and competition code.');

            return self::FAILURE;
        }

        $theirTeams = collect($response['teams']);
        $ourTeams = Team::where('league_id', $league->id)->where('is_published', true)->get();

        $rows = [];
        $unmatched = [];

        foreach ($ourTeams as $team) {
            $best = null;
            $bestScore = -1;

            foreach ($theirTeams as $theirs) {
                foreach ([$theirs['name'], $theirs['shortName'], $theirs['tla']] as $candidate) {
                    similar_text($this->normalize($team->name), $this->normalize($candidate), $percent);
                    if ($percent > $bestScore) {
                        $bestScore = $percent;
                        $best = $theirs;
                    }
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
                $team->update(['external_id' => $best['id']]);
            }
        }

        $this->table(['Our team', 'Matched to', 'External ID', 'Confidence', 'Auto-matched'], $rows);

        if (! empty($unmatched)) {
            $this->warn(count($unmatched).' team(s) need manual review - low-confidence match, set external_id by hand for these:');
            foreach ($unmatched as $team) {
                $this->line("  {$team->slug}");
            }
        }

        if (! $this->option('apply')) {
            $this->info('Dry run - nothing saved. Re-run with --apply once the table above looks correct.');
        } else {
            $this->info('Saved external_id for all confidently-matched teams.');
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
