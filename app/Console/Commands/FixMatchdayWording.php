<?php

namespace App\Console\Commands;

use App\Models\League;
use Illuminate\Console\Command;

/**
 * One-off content fix: the League table_intro/table_closing fields for
 * four leagues used "Matchday" / "matchday" in their prose (e.g. "after
 * Matchday 1 on goal difference"). The site no longer uses that word
 * anywhere - this rewrites those specific sentences to say "round"
 * instead, preserving everything else about the copy. Safe to run more
 * than once: it's idempotent, matching on slug and setting fixed text.
 */
class FixMatchdayWording extends Command
{
    protected $signature = 'content:fix-matchday-wording';

    protected $description = 'Rewrite League table_intro/table_closing text that used the word "matchday"';

    public function handle(): int
    {
        $updates = [
            'premier-league' => [
                'table_intro' => "One round in, Liverpool sit top of the Premier League on goal difference from Everton, with Manchester City, Sunderland, Brighton, Manchester United and Wolves all level on points after opening wins of their own. The table below reflects nothing more than a single result apiece for every club &mdash; treat it as an early snapshot, not a verdict &mdash; but it's already worth a look at who's making the fastest start.",
            ],
            'la-liga' => [
                'table_intro' => "Barcelona lead the way after round one on goal difference, ahead of Real Madrid, Athletic Bilbao and Espanyol, with four more clubs level on three points after opening wins. As in every league, one round of results settles very little &mdash; but it's the first real data point of a long season, and worth a look.",
                'table_closing' => "The presence of Girona, Leganes and Osasuna inside the early top seven is a reminder of how competitive the middle of La Liga has become in recent seasons, even as Real Madrid and Barcelona remain the clear financial and historical heavyweights at the top. Atl&eacute;tico Madrid sitting mid-table after one round is unlikely to cause genuine alarm at the Metropolitano, but it's a reminder that Diego Simeone's sides have rarely started fast. At the bottom, Real Sociedad and Valencia's heavy opening defeats have them occupying relegation-zone positions earlier than either club would like, while Villarreal and newly promoted Valladolid round out an early bottom four. It's the smallest of sample sizes, but La Liga's traditional shape &mdash; Real Madrid and Barcelona at the top, a fiercely contested middle, and a handful of clubs fighting from round one to stay up &mdash; already looks familiar.",
            ],
            'serie-a' => [
                'table_intro' => "Atalanta head the Serie A table after round one on goal difference from Inter Milan, with Como, AC Milan, Sassuolo, Bologna and Lecce all level on maximum points after opening wins of their own. As ever with a single round of results, treat the early shape of the table as a snapshot rather than a forecast.",
            ],
            'ligue-1' => [
                'table_intro' => "Paris Saint-Germain sit top of Ligue 1 after round one on goal difference, with Brest and Rennes both level on points and goal difference just behind the champions, and Marseille, Le Havre and Lens rounding out an early perfect-record group. It's the smallest possible sample size, but every club's season now has its first data point.",
            ],
        ];

        foreach ($updates as $slug => $fields) {
            $league = League::where('slug', $slug)->first();

            if (! $league) {
                $this->warn("Skipped {$slug}: no league with that slug found.");

                continue;
            }

            $league->update($fields);
            $this->info("Updated {$slug}: ".implode(', ', array_keys($fields)));
        }

        return self::SUCCESS;
    }
}
