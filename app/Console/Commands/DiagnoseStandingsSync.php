<?php

namespace App\Console\Commands;

use App\Models\League;
use App\Models\MatchFixture;
use App\Models\Standing;
use Illuminate\Console\Command;

/**
 * One-off diagnostic: independently recomputes each team's W/D/L/GF/GA/Pts
 * from real final MatchFixture rows and compares it against what's stored
 * in the Standing table, across every published league. Read-only - makes
 * no changes. Reports only teams where the two disagree, so a clean run
 * prints nothing per league.
 */
class DiagnoseStandingsSync extends Command
{
    protected $signature = 'diagnose:standings-sync';

    protected $description = 'Compare stored Standing rows against real match results, league by league';

    public function handle(): int
    {
        $leagues = League::published()->orderBy('name')->get();
        $totalMismatches = 0;

        foreach ($leagues as $league) {
            $standings = Standing::with('team')->where('league_id', $league->id)->get()->keyBy('team_id');

            if ($standings->isEmpty()) {
                continue;
            }

            $matches = MatchFixture::where('league_id', $league->id)
                ->where('status', 'final')
                ->whereNotNull('home_score')
                ->whereNotNull('away_score')
                ->get(['home_team_id', 'away_team_id', 'home_score', 'away_score']);

            $computed = [];
            foreach ($matches as $m) {
                foreach ([true, false] as $isHome) {
                    $teamId = $isHome ? $m->home_team_id : $m->away_team_id;
                    $for = $isHome ? $m->home_score : $m->away_score;
                    $against = $isHome ? $m->away_score : $m->home_score;

                    $computed[$teamId] ??= ['played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0, 'gf' => 0, 'ga' => 0];
                    $computed[$teamId]['played']++;
                    $computed[$teamId]['gf'] += $for;
                    $computed[$teamId]['ga'] += $against;
                    if ($for > $against) {
                        $computed[$teamId]['won']++;
                    } elseif ($for < $against) {
                        $computed[$teamId]['lost']++;
                    } else {
                        $computed[$teamId]['drawn']++;
                    }
                }
            }

            $leagueMismatches = [];
            foreach ($standings as $teamId => $s) {
                $c = $computed[$teamId] ?? ['played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0, 'gf' => 0, 'ga' => 0];
                $computedPts = $c['won'] * 3 + $c['drawn'];

                if ($s->played != $c['played'] || $s->won != $c['won'] || $s->drawn != $c['drawn']
                    || $s->lost != $c['lost'] || $s->goals_for != $c['gf'] || $s->goals_against != $c['ga']
                    || $s->points != $computedPts) {
                    $leagueMismatches[] = sprintf(
                        '%s: stored P%d W%d D%d L%d GF%d GA%d Pts%d | real P%d W%d D%d L%d GF%d GA%d Pts%d',
                        $s->team->name ?? "team#{$teamId}",
                        $s->played, $s->won, $s->drawn, $s->lost, $s->goals_for, $s->goals_against, $s->points,
                        $c['played'], $c['won'], $c['drawn'], $c['lost'], $c['gf'], $c['ga'], $computedPts
                    );
                }
            }

            if ($leagueMismatches) {
                $this->warn("--- {$league->name}: ".count($leagueMismatches).' mismatch(es) ---');
                foreach ($leagueMismatches as $line) {
                    $this->line('  '.$line);
                }
                $totalMismatches += count($leagueMismatches);
            } else {
                $this->info("{$league->name}: OK ({$standings->count()} teams checked)");
            }
        }

        $this->line('');
        $this->line("Total mismatches: {$totalMismatches}");

        return self::SUCCESS;
    }
}
