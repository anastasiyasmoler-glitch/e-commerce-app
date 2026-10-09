<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;

interface CatalogReadRepositoryInterface
{
    /**
     * @return Collection<int, Category>
     */
    public function categoriesForStore(int $storeId, ?int $parentId): Collection;

    /**
     * @return Collection<int, Product>
     */
    public function productsForStore(int $storeId, ?int $categoryId, int $page, int $perPage): Collection;

    public function findProductInStore(int $storeId, int $productId): ?Product;
}
