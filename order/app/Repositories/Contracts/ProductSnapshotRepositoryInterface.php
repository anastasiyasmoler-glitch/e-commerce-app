<?php

namespace App\Repositories\Contracts;

use App\Models\ProductSnapshot;
use Illuminate\Support\Collection;

interface ProductSnapshotRepositoryInterface
{
    /**
     * @param  list<int>  $productIds
     * @return Collection<int, ProductSnapshot>
     */
    public function findMany(array $productIds): Collection;
}
