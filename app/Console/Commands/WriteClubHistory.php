<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Services\AiClubHistoryWriter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Writes the "About" prose and head coach introduction for a club's page,
 * from facts an admin has already entered and verified in the Filament
 * panel (founded_year, honours_facts, manager_facts). Deliberately refuses
 * to run either part if its facts are missing - this is real content about
 * a real organisation and a real, currently-employed person, so there is
 * no safe fallback to "just make something up" the way there is for
 * simulated match content.
 *
 * Pass a specific team slug for one club, or --all to process every
 * published team that has at least one of the required fact fields set.
 */
#[Signature('teams:write-history {team? : Team slug, e.g. manchester-city} {--all : Process every published team with facts filled in}')]
#[Description('Write club history and head coach bios from admin-verified facts - refuses to run until those facts are filled in')]
class WriteClubHistory extends Command
{
    public function handle(): int
    {
        if ($this->option('all')) {
            return $this->handleAll();
        }

        if (! $this->argument('team')) {
            $this->error('Pass a team slug, or use --all to process every team.');

            return self::FAILURE;
        }

        $team = Team::where('slug', $this->argument('team'))->first();

        if (! $team) {
            $this->error("No team found with slug \"{$this->argument('team')}\".");

            return self::FAILURE;
        }

        return $this->processTeam($team) ? self::SUCCESS : self::FAILURE;
    }

    private function handleAll(): int
    {
        $teams = Team::published()
            ->where(function ($query) {
                $query->whereNotNull('founded_year')
                    ->orWhereNotNull('honours_facts')
                    ->orWhereNotNull('manager');
            })
            ->get();

        $this->info("Processing {$teams->count()} team(s)...");

        foreach ($teams as $team) {
            $this->processTeam($team);
        }

        return self::SUCCESS;
    }

    private function processTeam(Team $team): bool
    {
        $writer = app(AiClubHistoryWriter::class);
        $wroteAnything = false;

        if ($team->founded_year || $team->honours_facts) {
            $history = $writer->write($team);

            if ($history) {
                $team->update(['history_essay' => $history]);
                $this->info("Wrote club history for {$team->name}.");
                $wroteAnything = true;
            } else {
                $this->error("Club history generation failed for {$team->name} - check storage/logs/laravel.log.");
            }
        } else {
            $this->warn("Skipped club history for {$team->name}: no founded_year or honours_facts filled in yet.");
        }

        if ($team->manager) {
            $bio = $writer->writeManagerBio($team);

            if ($bio) {
                $team->update(['manager_bio' => $bio]);
                $this->info("Wrote head coach bio for {$team->manager} ({$team->name}).");
                $wroteAnything = true;
            } else {
                $this->error("Head coach bio generation failed for {$team->name} - check storage/logs/laravel.log.");
            }
        } else {
            $this->warn("Skipped head coach bio for {$team->name}: no manager name set.");
        }

        return $wroteAnything;
    }
}
