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
}
