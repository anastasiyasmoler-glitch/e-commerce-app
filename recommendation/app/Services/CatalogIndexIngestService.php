<?php

namespace App\Services;

use App\Contracts\LlmClient;
use App\Repositories\Contracts\CatalogProductProjectionRepositoryInterface;
use App\Repositories\Contracts\ProductVectorIndexInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

class CatalogIndexIngestService
{
    public function __construct(
        private readonly CatalogProductProjectionRepositoryInterface $projections,
        private readonly ProductVectorIndexInterface $vectors,
        private readonly ProductEmbeddingTextBuilder $embeddingText,
        private readonly LlmClient $llm,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(string $topic, array $payload): void
    {
        match ($topic) {
            'product.created', 'product.updated' => $this->upsertFromCatalogEvent($payload),
            'product.deleted' => $this->deleteFromCatalogEvent($payload),
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function upsertFromCatalogEvent(array $payload): void
    {
        $this->projections->upsertFromCatalogEvent($payload);

        $productId = (int) ($payload['id'] ?? 0);
        $storeId = (int) ($payload['store_id'] ?? 0);
        if ($productId < 1 || $storeId < 1) {
            return;
        }

        try {
            $text = $this->embeddingText->fromCatalogPayload($payload);
            if ($text === '') {
                return;
            }

            $embedding = $this->llm->embed($text);
            $this->vectors->upsertProductVector(
                $productId,
                $storeId,
                $embedding,
                (bool) ($payload['is_published'] ?? false),
                (bool) ($payload['in_stock'] ?? true),
            );
        } catch (Throwable $exception) {
            Log::warning('catalog_vector_index_failed', [
                'product_id' => $productId,
                'store_id' => $storeId,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function deleteFromCatalogEvent(array $payload): void
    {
        $productId = (int) ($payload['id'] ?? 0);
        $storeId = (int) ($payload['store_id'] ?? 0);

        $this->projections->deleteByProductAndStore($productId, $storeId);

        try {
            $this->vectors->deleteProductVector($productId, $storeId);
        } catch (Throwable $exception) {
            Log::warning('catalog_vector_delete_failed', [
                'product_id' => $productId,
                'store_id' => $storeId,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
