<?php

namespace Tests\Support;

use App\Contracts\LlmClient;

/**
 * Deterministic embeddings for integration tests (no Gemini network calls).
 */
class FixedVectorLlmClient implements LlmClient
{
    public function embed(string $text): array
    {
        $dimensions = (int) config('recommendation.embedding_dimensions', 8);
        $seed = crc32($text);
        $vector = [];

        for ($i = 0; $i < $dimensions; $i++) {
            $vector[] = (($seed + ($i * 9973)) % 1000) / 1000.0;
        }

        return $vector;
    }

    public function generate(string $prompt): string
    {
        return 'Integration test assistant reply.';
    }
}
