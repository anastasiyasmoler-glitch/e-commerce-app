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
            'product.published' => $this->handlePublished($payload),
            'product.unpublished' => $this->handleUnpublished($payload),
            'product.deleted' => $this->deleteFromCatalogEvent($payload),
            'stock.changed' => $this->handleStockChanged($payload),
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function handlePublished(array $payload): void
    {
        $name = $payload['name'] ?? null;
        if (is_string($name) && $name !== '') {
            $this->upsertFromCatalogEvent($payload);

            return;
        }

        $this->syncPublicationOnly($payload, true);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function handleUnpublished(array $payload): void
    {
        $this->syncPublicationOnly($payload, false);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function syncPublicationOnly(array $payload, bool $isPublished): void
    {
        $productId = (int) ($payload['id'] ?? 0);
        $storeId = (int) ($payload['store_id'] ?? 0);
        if ($productId < 1 || $storeId < 1) {
            return;
        }

        $flags = $this->projections->syncPublicationFlag($productId, $storeId, $isPublished);
        if ($flags === null) {
            return;
        }

        $this->syncVectorMetadata($productId, $storeId, $flags);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function handleStockChanged(array $payload): void
    {
        $productId = (int) ($payload['product_id'] ?? 0);
        $storeId = (int) ($payload['store_id'] ?? 0);
        if ($productId < 1 || $storeId < 1) {
            return;
        }

        $quantity = (int) ($payload['quantity'] ?? 0);
        $reserved = (int) ($payload['reserved'] ?? 0);
        $inStock = max(0, $quantity - $reserved) > 0;

        $flags = $this->projections->syncStockAvailability($productId, $storeId, $inStock);
        if ($flags === null) {
            return;
        }

        $this->syncVectorMetadata($productId, $storeId, $flags);
    }

    /**
     * @param  array{is_published: bool, in_stock: bool}  $flags
     */
    private function syncVectorMetadata(int $productId, int $storeId, array $flags): void
    {
        try {
            $this->vectors->updateProductVectorMetadata(
                $productId,
                $storeId,
                $flags['is_published'],
                $flags['in_stock'],
            );
        } catch (Throwable $exception) {
            Log::warning('catalog_vector_metadata_failed', [
                'product_id' => $productId,
                'store_id' => $storeId,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Rebuild Qdrant vectors from rows already stored in the local catalog projection.
     *
     * @return array{processed: int, vectors_indexed: int, vector_failures: int}
     */
    public function reindexFromProjections(?int $storeId = null): array
    {
        $processed = 0;
        $vectorsIndexed = 0;
        $vectorFailures = 0;

        foreach ($this->projections->allForReindex($storeId) as $row) {
            $processed++;
            $payload = $row['payload'];
            $payload['id'] = $row['product_id'];
            $payload['store_id'] = $row['store_id'];
            $payload['is_published'] = $row['is_published'];
            $payload['in_stock'] = $row['in_stock'];

            if ($this->upsertFromCatalogEvent($payload)) {
                $vectorsIndexed++;
            } else {
                $vectorFailures++;
            }
        }

        return [
            'processed' => $processed,
            'vectors_indexed' => $vectorsIndexed,
            'vector_failures' => $vectorFailures,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function upsertFromCatalogEvent(array $payload): bool
    {
        $this->projections->upsertFromCatalogEvent($payload);

        $productId = (int) ($payload['id'] ?? 0);
        $storeId = (int) ($payload['store_id'] ?? 0);
        if ($productId < 1 || $storeId < 1) {
            return false;
        }

        try {
            $text = $this->embeddingText->fromCatalogPayload($payload);
            if ($text === '') {
                return false;
            }

            $embedding = $this->llm->embed($text);
            $this->vectors->upsertProductVector(
                $productId,
                $storeId,
                $embedding,
                (bool) ($payload['is_published'] ?? false),
                (bool) ($payload['in_stock'] ?? true),
            );

            return true;
        } catch (Throwable $exception) {
            Log::warning('catalog_vector_index_failed', [
                'product_id' => $productId,
                'store_id' => $storeId,
                'error' => $exception->getMessage(),
            ]);

            return false;
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
