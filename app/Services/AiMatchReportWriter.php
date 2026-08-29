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
    public function write(MatchFixture $fixture, int $homeScore, int $awayScore, array $stats, ?array $motm = null): ?string
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
                        ['role' => 'user', 'content' => $this->buildPrompt($fixture, $homeScore, $awayScore, $stats, $motm)],
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

    private function buildPrompt(MatchFixture $fixture, int $homeScore, int $awayScore, array $stats, ?array $motm = null): string
    {
        $home = $fixture->homeTeam->name;
        $away = $fixture->awayTeam->name;
        $venue = $fixture->venue;
        $league = $fixture->league->name;

        $facts = [
            "Match: {$home} {$homeScore}-{$awayScore} {$away}",
            "Date played: {$fixture->kickoff_at->format('l, j F Y')}",
        ];

        if ($venue) {
            $facts[] = "Venue: {$venue}";
        }

        $facts[] = "Competition: {$league}";

        if (isset($fixture->home_score_ht, $fixture->away_score_ht)) {
            $facts[] = "Half-time score: {$home} {$fixture->home_score_ht}-{$fixture->away_score_ht} {$away}";
        }

        // Only include a stat when we actually have it - never invent a
        // 50/50 default or a guessed number, since that would misrepresent
        // a real match.
        if (isset($stats['possession']['home'], $stats['possession']['away'])) {
            $facts[] = "Possession: {$home} {$stats['possession']['home']}% - {$stats['possession']['away']}% {$away}";
        }

        if (isset($stats['shots']['home'], $stats['shots']['away'])) {
            $facts[] = "Total shots: {$home} {$stats['shots']['home']} - {$stats['shots']['away']} {$away}";
        }

        if (isset($stats['shots_on_target']['home'], $stats['shots_on_target']['away'])) {
            $facts[] = "Shots on target: {$home} {$stats['shots_on_target']['home']} - {$stats['shots_on_target']['away']} {$away}";
        }

        if (isset($stats['corners']['home'], $stats['corners']['away'])) {
            $facts[] = "Corners: {$home} {$stats['corners']['home']} - {$stats['corners']['away']} {$away}";
        }

        $motmInstruction = 'Do not name any manager, player, coach, or other real person - not even ones you\'re confident about from general knowledge, since names change and may already be outdated. Refer to the teams and squads only, never named individuals.';

        if ($motm) {
            $facts[] = "Man of the Match (official rating {$motm['rating']}/10): {$motm['name']}, playing for {$motm['team_name']}";
            $motmInstruction = "You may name exactly one real person: {$motm['name']}, given above as Man of the Match - work them into the report naturally. Do not name any other manager, player, coach, or real person not explicitly given above.";
        }

        $factsBlock = implode("\n        ", $facts);

        return <<<PROMPT
        Write a short football match report for a sports news website, in plain, natural, human English - the kind a real local sports journalist would write for a matchday roundup. Avoid robotic or corporate phrasing (no "furthermore", "it is important to note", "in conclusion"), avoid bullet points, and do not repeat the scoreline as a heading.

        {$factsBlock}

        Only use the facts given above - if possession or shot counts aren't listed, don't mention or estimate them at all, just describe the match in terms of the score and any half-time score given. If you reference when the match was played, use the exact date given above - never state a day of the week without checking it against that date first.

        Write two short paragraphs, around 90-130 words in total, covering how the game went and what the result means. Weave the numbers in naturally rather than listing them. Return only the two paragraphs, no title, no headings, no markdown formatting.

        The exact score ({$homeScore}-{$awayScore}) MUST appear as digits somewhere in the text, not just described in words like "a point apiece" - and any description of the result (draw, win, thriller, rout) must be factually consistent with that exact score.

        {$motmInstruction}

        Write like a real person on deadline, not like an AI trying to sound human: vary sentence length noticeably rather than settling into a smooth even rhythm, never use "not only X but also Y", and avoid these words entirely: moreover, furthermore, delve, tapestry, boasts, showcases, underscores, testament to, realm, seamless, landscape, in today's world, it's worth noting, in conclusion, overall.
        PROMPT;
    }
}
