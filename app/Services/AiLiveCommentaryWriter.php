<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Writes one new minute-by-minute commentary line for a live match via the
 * Anthropic API. Same rule as every other AI writer on this site: grounded
 * only in real data actually passed in, never inventing a play, incident,
 * or player action. For a quiet stretch with no new event, it's told to
 * describe the real run of play from score/minute/possession only - not to
 * manufacture drama that didn't happen.
 *
 * Returns null on any failure so the sync command just skips that tick
 * rather than saving broken or repetitive content.
 */
class AiLiveCommentaryWriter
{
    /**
     * @param  array<int, array{minute: string, type: string, detail: string, player: ?string, assist: ?string, team: string}>  $newEvents  Real events that happened since the last commentary line - empty if it's a quiet stretch.
     * @param  array{home: int, away: int}|null  $possession  Real current possession split, if API-Football has it yet.
     * @param  array<int, string>  $recentLines  The last few published lines, oldest first - purely so the model doesn't repeat itself or contradict its own tone.
     */
    public function write(
        string $homeTeam,
        string $awayTeam,
        string $competition,
        int $elapsedMinute,
        int $homeScore,
        int $awayScore,
        array $newEvents,
        ?array $possession,
        array $recentLines,
    ): ?string {
        $eventsText = empty($newEvents)
            ? 'No new event since the last update - this is a quiet stretch of play.'
            : collect($newEvents)->map(fn ($e) => "- {$e['minute']}': {$e['type']} ({$e['detail']}) - {$e['team']}".($e['player'] ? ", {$e['player']}" : '').($e['assist'] ? ", assist {$e['assist']}" : ''))->implode("\n");

        $possessionText = $possession
            ? "Current possession: {$homeTeam} {$possession['home']}% - {$awayTeam} {$possession['away']}%"
            : 'Possession split not available yet.';

        $recentText = empty($recentLines)
            ? '(this is the first line of commentary for this match)'
            : collect($recentLines)->map(fn ($l) => "- {$l}")->implode("\n");

        $prompt = <<<PROMPT
        You are a football live-commentary writer for a sports website, writing one short new line of minute-by-minute text commentary for a match in progress right now.

        Competition: {$competition}
        Match: {$homeTeam} vs {$awayTeam}
        Current minute: {$elapsedMinute}'
        Current score: {$homeTeam} {$homeScore}-{$awayScore} {$awayTeam}
        {$possessionText}

        New events since the last update:
        {$eventsText}

        Your last few commentary lines (do not repeat these, vary your phrasing and sentence rhythm from them):
        {$recentText}

        Write ONE new line, 1-2 sentences, for minute {$elapsedMinute}. Ground it ONLY in the facts given above - the score, the minute, the possession split, and any new events listed. If there are new events, describe them plainly using only the team/player/detail given - do not invent what led to them or how they happened. If it's a quiet stretch, describe the general run of play using only the score/minute/possession given (e.g. who's had more of the ball) - do not invent a specific chance, shot, or passage of play that isn't backed by the facts above. Do not name any player, manager, or person not explicitly given above. Write like a real commentator speaking live, not a press release - vary sentence length, avoid words like moreover, delve, boasts, seamless, testament to.

        Respond with ONLY valid JSON (no markdown fences, no commentary):
        {"line": "your 1-2 sentence commentary line"}
        PROMPT;

        $data = $this->callAndParseJson($prompt, 200);

        $line = $data['line'] ?? null;

        return is_string($line) && trim($line) !== '' ? trim($line) : null;
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
                ->timeout(20)
                ->retry(2, 500)
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => config('services.anthropic.model'),
                    'max_tokens' => $maxTokens,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

            if ($response->failed()) {
                Log::warning('AI live commentary request failed', [
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
            Log::warning('AI live commentary generation errored: '.$e->getMessage());

            return null;
        }
    }
}
