<?php

namespace App\Services;

use App\Models\Team;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Turns admin-verified real facts (founded_year, honours_facts, manager
 * bio facts) into natural prose for a club's page - the AI never supplies
 * the facts themselves, only the writing. Unlike match reports (fictional,
 * simulated results), club history and a named, currently-employed head
 * coach are real, checkable facts about real people and organisations, so
 * both methods here deliberately refuse to run until a human has entered
 * and verified the facts in the admin panel.
 */
class AiClubHistoryWriter
{
    public function write(Team $team): ?string
    {
        return $this->call($this->buildHistoryPrompt($team), $team, 700);
    }

    public function writeManagerBio(Team $team): ?string
    {
        return $this->call($this->buildManagerPrompt($team), $team, 300);
    }

    private function call(string $prompt, Team $team, int $maxTokens): ?string
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
                ->timeout(45)
                ->retry(2, 500)
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => config('services.anthropic.model'),
                    'max_tokens' => $maxTokens,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

            if ($response->failed()) {
                Log::warning('AI club content request failed', [
                    'team_id' => $team->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $text = trim((string) $response->json('content.0.text'));

            return $text !== '' ? $text : null;
        } catch (\Throwable $e) {
            Log::warning('AI club content generation errored: '.$e->getMessage(), ['team_id' => $team->id]);

            return null;
        }
    }

    private function buildHistoryPrompt(Team $team): string
    {
        $founded = $team->founded_year ?: 'not given - do not guess or state a founding year';
        $honours = $team->honours_facts ?: 'none given - do not mention trophies or honours at all';
        $stadium = $team->stadium ? "Home ground: {$team->stadium}" : '';

        return <<<PROMPT
        Write a short club history for a football website, in plain, natural, human English - the kind a knowledgeable local football writer would produce, not a Wikipedia summary and not a press release.

        Club: {$team->full_name}
        Founded: {$founded}
        {$stadium}
        Trophies and honours (use these exact facts only): {$honours}

        Rules - these matter more than usual, because this is real factual content about a real club, not a simulated match:
        - Only state facts given above. Do not invent, estimate, or round any year, trophy count, or honour not explicitly listed.
        - If founding year or honours are marked "not given" above, simply don't mention that topic at all rather than guessing.
        - Do not name any specific manager, player, or owner unless that name was given to you above.
        - Write like a real person, not an AI trying to sound human: vary sentence length, avoid words like moreover, delve, boasts, showcases, testament to, seamless, and never use "not only X but also Y".

        Write 2-3 short paragraphs (150-220 words) covering the club's founding and identity, and its trophy history if given. Return only the prose, no heading, no markdown.
        PROMPT;
    }

    private function buildManagerPrompt(Team $team): string
    {
        $facts = $team->manager_facts ?: 'no further facts given - only confirm the name and club, nothing else';

        return <<<PROMPT
        Write a short introduction to a football club's current head coach, for a football website. Plain, natural, human English - the kind a knowledgeable local football writer would produce, not a Wikipedia summary and not a press release.

        Head coach: {$team->manager}
        Club: {$team->full_name}
        Verified facts about this person (use only these - do not add anything else): {$facts}

        Rules - this is a real, currently-employed person, so accuracy matters more than usual:
        - Only state facts explicitly given above. Do not invent, guess, or assume nationality, career history, playing career, achievements, or tactical style beyond what's listed.
        - If the facts above are sparse, write a shorter, simpler introduction rather than padding it with invented detail.
        - Write like a real person, not an AI trying to sound human: avoid words like moreover, delve, boasts, showcases, testament to, seamless.

        Write exactly 2-3 sentences (40-60 words total). Return only the sentences, no heading, no markdown.
        PROMPT;
    }
}
