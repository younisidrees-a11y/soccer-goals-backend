<?php

namespace Database\Seeders;

use App\Models\League;
use Illuminate\Database\Seeder;

class LeagueSeeder extends Seeder
{
    public function run(): void
    {
        $leagues = [
            ['name' => 'Premier League', 'slug' => 'premier-league', 'country' => 'England', 'flag_code' => 'eng', 'season' => '2026-27', 'total_matchdays' => 38],
            ['name' => 'La Liga', 'slug' => 'la-liga', 'country' => 'Spain', 'flag_code' => 'esp', 'season' => '2026-27', 'total_matchdays' => 38],
            ['name' => 'Serie A', 'slug' => 'serie-a', 'country' => 'Italy', 'flag_code' => 'ita', 'season' => '2026-27', 'total_matchdays' => 38],
            ['name' => 'Bundesliga', 'slug' => 'bundesliga', 'country' => 'Germany', 'flag_code' => 'deu', 'season' => '2026-27', 'total_matchdays' => 34],
            ['name' => 'Ligue 1', 'slug' => 'ligue-1', 'country' => 'France', 'flag_code' => 'fra', 'season' => '2026-27', 'total_matchdays' => 34],
        ];

        foreach ($leagues as $league) {
            League::updateOrCreate(['slug' => $league['slug']], $league);
        }
    }
}
