<?php

namespace App\Services;

use App\Models\League;
use App\Models\MatchFixture;
use App\Models\Standing;

/**
 * Recalculates a league's points table from its final matches. Leaves the
 * "zone" field (Champions League / relegation highlighting) untouched,
 * since that's an editorial choice an admin sets deliberately rather than
 * something that should be guessed from the table position.
 */
class StandingsCalculator
{
    public static function recalculate(League $league): void
    {
        $teamIds = $league->teams()->pluck('id');

        $stats = $teamIds->mapWithKeys(fn ($id) => [$id => [
            'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0,
            'goals_for' => 0, 'goals_against' => 0, 'points' => 0,
        ]])->all();

        $finalMatches = MatchFixture::where('league_id', $league->id)
            ->where('status', 'final')
            ->get(['home_team_id', 'away_team_id', 'home_score', 'away_score']);

        foreach ($finalMatches as $match) {
            if (! isset($stats[$match->home_team_id], $stats[$match->away_team_id])) {
                continue;
            }

            $home = &$stats[$match->home_team_id];
            $away = &$stats[$match->away_team_id];

            $home['played']++;
            $away['played']++;
            $home['goals_for'] += $match->home_score;
            $home['goals_against'] += $match->away_score;
            $away['goals_for'] += $match->away_score;
            $away['goals_against'] += $match->home_score;

            if ($match->home_score > $match->away_score) {
                $home['won']++;
                $home['points'] += 3;
                $away['lost']++;
            } elseif ($match->home_score < $match->away_score) {
                $away['won']++;
                $away['points'] += 3;
                $home['lost']++;
            } else {
                $home['drawn']++;
                $away['drawn']++;
                $home['points']++;
                $away['points']++;
            }

            unset($home, $away);
        }

        $ranked = collect($stats)
            ->map(fn ($row, $teamId) => array_merge($row, [
                'team_id' => $teamId,
                'goal_difference' => $row['goals_for'] - $row['goals_against'],
            ]))
            ->sortByDesc(fn ($row) => [$row['points'], $row['goal_difference'], $row['goals_for']])
            ->values();

        foreach ($ranked as $index => $row) {
            Standing::updateOrCreate(
                ['league_id' => $league->id, 'team_id' => $row['team_id']],
                [
                    'position' => $index + 1,
                    'played' => $row['played'],
                    'won' => $row['won'],
                    'drawn' => $row['drawn'],
                    'lost' => $row['lost'],
                    'goals_for' => $row['goals_for'],
                    'goals_against' => $row['goals_against'],
                    'goal_difference' => $row['goal_difference'],
                    'points' => $row['points'],
                ]
            );
        }
    }
}
