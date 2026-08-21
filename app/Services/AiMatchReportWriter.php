<?php

namespace App\Services;

use App\Models\MatchFixture;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Writes a genuinely unique match report per fixture via the Anthropic API,
 * so reports don't repeat the way a fixed template bank eventually does.
 *
 * Returns null on any failure (no key configured, network error, bad
 * response) so callers can fall back to the local template bank instead of
 * breaking the publish command.
 */
class AiMatchReportWriter
{
    public function write(MatchFixture $fixture, int $homeScore, int $awayScore, array $stats): ?string
    {
        $apiKey = config('services.anthropic.key');

        if (! $apiKey) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
                ->timeout(30)
                ->retry(2, 500)
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => config('services.anthropic.model'),
                    'max_tokens' => 400,
                    'messages' => [
                        ['role' => 'user', 'content' => $this->buildPrompt($fixture, $homeScore, $awayScore, $stats)],
                    ],
                ]);

            if ($response->failed()) {
                Log::warning('AI match report request failed', [
                    'match_id' => $fixture->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $text = trim((string) $response->json('content.0.text'));

            return $text !== '' ? $text : null;
        } catch (\Throwable $e) {
            Log::warning('AI match report generation errored: '.$e->getMessage(), ['match_id' => $fixture->id]);

            return null;
        }
    }

    private function buildPrompt(MatchFixture $fixture, int $homeScore, int $awayScore, array $stats): string
    {
        $home = $fixture->homeTeam->name;
        $away = $fixture->awayTeam->name;
        $venue = $fixture->venue;
        $league = $fixture->league->name;

        $possessionHome = $stats['possession']['home'] ?? 50;
        $possessionAway = $stats['possession']['away'] ?? 50;
        $shotsHome = $stats['shots']['home'] ?? '-';
        $shotsAway = $stats['shots']['away'] ?? '-';

        return <<<PROMPT
        Write a short football match report for a sports news website, in plain, natural, human English - the kind a real local sports journalist would write for a matchday roundup. Avoid robotic or corporate phrasing (no "furthermore", "it is important to note", "in conclusion"), avoid bullet points, and do not repeat the scoreline as a heading.

        Match: {$home} {$homeScore}-{$awayScore} {$away}
        Venue: {$venue}
        Competition: {$league}
        Possession: {$home} {$possessionHome}% - {$possessionAway}% {$away}
        Shots: {$home} {$shotsHome} - {$shotsAway} {$away}

        Write two short paragraphs, around 90-130 words in total, covering how the game went and what the result means. Weave the numbers in naturally rather than listing them. Return only the two paragraphs, no title, no headings, no markdown formatting.

        The exact score ({$homeScore}-{$awayScore}) MUST appear as digits somewhere in the text, not just described in words like "a point apiece" - and any description of the result (draw, win, thriller, rout) must be factually consistent with that exact score.

        Do not name any manager, player, coach, or other real person - not even ones you're confident about from general knowledge, since names change and may already be outdated. Refer to the teams and squads only, never named individuals who weren't provided above.

        Write like a real person on deadline, not like an AI trying to sound human: vary sentence length noticeably rather than settling into a smooth even rhythm, never use "not only X but also Y", and avoid these words entirely: moreover, furthermore, delve, tapestry, boasts, showcases, underscores, testament to, realm, seamless, landscape, in today's world, it's worth noting, in conclusion, overall.
        PROMPT;
    }
}
