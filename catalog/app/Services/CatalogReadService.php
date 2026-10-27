<?php

namespace App\Services;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\Stock;
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

        /** @var list<array<string, mixed>> $rows */
        $rows = Cache::remember(
            $key,
            self::TTL_SECONDS,
            fn () => $this->catalog->categoriesForStore($storeId, $parentId)
                ->map(fn (Category $category) => $category->getAttributes())
                ->values()
                ->all(),
        );

        return Category::hydrate($rows);
    }

    /**
     * @return Collection<int, Product>
     */
    public function products(int $storeId, ?int $categoryId, int $page, int $perPage): Collection
    {
        $key = $this->productsKey($storeId, $categoryId, $page, $perPage);

        /** @var list<array{product: array<string, mixed>, stock: ?array<string, mixed>, attribute_values: list<array{value: array<string, mixed>, attribute: ?array<string, mixed>}>}> $rows */
        $rows = Cache::remember(
            $key,
            self::TTL_SECONDS,
            fn () => $this->catalog->productsForStore($storeId, $categoryId, $page, $perPage)
                ->map(fn (Product $product) => $this->productToCacheRow($product))
                ->values()
                ->all(),
        );

        return $this->productsFromCacheRows($rows);
    }

    public function product(int $storeId, int $productId): Product
    {
        $key = $this->productKey($storeId, $productId);

        /** @var ?array{product: array<string, mixed>, stock: ?array<string, mixed>, attribute_values: list<array{value: array<string, mixed>, attribute: ?array<string, mixed>}>} $row */
        $row = Cache::remember(
            $key,
            self::TTL_SECONDS,
            function () use ($storeId, $productId) {
                $product = $this->catalog->findProductInStore($storeId, $productId);

                return $product !== null ? $this->productToCacheRow($product) : null;
            },
        );

        if ($row === null) {
            Cache::forget($key);
            abort(404);
        }

        return $this->productFromCacheRow($row);
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

    /**
     * @return array{product: array<string, mixed>, stock: ?array<string, mixed>, attribute_values: list<array{value: array<string, mixed>, attribute: ?array<string, mixed>}>}
     */
    private function productToCacheRow(Product $product): array
    {
        return [
            'product' => $product->getAttributes(),
            'stock' => $product->stock?->getAttributes(),
            'attribute_values' => $product->attributeValues
                ->map(fn (ProductAttributeValue $attributeValue) => [
                    'value' => $attributeValue->getAttributes(),
                    'attribute' => $attributeValue->attribute?->getAttributes(),
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @param list<array{product: array<string, mixed>, stock: ?array<string, mixed>, attribute_values: list<array{value: array<string, mixed>, attribute: ?array<string, mixed>}>}> $rows
     * @return Collection<int, Product>
     */
    private function productsFromCacheRows(array $rows): Collection
    {
        return collect($rows)->map(fn (array $row) => $this->productFromCacheRow($row));
    }

    /**
     * @param array{product: array<string, mixed>, stock: ?array<string, mixed>, attribute_values: list<array{value: array<string, mixed>, attribute: ?array<string, mixed>}>} $row
     */
    private function productFromCacheRow(array $row): Product
    {
        $product = (new Product)->newFromBuilder($row['product']);

        if ($row['stock'] !== null) {
            $product->setRelation('stock', (new Stock)->newFromBuilder($row['stock']));
        }

        $attributeValues = collect($row['attribute_values'])->map(function (array $attributeRow) {
            $attributeValue = (new ProductAttributeValue)->newFromBuilder($attributeRow['value']);
            if ($attributeRow['attribute'] !== null) {
                $attributeValue->setRelation(
                    'attribute',
                    (new Attribute)->newFromBuilder($attributeRow['attribute']),
                );
            }

            return $attributeValue;
        });

        $product->setRelation('attributeValues', $attributeValues);

        return $product;
    }
}
