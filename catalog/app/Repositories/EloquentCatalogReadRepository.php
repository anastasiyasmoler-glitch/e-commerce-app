<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Product;
use App\Repositories\Contracts\CatalogReadRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentCatalogReadRepository implements CatalogReadRepositoryInterface
{
    public function categoriesForStore(int $storeId, ?int $parentId): Collection
    {
        return Category::query()
            ->where('store_id', $storeId)
            ->when(
                $parentId === null,
                fn ($q) => $q->whereNull('parent_id'),
                fn ($q) => $q->where('parent_id', $parentId),
            )
            ->orderBy('name')
            ->get();
    }

    public function productsForStore(int $storeId, ?int $categoryId, int $page, int $perPage): Collection
    {
        return Product::query()
            ->with('stock')
            ->where('store_id', $storeId)
            ->when($categoryId !== null, fn ($q) => $q->where('category_id', $categoryId))
            ->orderBy('name')
            ->forPage($page, $perPage)
            ->get();
    }

    public function findProductInStore(int $storeId, int $productId): ?Product
    {
        return Product::query()
            ->with('stock')
            ->where('store_id', $storeId)
            ->whereKey($productId)
            ->first();
    }
}
