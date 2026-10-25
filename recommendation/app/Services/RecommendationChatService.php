<?php

namespace App\Services;

use App\Contracts\LlmClient;
use App\Repositories\Contracts\CatalogProductProjectionRepositoryInterface;
use App\Repositories\Contracts\ProductVectorIndexInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class RecommendationChatService
{
    public function __construct(
        private readonly CatalogProductProjectionRepositoryInterface $projections,
        private readonly ProductVectorIndexInterface $vectors,
        private readonly LlmClient $llm,
        private readonly ChatPromptBuilder $prompts,
        private readonly ChatFilterExtractionService $filterExtraction,
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
        $conversation = $this->resolveConversation($userId, $storeId, $conversationId);

        DB::table('recommendation_messages')->insert([
            'conversation_id' => $conversation['id'],
            'role' => 'user',
            'content' => $message,
            'created_at' => now(),
        ]);

        $filters = $this->filterExtraction->extract($message);
        $filters['store_id'] = $storeId;

        if ($filters['needs_clarification']) {
            $answer = (string) $filters['clarification_question'];

            return $this->finishTurn($conversation['id'], $answer, $filters, []);
        }

        $searchQuery = (string) $filters['search_query'];
        $queryVector = $this->llm->embed($searchQuery);
        $hits = $this->vectors->searchSimilar(
            $storeId,
            $queryVector,
            $limit,
            (bool) $filters['in_stock_only'],
        );
        $productIds = array_values(array_unique(array_column($hits, 'product_id')));
        $rows = $this->projections->findPayloadsByStore($storeId, $productIds);
        $products = $this->formatProducts($storeId, $rows, $hits, $filters);

        $answer = $this->buildAnswer($message, $products);

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
        DB::table('recommendation_messages')->insert([
            'conversation_id' => $conversationId,
            'role' => 'assistant',
            'content' => $answer,
            'created_at' => now(),
        ]);

        $now = now();
        DB::table('recommendation_conversations')
            ->where('id', $conversationId)
            ->update(['last_activity_at' => $now, 'updated_at' => $now]);

        return [
            'conversation_id' => $conversationId,
            'answer' => $answer,
            'extracted_filters' => $filters,
            'products' => $products,
        ];
    }

    /**
     * @return array{id: string, user_id: int, store_id: int}
     */
    private function resolveConversation(int $userId, int $storeId, ?string $conversationId): array
    {
        $idleMinutes = (int) config('recommendation.chat.conversation_idle_minutes', 30);
        $maxHours = (int) config('recommendation.chat.conversation_max_hours', 24);
        $now = now();

        if ($conversationId !== null && $conversationId !== '') {
            $row = DB::table('recommendation_conversations')
                ->where('id', $conversationId)
                ->where('user_id', $userId)
                ->first();

            if ($row === null) {
                abort(404, 'Conversation not found.');
            }

            if ((int) $row->store_id !== $storeId) {
                abort(403, 'Conversation store mismatch.');
            }

            if ($now->greaterThan($row->expires_at)) {
                abort(410, 'Conversation expired.');
            }

            $lastActivity = $row->last_activity_at ?? $row->created_at;
            if ($now->diffInMinutes($lastActivity) > $idleMinutes) {
                abort(410, 'Conversation idle timeout exceeded.');
            }

            return [
                'id' => (string) $row->id,
                'user_id' => (int) $row->user_id,
                'store_id' => (int) $row->store_id,
            ];
        }

        $id = (string) Str::uuid();
        DB::table('recommendation_conversations')->insert([
            'id' => $id,
            'user_id' => $userId,
            'store_id' => $storeId,
            'expires_at' => $now->copy()->addHours($maxHours),
            'last_activity_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [
            'id' => $id,
            'user_id' => $userId,
            'store_id' => $storeId,
        ];
    }

    /**
     * @param  list<array{product_id: int, store_id: int, payload: array<string, mixed>}>  $rows
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

        $products = [];
        foreach ($rows as $row) {
            $payload = $row['payload'];
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
            $products[] = [
                'product_id' => $row['product_id'],
                'store_id' => $storeId,
                'name' => (string) ($payload['name'] ?? ''),
                'slug' => $slug,
                'price_cents' => $priceCents,
                'score' => $scores[$row['product_id']] ?? null,
                'url' => $slug !== '' ? '/products/'.$slug.'?store_id='.$storeId : null,
            ];
        }

        usort($products, static function (array $a, array $b): int {
            return ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
        });

        return $products;
    }

    /**
     * @param  list<array<string, mixed>>  $products
     */
    private function buildAnswer(string $message, array $products): string
    {
        if ($products === []) {
            return $this->prompts->noMatchAnswer();
        }

        $prompt = $this->prompts->buildAnswerPrompt($message, $products);

        try {
            return trim($this->llm->generate($prompt));
        } catch (Throwable) {
            return $this->prompts->fallbackAnswer($products);
        }
    }
}
