<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Services\AiClubHistoryWriter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Writes the "About" prose for a club's page from facts an admin has
 * already entered and verified in the Filament panel (founded_year,
 * honours_facts). Deliberately refuses to run if those facts are missing -
 * this is real content about a real organisation, so there is no safe
 * fallback to "just make something up" the way there is for simulated
 * match content.
 */
#[Signature('teams:write-history {team : Team slug, e.g. manchester-city}')]
#[Description('Write a club history from admin-verified facts (founded year, honours) - refuses to run until those facts are filled in')]
class WriteClubHistory extends Command
{
    public function handle(): int
    {
        $team = Team::where('slug', $this->argument('team'))->first();

        if (! $team) {
            $this->error("No team found with slug \"{$this->argument('team')}\".");

            return self::FAILURE;
        }

        if (! $team->founded_year && ! $team->honours_facts) {
            $this->error("{$team->name} has no founded_year or honours_facts filled in yet. Add verified facts in the admin panel first - see Teams > {$team->name} > Trophies & honours.");

            return self::FAILURE;
        }

        $written = app(AiClubHistoryWriter::class)->write($team);

        if (! $written) {
            $this->error('AI generation failed - check storage/logs/laravel.log. history_essay was not changed.');

            return self::FAILURE;
        }

        $team->update(['history_essay' => $written]);

        $this->info("Wrote history for {$team->name}.");

        return self::SUCCESS;
    }
}
