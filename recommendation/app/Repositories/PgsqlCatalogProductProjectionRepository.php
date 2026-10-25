<?php

namespace App\Repositories;

use App\Repositories\Contracts\CatalogProductProjectionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PgsqlCatalogProductProjectionRepository implements CatalogProductProjectionRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function upsertFromCatalogEvent(array $payload): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        $productId = (int) ($payload['id'] ?? 0);
        $storeId = (int) ($payload['store_id'] ?? 0);
        $name = $payload['name'] ?? null;

        if ($productId < 1 || $storeId < 1 || ! is_string($name) || $name === '') {
            return;
        }

        $now = now();

        DB::table('catalog_product_projections')->upsert(
            [
                'product_id' => $productId,
                'store_id' => $storeId,
                'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
                'is_published' => (bool) ($payload['is_published'] ?? false),
                'in_stock' => (bool) ($payload['in_stock'] ?? true),
                'indexed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            ['product_id', 'store_id'],
            ['payload', 'is_published', 'in_stock', 'indexed_at', 'updated_at'],
        );
    }

    public function deleteByProductAndStore(int $productId, int $storeId): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        if ($productId < 1 || $storeId < 1) {
            return;
        }

        DB::table('catalog_product_projections')
            ->where('product_id', $productId)
            ->where('store_id', $storeId)
            ->delete();
    }
}
