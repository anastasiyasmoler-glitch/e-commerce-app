<?php

namespace App\Infrastructure;

use App\Repositories\Contracts\ProductVectorIndexInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class QdrantProductVectorRepository implements ProductVectorIndexInterface
{
    public function upsertProductVector(
        int $productId,
        int $storeId,
        array $embedding,
        bool $isPublished,
        bool $inStock,
    ): void {
        if ($productId < 1 || $storeId < 1 || $embedding === []) {
            return;
        }

        $this->ensureCollection();

        $response = Http::timeout(15)
            ->acceptJson()
            ->put($this->collectionUrl('/points'), [
                'points' => [
                    [
                        'id' => $this->pointId($productId, $storeId),
                        'vector' => $embedding,
                        'payload' => [
                            'product_id' => $productId,
                            'store_id' => $storeId,
                            'is_published' => $isPublished,
                            'in_stock' => $inStock,
                        ],
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Qdrant upsert failed: '.$response->body());
        }
    }

    public function updateProductVectorMetadata(
        int $productId,
        int $storeId,
        bool $isPublished,
        bool $inStock,
    ): void {
        if ($productId < 1 || $storeId < 1) {
            return;
        }

        $response = Http::timeout(15)
            ->acceptJson()
            ->post($this->collectionUrl('/points/payload'), [
                'payload' => [
                    'product_id' => $productId,
                    'store_id' => $storeId,
                    'is_published' => $isPublished,
                    'in_stock' => $inStock,
                ],
                'points' => [$this->pointId($productId, $storeId)],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Qdrant payload update failed: '.$response->body());
        }
    }

    public function deleteProductVector(int $productId, int $storeId): void
    {
        if ($productId < 1 || $storeId < 1) {
            return;
        }

        $response = Http::timeout(15)
            ->acceptJson()
            ->post($this->collectionUrl('/points/delete'), [
                'points' => [$this->pointId($productId, $storeId)],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Qdrant delete failed: '.$response->body());
        }
    }

    public function searchSimilar(
        int $storeId,
        array $queryVector,
        int $limit,
        bool $inStockOnly = false,
    ): array {
        if ($storeId < 1 || $queryVector === [] || $limit < 1) {
            return [];
        }

        $must = [
            [
                'key' => 'store_id',
                'match' => ['value' => $storeId],
            ],
            [
                'key' => 'is_published',
                'match' => ['value' => true],
            ],
        ];

        if ($inStockOnly) {
            $must[] = [
                'key' => 'in_stock',
                'match' => ['value' => true],
            ];
        }

        $response = Http::timeout(15)
            ->acceptJson()
            ->post($this->collectionUrl('/points/search'), [
                'vector' => $queryVector,
                'limit' => $limit,
                'with_payload' => true,
                'filter' => [
                    'must' => $must,
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Qdrant search failed: '.$response->body());
        }

        $decoded = $response->json();
        if (! is_array($decoded)) {
            return [];
        }

        $threshold = (float) config('recommendation.chat.similarity_threshold', 0.55);
        $hits = [];

        foreach ($decoded['result'] ?? [] as $point) {
            if (! is_array($point)) {
                continue;
            }

            $score = (float) ($point['score'] ?? 0);
            if ($score < $threshold) {
                continue;
            }

            $payload = $point['payload'] ?? [];
            if (! is_array($payload)) {
                continue;
            }

            $productId = (int) ($payload['product_id'] ?? 0);
            $pointStoreId = (int) ($payload['store_id'] ?? 0);
            if ($productId < 1 || $pointStoreId !== $storeId) {
                continue;
            }

            $hits[] = [
                'product_id' => $productId,
                'store_id' => $pointStoreId,
                'score' => $score,
            ];
        }

        return $hits;
    }

    public function ping(): bool
    {
        $response = Http::timeout(5)->get($this->baseUrl().'/healthz');

        return $response->successful();
    }

    private function ensureCollection(): void
    {
        $name = (string) config('recommendation.qdrant.collection');
        $size = (int) config('recommendation.embedding_dimensions');

        $response = Http::timeout(15)
            ->acceptJson()
            ->put($this->baseUrl().'/collections/'.$name, [
                'vectors' => [
                    'size' => $size,
                    'distance' => 'Cosine',
                ],
            ]);

        if ($response->successful() || $response->status() === 409) {
            return;
        }

        throw new RuntimeException('Qdrant create collection failed: '.$response->body());
    }

    private function pointId(int $productId, int $storeId): int
    {
        return crc32($storeId.':'.$productId) & 0x7FFFFFFF;
    }

    private function collectionUrl(string $suffix): string
    {
        $name = (string) config('recommendation.qdrant.collection');

        return $this->baseUrl().'/collections/'.$name.$suffix;
    }

    private function baseUrl(): string
    {
        $host = (string) config('recommendation.qdrant.host');
        $port = (int) config('recommendation.qdrant.port');
        $scheme = config('recommendation.qdrant.use_https') ? 'https' : 'http';

        return sprintf('%s://%s:%d', $scheme, $host, $port);
    }
}
