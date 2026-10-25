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
