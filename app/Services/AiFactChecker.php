<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;

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

    /** Every prompt already bans these; this is the code-level backstop for when the model uses one anyway. */
    private const BANNED_WORDS = [
        'moreover', 'furthermore', 'delve', 'tapestry', 'boasts', 'showcases', 'underscores',
        'testament to', 'realm', 'seamless', 'ever-evolving', 'landscape', 'game-changer',
        "in today's world", "it's worth noting", 'at the end of the day', 'in conclusion', 'overall,',
    ];

    /**
     * Language that only makes sense if a goal, card, or substitution
     * actually happened - used to catch a "quiet stretch" commentary line
     * (no real events given) that invents one anyway. Deliberately broad:
     * false positives (a fine line discarded because it brushed one of
     * these words) are the acceptable cost of never letting a phantom
     * event through - matches the site's own zero-tolerance rule on this.
     */
    private const EVENT_CLAIM_KEYWORDS = [
        'scores', 'scored', 'nets', 'finds the net', 'back of the net', 'beats the keeper', 'header home', 'taps in', 'slots home',
        'yellow card', 'red card', 'sent off', 'booked', 'dismissed', 'second yellow',
        'substituted', 'brought on', 'comes on for', 'replaces him', 'replaces her',
    ];

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

    /**
     * True if $text names a day of the week that matches none of the real
     * dates given - for content that legitimately references more than
     * one real date (e.g. club news covering both a past result and an
     * upcoming fixture), where a single blind swap-to-the-real-day
     * (fixDayOfWeek) risks "correcting" a mention that was already right
     * about the OTHER date. Safer to reject outright than to guess which
     * date a mismatch was supposed to refer to.
     */
    public static function containsUnrecognizedDayOfWeek(string $text, array $realDates): bool
    {
        $allowedDays = collect($realDates)
            ->filter()
            ->map(fn (Carbon $date) => $date->format('l'))
            ->unique()
            ->all();

        foreach (self::WEEKDAYS as $day) {
            if (in_array($day, $allowedDays, true)) {
                continue;
            }

            if (preg_match('/\b'.$day.'\b/', $text)) {
                return true;
            }
        }

        return false;
    }

    /**
     * True if $text uses language claiming a goal, card, or substitution
     * happened. Only meaningful when checking a "quiet stretch" commentary
     * line written from no real events - a line describing real given
     * events is expected and fine to use this language.
     */
    public static function containsUnverifiedEventClaim(string $text): bool
    {
        $lower = Str::lower($text);

        foreach (self::EVENT_CLAIM_KEYWORDS as $keyword) {
            if (str_contains($lower, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /** Returns the first banned AI-tone word/phrase found in $text, or null if it's clean. */
    public static function findBannedTone(string $text): ?string
    {
        $lower = Str::lower($text);

        foreach (self::BANNED_WORDS as $word) {
            if (str_contains($lower, $word)) {
                return $word;
            }
        }

        return null;
    }
}
