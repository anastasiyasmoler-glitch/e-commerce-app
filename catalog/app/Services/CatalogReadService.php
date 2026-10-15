<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Repositories\Contracts\CatalogReadRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CatalogReadService
{
    private const TTL_SECONDS = 300;

    public function __construct(
        private readonly CatalogReadRepositoryInterface $catalog,
    ) {}

    /**
     * @return Collection<int, Category>
     */
    public function categories(int $storeId, ?int $parentId): Collection
    {
        $key = $this->categoriesKey($storeId, $parentId);

        return Cache::remember(
            $key,
            self::TTL_SECONDS,
            fn () => $this->catalog->categoriesForStore($storeId, $parentId),
        );
    }

    /**
     * @return Collection<int, Product>
     */
    public function products(int $storeId, ?int $categoryId, int $page, int $perPage): Collection
    {
        $key = $this->productsKey($storeId, $categoryId, $page, $perPage);

        return Cache::remember(
            $key,
            self::TTL_SECONDS,
            fn () => $this->catalog->productsForStore($storeId, $categoryId, $page, $perPage),
        );
    }

    public function product(int $storeId, int $productId): Product
    {
        $key = $this->productKey($storeId, $productId);

        $product = Cache::remember(
            $key,
            self::TTL_SECONDS,
            fn () => $this->catalog->findProductInStore($storeId, $productId),
        );

        if ($product === null) {
            Cache::forget($key);
            abort(404);
        }

        return $product;
    }

    public function invalidateStore(int $storeId): void
    {
        Cache::increment($this->versionKey($storeId));
    }

    public function invalidateProduct(int $storeId, int $productId): void
    {
        Cache::forget($this->productKey($storeId, $productId));
        $this->invalidateStore($storeId);
    }

    private function version(int $storeId): int
    {
        return (int) Cache::get($this->versionKey($storeId), 1);
    }

    private function versionKey(int $storeId): string
    {
        return "catalog:store:{$storeId}:v";
    }

    private function categoriesKey(int $storeId, ?int $parentId): string
    {
        $v = $this->version($storeId);
        $parent = $parentId ?? 'roots';

        return "catalog:store:{$storeId}:v{$v}:categories:{$parent}";
    }

    private function productsKey(int $storeId, ?int $categoryId, int $page, int $perPage): string
    {
        $v = $this->version($storeId);
        $cat = $categoryId ?? 'all';

        return "catalog:store:{$storeId}:v{$v}:products:{$cat}:p{$page}:n{$perPage}";
    }

    private function productKey(int $storeId, int $productId): string
    {
        $v = $this->version($storeId);

        return "catalog:store:{$storeId}:v{$v}:product:{$productId}";
    }
}
