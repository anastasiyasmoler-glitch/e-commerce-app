<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Stub for RECOMMENDATION-1: validates topic and logs until embeddings pipeline lands.
 */
class CatalogIndexIngestService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(string $topic, array $payload): void
    {
        $productId = $payload['id'] ?? $payload['product_id'] ?? null;
        $storeId = $payload['store_id'] ?? null;

        Log::info('catalog_index_event_received', [
            'topic' => $topic,
            'product_id' => $productId,
            'store_id' => $storeId,
        ]);
    }
}
