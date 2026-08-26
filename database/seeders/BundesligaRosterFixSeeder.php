<?php

namespace Database\Seeders;

use App\Models\League;
use App\Models\Team;
use Illuminate\Database\Seeder;

/**
 * Corrects the Bundesliga roster to the real 2026-27 season and clears the
 * fictional matchday-1 narrative text (table_intro/table_closing) that
 * described results that never happened, ahead of activating the league
 * with real fixtures the same way Serie A/Ligue 1/Saudi Pro League were.
 *
 * Diffed against a fresh API-Football /teams call: 13 of the site's
 * original 18 clubs are still correct. 5 have since been relegated
 * (Heidenheim, St. Pauli, Holstein Kiel, Bochum, Wolfsburg) and are left
 * untouched/unpublished rather than deleted. Their real 2026-27
 * replacements - Schalke 04, Hamburger SV, SC Paderborn 07, 1. FC Köln and
 * SV Elversberg - are created here with real venue/capacity/founded data
 * and crests sourced from API-Football's /teams endpoint, crests
 * personally verified before use.
 *
 * The 13 keepers also get their real api_football_id set, even though
 * API-Football's coverage for this league lacks lineups/events/statistics
 * - it's still useful for whatever partial data (predictions, coaches) is
 * available, and costs nothing to set correctly.
 */
class BundesligaRosterFixSeeder extends Seeder
{
    private const KEEPERS = [
        'bayer-leverkusen' => 168,
        'bayern-munich' => 157,
        'borussia-dortmund' => 165,
        'borussia-monchengladbach' => 163,
        'eintracht-frankfurt' => 169,
        'fc-augsburg' => 170,
        'mainz-05' => 164,
        'rb-leipzig' => 173,
        'sc-freiburg' => 160,
        'tsg-hoffenheim' => 167,
        'union-berlin' => 182,
        'vfb-stuttgart' => 172,
        'werder-bremen' => 162,
    ];

    private const NEW_CLUBS = [
        ['name' => 'Schalke 04', 'full' => 'FC Schalke 04', 'slug' => 'schalke-04', 'code' => 's04', 'color' => '#004D9D', 'stadium' => 'VELTINS-Arena', 'capacity' => 62278, 'founded' => 1904, 'api_id' => 174],
        ['name' => 'Hamburger SV', 'full' => 'Hamburger Sport-Verein', 'slug' => 'hamburger-sv', 'code' => 'hsv', 'color' => '#0C2C56', 'stadium' => 'Volksparkstadion', 'capacity' => 57000, 'founded' => 1887, 'api_id' => 175],
        ['name' => 'Paderborn', 'full' => 'SC Paderborn 07', 'slug' => 'sc-paderborn-07', 'code' => 'scp', 'color' => '#1861AC', 'stadium' => 'Home Deluxe Arena', 'capacity' => 15306, 'founded' => 1907, 'api_id' => 185],
        ['name' => 'Köln', 'full' => '1. FC Köln', 'slug' => '1-fc-koln', 'code' => 'koe', 'color' => '#ED1C24', 'stadium' => 'RheinEnergieSTADION', 'capacity' => 50076, 'founded' => 1948, 'api_id' => 192],
        ['name' => 'Elversberg', 'full' => 'SV 07 Elversberg', 'slug' => 'sv-elversberg', 'code' => 'sve', 'color' => '#000000', 'stadium' => 'URSAPHARM-Arena an der Kaiserlinde', 'capacity' => 11150, 'founded' => 1907, 'api_id' => 1660],
    ];

    public function run(): void
    {
        $league = League::where('slug', 'bundesliga')->first();

        if (! $league) {
            $this->command?->error('Bundesliga league not found - run LeagueSeeder first.');

            return;
        }

        $league->update([
            'api_football_id' => 78,
            'table_intro' => null,
            'table_closing' => null,
            'about_text' => "The Bundesliga is widely regarded as one of the best-attended and most competitively balanced top divisions in world football, built around a fan-first culture that keeps ticket prices low and terraces full. Eighteen clubs play a full home-and-away season, with Bayern Munich the division's most consistent force in recent decades and Bayer Leverkusen, Borussia Dortmund and RB Leipzig its most regular modern challengers.\nThe league's promotion and relegation link with the 2. Bundesliga is especially active - fixtures below and giant venues like Signal Iduna Park and the Allianz Arena, alongside the return of storied names such as Hamburger SV and Schalke 04 this season, are part of what keeps German football's pyramid so closely watched.",
        ]);

        foreach (self::KEEPERS as $slug => $apiId) {
            $team = Team::where('slug', $slug)->where('league_id', $league->id)->first();

            if (! $team) {
                $this->command?->warn("Keeper club not found, skipping: {$slug}");

                continue;
            }

            $team->update(['api_football_id' => $apiId]);
        }

        foreach (self::NEW_CLUBS as $t) {
            Team::updateOrCreate(
                ['slug' => $t['slug']],
                [
                    'league_id' => $league->id,
                    'name' => $t['name'],
                    'full_name' => $t['full'],
                    'crest_code' => $t['code'],
                    'color_hex' => $t['color'],
                    'stadium' => $t['stadium'],
                    'stadium_capacity' => $t['capacity'],
                    'founded_year' => $t['founded'],
                    'api_football_id' => $t['api_id'],
                ]
            );
        }

        $this->command?->info('Bundesliga roster corrected: 13 keepers mapped, 5 real replacement clubs created.');
    }
}
