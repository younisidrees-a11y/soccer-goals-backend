<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Writes a genuinely unique news article via the Anthropic API from a
 * fully-built prompt supplied by the caller (see PublishNewsArticle, which
 * assembles category-specific context and instructions).
 *
 * Returns null on any failure - no key configured, network error, or a
 * response that isn't valid JSON in the expected shape - so the command can
 * report that nothing was created instead of saving broken content.
 */
class AiNewsWriter
{
    /**
     * @return array{title: string, dek: string, body: string, meta_title: string, meta_description: string, meta_keywords: string}|null
     */
    public function write(string $prompt): ?array
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
                    'max_tokens' => 1200,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

            if ($response->failed()) {
                Log::warning('AI news request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $text = trim((string) $response->json('content.0.text'));
            $text = preg_replace('/^```(?:json)?|```$/m', '', $text);
            $data = json_decode(trim((string) $text), true);

            if (! is_array($data) || empty($data['title']) || empty($data['body'])) {
                Log::warning('AI news response missing expected fields', ['raw' => $text]);

                return null;
            }

            return $data;
        } catch (\Throwable $e) {
            Log::warning('AI news generation errored: '.$e->getMessage());

            return null;
        }
    }
}
