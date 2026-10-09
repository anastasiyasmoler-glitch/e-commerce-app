<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Repositories\Contracts\CatalogReadRepositoryInterface;
use Illuminate\Support\Collection;

class CatalogReadService
{
    public function __construct(
        private readonly CatalogReadRepositoryInterface $catalog,
    ) {}

    /**
     * @return Collection<int, Category>
     */
    public function categories(int $storeId, ?int $parentId): Collection
    {
        return $this->catalog->categoriesForStore($storeId, $parentId);
    }

    /**
     * @return Collection<int, Product>
     */
    public function products(int $storeId, ?int $categoryId, int $page, int $perPage): Collection
    {
        return $this->catalog->productsForStore($storeId, $categoryId, $page, $perPage);
    }

    public function product(int $storeId, int $productId): Product
    {
        $product = $this->catalog->findProductInStore($storeId, $productId);

        if ($product === null) {
            abort(404);
        }

        return $product;
    }
}
