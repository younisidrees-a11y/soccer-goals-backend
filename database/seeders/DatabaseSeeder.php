<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with the current static-site data.
     */
    public function run(): void
    {
        $this->call([
            LeagueSeeder::class,
            LeagueTableContentSeeder::class,
            TeamSeeder::class,
            StandingSeeder::class,
            MatchSeeder::class,
            PlayerSeeder::class,
            AdminUserSeeder::class,
            NewsArticleSeeder::class,
        ]);
    }
}
