<?php

namespace App\Services;

use App\Contracts\LlmClient;
use JsonException;
use Throwable;

class ChatFilterExtractionService
{
    public function __construct(
        private readonly LlmClient $llm,
        private readonly ChatPromptBuilder $prompts,
    ) {}

    /**
     * @return array{
     *     needs_clarification: bool,
     *     clarification_question: ?string,
     *     search_query: ?string,
     *     in_stock_only: bool,
     *     max_price_cents: ?int,
     *     min_price_cents: ?int,
     *     attributes: array<string, mixed>
     * }
     */
    /**
     * @param  list<array{role: string, content: string}>  $priorHistory
     */
    public function extract(string $userMessage, array $priorHistory = []): array
    {
        try {
            $raw = $this->llm->generate(
                $this->prompts->buildFilterExtractionPrompt($userMessage, $priorHistory),
            );
            $decoded = json_decode(trim($raw), true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($decoded)) {
                return $this->fallback($userMessage);
            }

            return $this->normalize($decoded, $userMessage);
        } catch (JsonException|Throwable) {
            return $this->fallback($userMessage);
        }
    }

    /**
     * @param  array<string, mixed>  $decoded
     * @return array{
     *     needs_clarification: bool,
     *     clarification_question: ?string,
     *     search_query: ?string,
     *     in_stock_only: bool,
     *     max_price_cents: ?int,
     *     min_price_cents: ?int,
     *     attributes: array<string, mixed>
     * }
     */
    private function normalize(array $decoded, string $userMessage): array
    {
        $needsClarification = (bool) ($decoded['needs_clarification'] ?? false);
        $question = $decoded['clarification_question'] ?? null;
        $searchQuery = $decoded['search_query'] ?? null;

        if ($needsClarification) {
            return [
                'needs_clarification' => true,
                'clarification_question' => is_string($question) && trim($question) !== ''
                    ? trim($question)
                    : 'What product type are you looking for, and do you have a budget in mind?',
                'search_query' => null,
                'in_stock_only' => false,
                'max_price_cents' => null,
                'min_price_cents' => null,
                'attributes' => [],
            ];
        }

        $query = is_string($searchQuery) && trim($searchQuery) !== ''
            ? trim($searchQuery)
            : trim($userMessage);

        if ($query === '') {
            return $this->fallback($userMessage);
        }

        return [
            'needs_clarification' => false,
            'clarification_question' => null,
            'search_query' => $query,
            'in_stock_only' => (bool) ($decoded['in_stock_only'] ?? false),
            'max_price_cents' => $this->nullablePositiveInt($decoded['max_price_cents'] ?? null),
            'min_price_cents' => $this->nullablePositiveInt($decoded['min_price_cents'] ?? null),
            'attributes' => is_array($decoded['attributes'] ?? null) ? $decoded['attributes'] : [],
        ];
    }

    /**
     * @return array{
     *     needs_clarification: bool,
     *     clarification_question: ?string,
     *     search_query: ?string,
     *     in_stock_only: bool,
     *     max_price_cents: ?int,
     *     min_price_cents: ?int,
     *     attributes: array<string, mixed>
     * }
     */
    private function fallback(string $userMessage): array
    {
        $trimmed = trim($userMessage);

        return [
            'needs_clarification' => false,
            'clarification_question' => null,
            'search_query' => $trimmed !== '' ? $trimmed : null,
            'in_stock_only' => false,
            'max_price_cents' => null,
            'min_price_cents' => null,
            'attributes' => [],
        ];
    }

    private function nullablePositiveInt(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        $int = (int) $value;

        return $int > 0 ? $int : null;
    }
}
