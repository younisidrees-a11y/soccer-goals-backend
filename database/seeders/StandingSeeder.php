<?php

namespace Database\Seeders;

use App\Models\League;
use App\Models\Standing;
use App\Models\Team;
use Illuminate\Database\Seeder;

class StandingSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['league_slug' => 'premier-league', 'team_code' => 'liv', 'position' => 1, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 3, 'goals_against' => 0, 'goal_difference' => 3, 'points' => 3, 'zone' => 'ucl'],
            ['league_slug' => 'premier-league', 'team_code' => 'eve', 'position' => 2, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 0, 'goal_difference' => 2, 'points' => 3, 'zone' => 'ucl'],
            ['league_slug' => 'premier-league', 'team_code' => 'mci', 'position' => 3, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 1, 'goal_difference' => 1, 'points' => 3, 'zone' => 'ucl'],
            ['league_slug' => 'premier-league', 'team_code' => 'sun', 'position' => 4, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 1, 'goal_difference' => 1, 'points' => 3, 'zone' => 'ucl'],
            ['league_slug' => 'premier-league', 'team_code' => 'bha', 'position' => 5, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 0, 'goal_difference' => 1, 'points' => 3, 'zone' => 'none'],
            ['league_slug' => 'premier-league', 'team_code' => 'mun', 'position' => 6, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 0, 'goal_difference' => 1, 'points' => 3, 'zone' => 'none'],
            ['league_slug' => 'premier-league', 'team_code' => 'wol', 'position' => 7, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 0, 'goal_difference' => 1, 'points' => 3, 'zone' => 'none'],
            ['league_slug' => 'premier-league', 'team_code' => 'ars', 'position' => 8, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 1, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'premier-league', 'team_code' => 'avl', 'position' => 9, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 1, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'premier-league', 'team_code' => 'cry', 'position' => 10, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 2, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'premier-league', 'team_code' => 'new', 'position' => 11, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 1, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'premier-league', 'team_code' => 'nfo', 'position' => 12, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 1, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'premier-league', 'team_code' => 'whu', 'position' => 13, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 2, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'premier-league', 'team_code' => 'bou', 'position' => 14, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 0, 'goals_against' => 3, 'goal_difference' => -3, 'points' => 0, 'zone' => 'none'],
            ['league_slug' => 'premier-league', 'team_code' => 'bre', 'position' => 15, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 1, 'goals_against' => 2, 'goal_difference' => -1, 'points' => 0, 'zone' => 'none'],
            ['league_slug' => 'premier-league', 'team_code' => 'bur', 'position' => 16, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 0, 'goals_against' => 2, 'goal_difference' => -2, 'points' => 0, 'zone' => 'none'],
            ['league_slug' => 'premier-league', 'team_code' => 'che', 'position' => 17, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 1, 'goals_against' => 2, 'goal_difference' => -1, 'points' => 0, 'zone' => 'rel'],
            ['league_slug' => 'premier-league', 'team_code' => 'ful', 'position' => 18, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 0, 'goals_against' => 1, 'goal_difference' => -1, 'points' => 0, 'zone' => 'rel'],
            ['league_slug' => 'premier-league', 'team_code' => 'lee', 'position' => 19, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 0, 'goals_against' => 1, 'goal_difference' => -1, 'points' => 0, 'zone' => 'rel'],
            ['league_slug' => 'premier-league', 'team_code' => 'tot', 'position' => 20, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 0, 'goals_against' => 1, 'goal_difference' => -1, 'points' => 0, 'zone' => 'rel'],
            ['league_slug' => 'la-liga', 'team_code' => 'bar', 'position' => 1, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 4, 'goals_against' => 1, 'goal_difference' => 3, 'points' => 3, 'zone' => 'ucl'],
            ['league_slug' => 'la-liga', 'team_code' => 'rma', 'position' => 2, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 3, 'goals_against' => 1, 'goal_difference' => 2, 'points' => 3, 'zone' => 'ucl'],
            ['league_slug' => 'la-liga', 'team_code' => 'ath', 'position' => 3, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 0, 'goal_difference' => 2, 'points' => 3, 'zone' => 'ucl'],
            ['league_slug' => 'la-liga', 'team_code' => 'esp', 'position' => 4, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 1, 'goal_difference' => 1, 'points' => 3, 'zone' => 'ucl'],
            ['league_slug' => 'la-liga', 'team_code' => 'gir', 'position' => 5, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 1, 'goal_difference' => 1, 'points' => 3, 'zone' => 'none'],
            ['league_slug' => 'la-liga', 'team_code' => 'leg', 'position' => 6, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 1, 'goal_difference' => 1, 'points' => 3, 'zone' => 'none'],
            ['league_slug' => 'la-liga', 'team_code' => 'osa', 'position' => 7, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 0, 'goal_difference' => 1, 'points' => 3, 'zone' => 'none'],
            ['league_slug' => 'la-liga', 'team_code' => 'atm', 'position' => 8, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 1, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'la-liga', 'team_code' => 'ala', 'position' => 9, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 1, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'la-liga', 'team_code' => 'bet', 'position' => 10, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 1, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'la-liga', 'team_code' => 'get', 'position' => 11, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 0, 'goals_against' => 0, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'la-liga', 'team_code' => 'mll', 'position' => 12, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 0, 'goals_against' => 0, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'la-liga', 'team_code' => 'sev', 'position' => 13, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 1, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'la-liga', 'team_code' => 'cel', 'position' => 14, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 1, 'goals_against' => 2, 'goal_difference' => -1, 'points' => 0, 'zone' => 'none'],
            ['league_slug' => 'la-liga', 'team_code' => 'lpa', 'position' => 15, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 1, 'goals_against' => 2, 'goal_difference' => -1, 'points' => 0, 'zone' => 'none'],
            ['league_slug' => 'la-liga', 'team_code' => 'ray', 'position' => 16, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 0, 'goals_against' => 1, 'goal_difference' => -1, 'points' => 0, 'zone' => 'none'],
            ['league_slug' => 'la-liga', 'team_code' => 'rso', 'position' => 17, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 1, 'goals_against' => 3, 'goal_difference' => -2, 'points' => 0, 'zone' => 'rel'],
            ['league_slug' => 'la-liga', 'team_code' => 'val', 'position' => 18, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 1, 'goals_against' => 4, 'goal_difference' => -3, 'points' => 0, 'zone' => 'rel'],
            ['league_slug' => 'la-liga', 'team_code' => 'vil', 'position' => 19, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 0, 'goals_against' => 2, 'goal_difference' => -2, 'points' => 0, 'zone' => 'rel'],
            ['league_slug' => 'la-liga', 'team_code' => 'vll', 'position' => 20, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 1, 'goals_against' => 2, 'goal_difference' => -1, 'points' => 0, 'zone' => 'rel'],
            ['league_slug' => 'serie-a', 'team_code' => 'ata', 'position' => 1, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 3, 'goals_against' => 1, 'goal_difference' => 2, 'points' => 3, 'zone' => 'ucl'],
            ['league_slug' => 'serie-a', 'team_code' => 'int', 'position' => 2, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 0, 'goal_difference' => 2, 'points' => 3, 'zone' => 'ucl'],
            ['league_slug' => 'serie-a', 'team_code' => 'com', 'position' => 3, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 1, 'goal_difference' => 1, 'points' => 3, 'zone' => 'ucl'],
            ['league_slug' => 'serie-a', 'team_code' => 'mil', 'position' => 4, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 1, 'goal_difference' => 1, 'points' => 3, 'zone' => 'ucl'],
            ['league_slug' => 'serie-a', 'team_code' => 'sas', 'position' => 5, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 1, 'goal_difference' => 1, 'points' => 3, 'zone' => 'none'],
            ['league_slug' => 'serie-a', 'team_code' => 'bol', 'position' => 6, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 0, 'goal_difference' => 1, 'points' => 3, 'zone' => 'none'],
            ['league_slug' => 'serie-a', 'team_code' => 'lec', 'position' => 7, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 0, 'goal_difference' => 1, 'points' => 3, 'zone' => 'none'],
            ['league_slug' => 'serie-a', 'team_code' => 'emp', 'position' => 8, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 0, 'goals_against' => 0, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'serie-a', 'team_code' => 'juv', 'position' => 9, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 1, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'serie-a', 'team_code' => 'laz', 'position' => 10, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 2, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'serie-a', 'team_code' => 'nap', 'position' => 11, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 1, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'serie-a', 'team_code' => 'rom', 'position' => 12, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 2, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'serie-a', 'team_code' => 'ver', 'position' => 13, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 0, 'goals_against' => 0, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'serie-a', 'team_code' => 'cag', 'position' => 14, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 1, 'goals_against' => 2, 'goal_difference' => -1, 'points' => 0, 'zone' => 'none'],
            ['league_slug' => 'serie-a', 'team_code' => 'fio', 'position' => 15, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 1, 'goals_against' => 3, 'goal_difference' => -2, 'points' => 0, 'zone' => 'none'],
            ['league_slug' => 'serie-a', 'team_code' => 'gen', 'position' => 16, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 0, 'goals_against' => 2, 'goal_difference' => -2, 'points' => 0, 'zone' => 'none'],
            ['league_slug' => 'serie-a', 'team_code' => 'par', 'position' => 17, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 1, 'goals_against' => 2, 'goal_difference' => -1, 'points' => 0, 'zone' => 'rel'],
            ['league_slug' => 'serie-a', 'team_code' => 'tor', 'position' => 18, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 1, 'goals_against' => 2, 'goal_difference' => -1, 'points' => 0, 'zone' => 'rel'],
            ['league_slug' => 'serie-a', 'team_code' => 'udi', 'position' => 19, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 0, 'goals_against' => 1, 'goal_difference' => -1, 'points' => 0, 'zone' => 'rel'],
            ['league_slug' => 'serie-a', 'team_code' => 'ven', 'position' => 20, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 0, 'goals_against' => 1, 'goal_difference' => -1, 'points' => 0, 'zone' => 'rel'],
            ['league_slug' => 'bundesliga', 'team_code' => 'bay', 'position' => 1, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 3, 'goals_against' => 0, 'goal_difference' => 3, 'points' => 3, 'zone' => 'ucl'],
            ['league_slug' => 'bundesliga', 'team_code' => 'bvb', 'position' => 2, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 3, 'goals_against' => 1, 'goal_difference' => 2, 'points' => 3, 'zone' => 'ucl'],
            ['league_slug' => 'bundesliga', 'team_code' => 'fch', 'position' => 3, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 1, 'goal_difference' => 1, 'points' => 3, 'zone' => 'ucl'],
            ['league_slug' => 'bundesliga', 'team_code' => 'm05', 'position' => 4, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 1, 'goal_difference' => 1, 'points' => 3, 'zone' => 'none'],
            ['league_slug' => 'bundesliga', 'team_code' => 'sge', 'position' => 5, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 0, 'goal_difference' => 1, 'points' => 3, 'zone' => 'none'],
            ['league_slug' => 'bundesliga', 'team_code' => 'fca', 'position' => 6, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 0, 'goals_against' => 0, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'bundesliga', 'team_code' => 'b04', 'position' => 7, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 2, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'bundesliga', 'team_code' => 'bmg', 'position' => 8, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 1, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'bundesliga', 'team_code' => 'tsg', 'position' => 9, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 0, 'goals_against' => 0, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'bundesliga', 'team_code' => 'kie', 'position' => 10, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 1, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'bundesliga', 'team_code' => 'scf', 'position' => 11, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 1, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'bundesliga', 'team_code' => 'stp', 'position' => 12, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 1, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'bundesliga', 'team_code' => 'vfb', 'position' => 13, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 2, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'bundesliga', 'team_code' => 'boc', 'position' => 14, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 1, 'goals_against' => 2, 'goal_difference' => -1, 'points' => 0, 'zone' => 'none'],
            ['league_slug' => 'bundesliga', 'team_code' => 'rbl', 'position' => 15, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 0, 'goals_against' => 3, 'goal_difference' => -3, 'points' => 0, 'zone' => 'rel'],
            ['league_slug' => 'bundesliga', 'team_code' => 'fcu', 'position' => 16, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 1, 'goals_against' => 3, 'goal_difference' => -2, 'points' => 0, 'zone' => 'rel'],
            ['league_slug' => 'bundesliga', 'team_code' => 'svw', 'position' => 17, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 1, 'goals_against' => 2, 'goal_difference' => -1, 'points' => 0, 'zone' => 'rel'],
            ['league_slug' => 'bundesliga', 'team_code' => 'wob', 'position' => 18, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 0, 'goals_against' => 1, 'goal_difference' => -1, 'points' => 0, 'zone' => 'rel'],
            ['league_slug' => 'ligue-1', 'team_code' => 'psg', 'position' => 1, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 3, 'goals_against' => 0, 'goal_difference' => 3, 'points' => 3, 'zone' => 'ucl'],
            ['league_slug' => 'ligue-1', 'team_code' => 'bre2', 'position' => 2, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 0, 'goal_difference' => 2, 'points' => 3, 'zone' => 'ucl'],
            ['league_slug' => 'ligue-1', 'team_code' => 'ren', 'position' => 3, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 0, 'goal_difference' => 2, 'points' => 3, 'zone' => 'ucl'],
            ['league_slug' => 'ligue-1', 'team_code' => 'om', 'position' => 4, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 1, 'goal_difference' => 1, 'points' => 3, 'zone' => 'none'],
            ['league_slug' => 'ligue-1', 'team_code' => 'leh', 'position' => 5, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 0, 'goal_difference' => 1, 'points' => 3, 'zone' => 'none'],
            ['league_slug' => 'ligue-1', 'team_code' => 'rcl', 'position' => 6, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 0, 'goal_difference' => 1, 'points' => 3, 'zone' => 'none'],
            ['league_slug' => 'ligue-1', 'team_code' => 'aja', 'position' => 7, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 2, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'ligue-1', 'team_code' => 'lil', 'position' => 8, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 1, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'ligue-1', 'team_code' => 'ol', 'position' => 9, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 1, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'ligue-1', 'team_code' => 'met', 'position' => 10, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 2, 'goals_against' => 2, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'ligue-1', 'team_code' => 'sdr', 'position' => 11, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 1, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'ligue-1', 'team_code' => 'tfc', 'position' => 12, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'goals_for' => 1, 'goals_against' => 1, 'goal_difference' => 0, 'points' => 1, 'zone' => 'none'],
            ['league_slug' => 'ligue-1', 'team_code' => 'ang', 'position' => 13, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 0, 'goals_against' => 1, 'goal_difference' => -1, 'points' => 0, 'zone' => 'none'],
            ['league_slug' => 'ligue-1', 'team_code' => 'asm', 'position' => 14, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 1, 'goals_against' => 2, 'goal_difference' => -1, 'points' => 0, 'zone' => 'none'],
            ['league_slug' => 'ligue-1', 'team_code' => 'mhs', 'position' => 15, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 0, 'goals_against' => 2, 'goal_difference' => -2, 'points' => 0, 'zone' => 'none'],
            ['league_slug' => 'ligue-1', 'team_code' => 'nan', 'position' => 16, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 0, 'goals_against' => 3, 'goal_difference' => -3, 'points' => 0, 'zone' => 'rel'],
            ['league_slug' => 'ligue-1', 'team_code' => 'nic', 'position' => 17, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 0, 'goals_against' => 1, 'goal_difference' => -1, 'points' => 0, 'zone' => 'rel'],
            ['league_slug' => 'ligue-1', 'team_code' => 'str', 'position' => 18, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'goals_for' => 0, 'goals_against' => 2, 'goal_difference' => -2, 'points' => 0, 'zone' => 'rel'],
        ];

        $leagueIds = League::pluck('id', 'slug');
        $teamIds = Team::pluck('id', 'crest_code');

        foreach ($rows as $row) {
            Standing::updateOrCreate(
                [
                    'league_id' => $leagueIds[$row['league_slug']],
                    'team_id' => $teamIds[$row['team_code']],
                ],
                [
                    'position' => $row['position'],
                    'played' => $row['played'],
                    'won' => $row['won'],
                    'drawn' => $row['drawn'],
                    'lost' => $row['lost'],
                    'goals_for' => $row['goals_for'],
                    'goals_against' => $row['goals_against'],
                    'goal_difference' => $row['goal_difference'],
                    'points' => $row['points'],
                    'zone' => $row['zone'],
                ]
            );
        }
    }
}
