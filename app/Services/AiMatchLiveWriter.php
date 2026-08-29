<?php

namespace App\Services;

use App\Models\MatchFixture;
use App\Models\Standing;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Writes the pre-match preview and half-time update for a fixture via the
 * Anthropic API - the two lifecycle stages that come before the full-time
 * report already handled by AiMatchReportWriter. Same rules: natural,
 * human-sounding writing grounded only in real data, no robotic phrasing.
 *
 * Both methods return null on any failure so ProgressLiveMatches can simply
 * skip and retry on the next scheduled run rather than saving broken
 * content or crashing the whole pass over every fixture.
 */
class AiMatchLiveWriter
{
    /** @return array{home: string, away: string}|null */
    public function writePreview(MatchFixture $fixture): ?array
    {
        $home = $fixture->homeTeam;
        $away = $fixture->awayTeam;
        $homeStanding = Standing::where('league_id', $fixture->league_id)->where('team_id', $home->id)->first();
        $awayStanding = Standing::where('league_id', $fixture->league_id)->where('team_id', $away->id)->first();
        $homePosition = $homeStanding?->position ?? 'n/a';
        $homePoints = $homeStanding?->points ?? 0;
        $awayPosition = $awayStanding?->position ?? 'n/a';
        $awayPoints = $awayStanding?->points ?? 0;
        $kickoff = $fixture->kickoff_at->format('l, j F Y, g:i A');

        $prompt = <<<PROMPT
        You are a football news editor writing a short pre-match preview for a sports website. Write in a natural, human-sounding style, avoiding robotic AI phrasing (no "furthermore", "it is important to note").

        Match: {$home->name} vs {$away->name}
        Competition: {$fixture->league->name}
        Venue: {$fixture->venue}
        Kickoff: {$kickoff}
        {$home->name} current league position: {$homePosition} ({$homePoints} points)
        {$away->name} current league position: {$awayPosition} ({$awayPoints} points)

        Write two short previews, 2-3 sentences each, one written from each team's perspective - what's at stake for them in this match given their current position. Only use the facts given above - do not invent or guess any detail not explicitly listed. Do not name any manager, player, or other real person not given above - not even ones you're confident about from general knowledge, since names change and may already be outdated. If you reference when the match kicks off, use the exact date given above - never state a day of the week without checking it against that date first.

        Write like a real person, not like an AI trying to sound human: vary sentence length rather than a smooth even rhythm, never use "not only X but also Y", and avoid words like moreover, delve, boasts, showcases, testament to, seamless.

        Respond with ONLY valid JSON (no markdown fences, no commentary):
        {"home": "2-3 sentence preview from the home team's perspective", "away": "2-3 sentence preview from the away team's perspective"}
        PROMPT;

        $data = $this->callAndParseJson($prompt, 500);

        if (! $data || empty($data['home']) || empty($data['away'])) {
            return null;
        }

        // Deterministic fix, not just another prompt instruction the model
        // could still ignore - same class of bug as the "Sunday" match
        // report: a single real kickoff date, so any wrong weekday found
        // is safely correctable rather than something to discard over.
        return [
            'home' => AiFactChecker::fixDayOfWeek($data['home'], $fixture->kickoff_at),
            'away' => AiFactChecker::fixDayOfWeek($data['away'], $fixture->kickoff_at),
        ];
    }

    public function writeHalftime(MatchFixture $fixture, int $homeScoreHt, int $awayScoreHt): ?string
    {
        $home = $fixture->homeTeam->name;
        $away = $fixture->awayTeam->name;

        $prompt = <<<PROMPT
        You are a football live-updates writer for a sports website. Write a short half-time update, in a natural, human-sounding style, avoiding robotic AI phrasing.

        Match: {$home} vs {$away}
        Competition: {$fixture->league->name}
        Venue: {$fixture->venue}
        Half-time score: {$home} {$homeScoreHt}-{$awayScoreHt} {$away}

        Write 2-3 sentences summing up how the first half went, based only on the score given above - do not invent goal scorers, cards, or incidents, and do not name any manager, player, or other real person not given above. Write plainly, like a real person, not a press release - avoid words like moreover, delve, boasts, seamless.

        Respond with ONLY valid JSON (no markdown fences, no commentary):
        {"update": "2-3 sentence half-time update"}
        PROMPT;

        $data = $this->callAndParseJson($prompt, 300);

        return $data['update'] ?? null;
    }

    private function callAndParseJson(string $prompt, int $maxTokens): ?array
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
                    'max_tokens' => $maxTokens,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

            if ($response->failed()) {
                Log::warning('AI match live-content request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $text = trim((string) $response->json('content.0.text'));
            $text = preg_replace('/^```(?:json)?|```$/m', '', $text);
            $data = json_decode(trim((string) $text), true);

            return is_array($data) ? $data : null;
        } catch (\Throwable $e) {
            Log::warning('AI match live-content generation errored: '.$e->getMessage());

            return null;
        }
    }
}
