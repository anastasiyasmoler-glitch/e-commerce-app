<?php

namespace App\Infrastructure;

use App\Contracts\LlmClient;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiLlmClient implements LlmClient
{
    public function embed(string $text): array
    {
        $dimensions = (int) config('recommendation.embedding_dimensions');
        $model = (string) config('recommendation.gemini.embedding_model', 'gemini-embedding-001');

        $response = $this->request("models/{$model}:embedContent", [
            'model' => "models/{$model}",
            'content' => [
                'parts' => [['text' => $text]],
            ],
            'outputDimensionality' => $dimensions,
        ]);

        $values = $response['embedding']['values'] ?? null;
        if (! is_array($values) || $values === []) {
            throw new RuntimeException('Gemini embed response has no vector values.');
        }

        return array_map(static fn ($value): float => (float) $value, array_values($values));
    }

    public function generate(string $prompt): string
    {
        $model = (string) config('recommendation.gemini.model', 'gemini-2.0-flash');

        $response = $this->request("models/{$model}:generateContent", [
            'contents' => [
                [
                    'parts' => [['text' => $prompt]],
                ],
            ],
        ]);

        $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('Gemini generate response has no text.');
        }

        return $text;
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    private function request(string $path, array $body): array
    {
        $apiKey = (string) config('recommendation.gemini.api_key');
        if ($apiKey === '') {
            throw new RuntimeException('GEMINI_API_KEY is empty.');
        }

        $response = Http::timeout(30)
            ->acceptJson()
            ->withHeaders(['x-goog-api-key' => $apiKey])
            ->post('https://generativelanguage.googleapis.com/v1beta/'.$path, $body);

        if ($response->failed()) {
            throw new RuntimeException('Gemini HTTP '.$response->status().': '.$response->body());
        }

        $decoded = $response->json();
        if (! is_array($decoded)) {
            throw new RuntimeException('Gemini response is not JSON object.');
        }

        return $decoded;
    }
}
