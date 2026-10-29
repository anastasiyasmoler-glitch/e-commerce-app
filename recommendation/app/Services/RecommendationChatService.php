<?php

namespace App\Services;

use App\Contracts\LlmClient;
use App\Repositories\Contracts\CatalogProductProjectionRepositoryInterface;
use App\Repositories\Contracts\ProductVectorIndexInterface;
use App\Support\ProductAttributeMatcher;
use Throwable;

class RecommendationChatService
{
    public function __construct(
        private readonly CatalogProductProjectionRepositoryInterface $projections,
        private readonly ProductVectorIndexInterface $vectors,
        private readonly LlmClient $llm,
        private readonly ChatPromptBuilder $prompts,
        private readonly ChatFilterExtractionService $filterExtraction,
        private readonly ConversationService $conversations,
        private readonly CatalogReadModelProductVerifier $readModelVerifier,
    ) {}

    /**
     * @return array{
     *     conversation_id: string,
     *     answer: string,
     *     extracted_filters: array<string, mixed>,
     *     products: list<array<string, mixed>>
     * }
     */
    public function chat(
        int $userId,
        int $storeId,
        string $message,
        ?string $conversationId,
        int $limit,
    ): array {
        $conversation = $this->conversations->resolveForChat($userId, $storeId, $conversationId);

        $this->conversations->appendMessage($conversation['id'], 'user', $message);

        $priorHistory = $this->conversations->priorMessagesForPrompt($conversation['id']);

        $filters = $this->filterExtraction->extract($message, $priorHistory);
        $filters['store_id'] = $storeId;

        if ($filters['needs_clarification']) {
            $answer = (string) $filters['clarification_question'];

            return $this->finishTurn($conversation['id'], $answer, $filters, []);
        }

        $searchQuery = (string) ($filters['search_query'] ?? '');
        if ($searchQuery === '') {
            return $this->finishTurn(
                $conversation['id'],
                $this->prompts->noMatchAnswer(),
                $filters,
                [],
            );
        }

        try {
            $queryVector = $this->llm->embed($searchQuery);
        } catch (Throwable) {
            return $this->finishTurn(
                $conversation['id'],
                $this->prompts->embedUnavailableAnswer(),
                $filters,
                [],
            );
        }
        $hits = $this->vectors->searchSimilar(
            $storeId,
            $queryVector,
            $limit,
            (bool) $filters['in_stock_only'],
        );
        $productIds = array_values(array_unique(array_column($hits, 'product_id')));
        $rows = $this->projections->findPayloadsByStore($storeId, $productIds);
        $rows = $this->readModelVerifier->verify(
            $storeId,
            $rows,
            (bool) $filters['in_stock_only'],
        );
        $products = $this->formatProducts($storeId, $rows, $hits, $filters);

        $answer = $this->buildAnswer($message, $products, $priorHistory);

        return $this->finishTurn($conversation['id'], $answer, $filters, $products);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  list<array<string, mixed>>  $products
     * @return array{
     *     conversation_id: string,
     *     answer: string,
     *     extracted_filters: array<string, mixed>,
     *     products: list<array<string, mixed>>
     * }
     */
    private function finishTurn(
        string $conversationId,
        string $answer,
        array $filters,
        array $products,
    ): array {
        $this->conversations->appendMessage($conversationId, 'assistant', $answer);

        $this->conversations->touchActivity($conversationId);

        return [
            'conversation_id' => $conversationId,
            'answer' => $answer,
            'extracted_filters' => $filters,
            'products' => $products,
        ];
    }

    /**
     * @param  list<array{
     *     product_id: int,
     *     store_id: int,
     *     payload: array<string, mixed>,
     *     is_published: bool,
     *     in_stock: bool
     * }>  $rows
     * @param  list<array{product_id: int, store_id: int, score: float}>  $hits
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    private function formatProducts(int $storeId, array $rows, array $hits, array $filters): array
    {
        if ($rows === []) {
            return [];
        }

        $scores = [];
        foreach ($hits as $hit) {
            $scores[$hit['product_id']] = $hit['score'];
        }

        $maxPrice = $filters['max_price_cents'] ?? null;
        $minPrice = $filters['min_price_cents'] ?? null;
        $attributeFilters = is_array($filters['attributes'] ?? null) ? $filters['attributes'] : [];

        $products = [];
        foreach ($rows as $row) {
            $payload = $row['payload'];
            $productAttributes = is_array($payload['attributes'] ?? null) ? $payload['attributes'] : [];
            if (! ProductAttributeMatcher::matches($productAttributes, $attributeFilters)) {
                continue;
            }

            $priceCents = isset($payload['price']) && is_numeric($payload['price'])
                ? (int) $payload['price']
                : null;

            if ($maxPrice !== null && $priceCents !== null && $priceCents > $maxPrice) {
                continue;
            }

            if ($minPrice !== null && $priceCents !== null && $priceCents < $minPrice) {
                continue;
            }

            $slug = isset($payload['slug']) ? (string) $payload['slug'] : '';
            $imagePath = isset($payload['image']) && is_string($payload['image'])
                ? trim($payload['image'])
                : '';
            $products[] = [
                'product_id' => $row['product_id'],
                'store_id' => $storeId,
                'name' => (string) ($payload['name'] ?? ''),
                'slug' => $slug,
                'price_cents' => $priceCents,
                'score' => $scores[$row['product_id']] ?? null,
                'url' => '/products/'.$row['product_id'],
                'image_url' => $imagePath !== ''
                    ? rtrim((string) config('recommendation.product_images_public_url'), '/').'/'.ltrim($imagePath, '/')
                    : null,
            ];
        }

        usort($products, static function (array $a, array $b): int {
            return ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
        });

        return $products;
    }

    /**
     * @param  list<array<string, mixed>>  $products
     * @param  list<array{role: string, content: string}>  $priorHistory
     */
    private function buildAnswer(string $message, array $products, array $priorHistory): string
    {
        if ($products === []) {
            return $this->prompts->noMatchAnswer();
        }

        $prompt = $this->prompts->buildAnswerPrompt($message, $products, $priorHistory);

        try {
            return trim($this->llm->generate($prompt));
        } catch (Throwable) {
            return $this->prompts->fallbackAnswer($products);
        }
    }
}
