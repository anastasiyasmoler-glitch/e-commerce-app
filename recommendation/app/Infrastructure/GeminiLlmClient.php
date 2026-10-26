<?php

namespace App\Infrastructure;

use App\Contracts\LlmClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

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

        $timeout = max(1, (int) config('recommendation.gemini.timeout_seconds', 25));
        $maxAttempts = max(1, (int) config('recommendation.gemini.retry_times', 2) + 1);
        $sleepMs = max(0, (int) config('recommendation.gemini.retry_sleep_ms', 300));

        $lastError = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $response = Http::timeout($timeout)
                    ->acceptJson()
                    ->withHeaders(['x-goog-api-key' => $apiKey])
                    ->post('https://generativelanguage.googleapis.com/v1beta/'.$path, $body);

                if ($response->successful()) {
                    $decoded = $response->json();
                    if (! is_array($decoded)) {
                        throw new RuntimeException('Gemini response is not JSON object.');
                    }

                    return $decoded;
                }

                $status = $response->status();
                $lastError = 'Gemini HTTP '.$status.': '.$response->body();

                if ($this->shouldRetryHttp($status) && $attempt < $maxAttempts) {
                    $this->pause($sleepMs, $attempt);

                    continue;
                }

                throw new RuntimeException($lastError);
            } catch (ConnectionException $exception) {
                $lastError = $exception->getMessage();
                if ($attempt < $maxAttempts) {
                    $this->pause($sleepMs, $attempt);

                    continue;
                }

                throw new RuntimeException('Gemini connection failed: '.$lastError, 0, $exception);
            } catch (RuntimeException $exception) {
                throw $exception;
            } catch (Throwable $exception) {
                $lastError = $exception->getMessage();
                if ($attempt < $maxAttempts) {
                    $this->pause($sleepMs, $attempt);

                    continue;
                }

                throw new RuntimeException('Gemini request failed: '.$lastError, 0, $exception);
            }
        }

        throw new RuntimeException($lastError ?? 'Gemini request failed.');
    }

    private function shouldRetryHttp(int $status): bool
    {
        return in_array($status, [408, 429, 500, 502, 503, 504], true);
    }

    private function pause(int $sleepMs, int $attempt): void
    {
        if ($sleepMs < 1) {
            return;
        }

        usleep($sleepMs * 1000 * $attempt);
    }
}
