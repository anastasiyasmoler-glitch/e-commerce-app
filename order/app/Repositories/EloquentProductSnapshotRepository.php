<?php

namespace App\Repositories;

use App\Models\ProductSnapshot;
use App\Repositories\Contracts\ProductSnapshotRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentProductSnapshotRepository implements ProductSnapshotRepositoryInterface
{
    /**
     * @param  list<int>  $productIds
     * @return Collection<int, ProductSnapshot>
     */
    public function findMany(array $productIds): Collection
    {
        if ($productIds === []) {
            return new Collection;
        }

        return ProductSnapshot::query()
            ->whereIn('product_id', $productIds)
            ->get()
            ->keyBy('product_id');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function upsertFromCatalogEvent(array $payload): void
    {
        $productId = (int) ($payload['id'] ?? 0);
        $storeId = (int) ($payload['store_id'] ?? 0);
        $name = $payload['name'] ?? null;

        if ($productId < 1 || $storeId < 1 || ! is_string($name) || $name === '') {
            return;
        }

        $price = $payload['price'] ?? 0;
        $priceCents = is_int($price) ? $price : (int) $price;

        ProductSnapshot::query()->updateOrCreate(
            ['product_id' => $productId],
            [
                'store_id' => $storeId,
                'name' => $name,
                'sku' => isset($payload['sku']) ? (string) $payload['sku'] : null,
                'price_cents' => max(0, $priceCents),
            ],
        );
    }
}
