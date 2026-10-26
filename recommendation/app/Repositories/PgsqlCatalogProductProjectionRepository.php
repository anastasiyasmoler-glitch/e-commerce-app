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

    public function syncStockAvailability(int $productId, int $storeId, bool $inStock): ?array
    {
        if (DB::connection()->getDriverName() !== 'pgsql' || $productId < 1 || $storeId < 1) {
            return null;
        }

        $row = DB::table('catalog_product_projections')
            ->where('product_id', $productId)
            ->where('store_id', $storeId)
            ->first();

        if ($row === null) {
            return null;
        }

        $payload = json_decode((string) $row->payload, true);
        if (! is_array($payload)) {
            $payload = [];
        }

        $payload['in_stock'] = $inStock;
        $now = now();

        DB::table('catalog_product_projections')
            ->where('product_id', $productId)
            ->where('store_id', $storeId)
            ->update([
                'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
                'in_stock' => $inStock,
                'updated_at' => $now,
            ]);

        return [
            'is_published' => (bool) $row->is_published,
            'in_stock' => $inStock,
        ];
    }

    public function syncPublicationFlag(int $productId, int $storeId, bool $isPublished): ?array
    {
        if (DB::connection()->getDriverName() !== 'pgsql' || $productId < 1 || $storeId < 1) {
            return null;
        }

        $row = DB::table('catalog_product_projections')
            ->where('product_id', $productId)
            ->where('store_id', $storeId)
            ->first();

        if ($row === null) {
            return null;
        }

        $payload = json_decode((string) $row->payload, true);
        if (! is_array($payload)) {
            $payload = [];
        }

        $payload['is_published'] = $isPublished;
        $inStock = (bool) $row->in_stock;
        $now = now();

        DB::table('catalog_product_projections')
            ->where('product_id', $productId)
            ->where('store_id', $storeId)
            ->update([
                'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
                'is_published' => $isPublished,
                'updated_at' => $now,
            ]);

        return [
            'is_published' => $isPublished,
            'in_stock' => $inStock,
        ];
    }

    /**
     * @param  list<int>  $productIds
     * @return list<array{
     *     product_id: int,
     *     store_id: int,
     *     payload: array<string, mixed>,
     *     is_published: bool,
     *     in_stock: bool
     * }>
     */
    public function findPayloadsByStore(int $storeId, array $productIds): array
    {
        if (DB::connection()->getDriverName() !== 'pgsql' || $storeId < 1 || $productIds === []) {
            return [];
        }

        $rows = DB::table('catalog_product_projections')
            ->where('store_id', $storeId)
            ->whereIn('product_id', $productIds)
            ->get(['product_id', 'store_id', 'payload', 'is_published', 'in_stock']);

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
                'is_published' => (bool) $row->is_published,
                'in_stock' => (bool) $row->in_stock,
            ];
        }

        return $result;
    }
}
