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

    /**
     * @param  list<int>  $productIds
     * @return list<array{product_id: int, store_id: int, payload: array<string, mixed>}>
     */
    public function findPayloadsByStore(int $storeId, array $productIds): array
    {
        if (DB::connection()->getDriverName() !== 'pgsql' || $storeId < 1 || $productIds === []) {
            return [];
        }

        $rows = DB::table('catalog_product_projections')
            ->where('store_id', $storeId)
            ->whereIn('product_id', $productIds)
            ->get(['product_id', 'store_id', 'payload']);

        $result = [];
        foreach ($rows as $row) {
            $payload = json_decode((string) $row->payload, true);
            if (! is_array($payload)) {
                continue;
            }

            $result[] = [
                'product_id' => (int) $row->product_id,
                'store_id' => (int) $row->store_id,
                'payload' => $payload,
            ];
        }

        return $result;
    }
}
