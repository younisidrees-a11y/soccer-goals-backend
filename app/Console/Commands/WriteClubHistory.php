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
 */
#[Signature('teams:write-history {team : Team slug, e.g. manchester-city}')]
#[Description('Write a club history and head coach bio from admin-verified facts - refuses to run until those facts are filled in')]
class WriteClubHistory extends Command
{
    public function handle(): int
    {
        $team = Team::where('slug', $this->argument('team'))->first();

        if (! $team) {
            $this->error("No team found with slug \"{$this->argument('team')}\".");

            return self::FAILURE;
        }

        $writer = app(AiClubHistoryWriter::class);
        $wroteAnything = false;

        if ($team->founded_year || $team->honours_facts) {
            $history = $writer->write($team);

            if ($history) {
                $team->update(['history_essay' => $history]);
                $this->info("Wrote club history for {$team->name}.");
                $wroteAnything = true;
            } else {
                $this->error('Club history generation failed - check storage/logs/laravel.log.');
            }
        } else {
            $this->warn("Skipped club history: no founded_year or honours_facts filled in yet.");
        }

        if ($team->manager) {
            $bio = $writer->writeManagerBio($team);

            if ($bio) {
                $team->update(['manager_bio' => $bio]);
                $this->info("Wrote head coach bio for {$team->manager}.");
                $wroteAnything = true;
            } else {
                $this->error('Head coach bio generation failed - check storage/logs/laravel.log.');
            }
        } else {
            $this->warn("Skipped head coach bio: no manager name set for {$team->name}.");
        }

        return $wroteAnything ? self::SUCCESS : self::FAILURE;
    }
}
