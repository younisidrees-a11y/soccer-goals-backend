<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * A deterministic, code-level safety net for AI-written text - not a
 * substitute for careful prompts, a backstop for when the model still gets
 * a checkable fact wrong despite being explicitly told not to. That's
 * exactly what happened once already: a match report stated "Sunday" for a
 * match that was really played on Friday, despite the prompt saying "only
 * use the facts given." Every check here verifies against real data
 * already in hand (a MatchFixture's own score/kickoff_at) - never against
 * anything the AI itself said, and never by asking the AI to check its own
 * work.
 */
class AiFactChecker
{
    private const WEEKDAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    /** True only if the exact "home-away" scoreline appears as digits somewhere in the text. */
    public static function containsScore(string $text, int $homeScore, int $awayScore): bool
    {
        return str_contains($text, "{$homeScore}-{$awayScore}");
    }

    /**
     * Swaps any wrong day-of-week name found in $text for the real one. A
     * model given the real date in the prompt can still slip and state a
     * different day somewhere in its response - this fixes that
     * deterministically rather than trusting every mention landed right.
     * A no-op when no weekday name is present, or the one used is already
     * correct.
     */
    public static function fixDayOfWeek(string $text, Carbon $realDate): string
    {
        $realDay = $realDate->format('l');

        foreach (self::WEEKDAYS as $day) {
            if ($day === $realDay) {
                continue;
            }

            $text = preg_replace('/\b'.$day.'\b/', $realDay, $text);
        }

        return $text;
    }
}
