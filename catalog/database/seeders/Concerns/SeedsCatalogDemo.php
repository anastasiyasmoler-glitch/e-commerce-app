<?php

namespace Database\Seeders\Concerns;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\Stock;
use App\Models\Store;
use Database\Seeders\Data\ProductImageMap;
use Illuminate\Support\Facades\Storage;

trait SeedsCatalogDemo
{
    /** @var list<int> */
    private const PRICE_TIERS_CENTS = [
        1999, 3499, 4999, 7900, 12900, 18900, 24900, 39900, 54900, 89900,
        129900, 179900, 249900, 399900, 599900, 999900, 1499900,
    ];

    protected function wipeSeedProductImages(): void
    {
        if (config('filesystems.default') !== 's3') {
            return;
        }

        Storage::disk('s3')->deleteDirectory('products');
    }

    protected function category(Store $store, string $name, string $slug, ?Category $parent = null): Category
    {
        return Category::query()->create([
            'store_id' => $store->id,
            'parent_id' => $parent?->id,
            'name' => $name,
            'slug' => $slug,
        ]);
    }

    /**
     * @param  array<int, array{cat: string, name: string, slug: string, sku: string, desc: string, qty: int, res: int, price?: int, attributes?: array<string, string>}>  $items
     */
    protected function seedStoreProducts(Store $store, array $items): void
    {
        $categories = $store->categories()->get()->keyBy('slug');

        foreach ($items as $item) {
            /** @var Category $category */
            $category = $categories->get($item['cat']);

            $product = Product::query()->create([
                'store_id' => $store->id,
                'category_id' => $category->id,
                'name' => $item['name'],
                'slug' => $item['slug'],
                'description' => $item['desc'],
                'sku' => $item['sku'],
                'price' => $this->priceCentsForSeedItem($item),
                'is_published' => true,
            ]);

            $this->seedProductImage($product);
            $attributeMap = $item['attributes'] ?? $this->defaultAttributesForCategory($item['cat']);
            $this->syncProductAttributes($store, $product, $attributeMap);

            Stock::query()->create([
                'product_id' => $product->id,
                'store_id' => $store->id,
                'quantity' => $item['qty'],
                'reserved' => $item['res'],
            ]);
        }
    }

    /**
     * @param  array<string, string>  $attributeMap
     */
    private function syncProductAttributes(Store $store, Product $product, array $attributeMap): void
    {
        foreach ($attributeMap as $code => $value) {
            $attribute = Attribute::query()->firstOrCreate(
                [
                    'store_id' => $store->id,
                    'code' => $code,
                ],
                [
                    'name' => ucfirst(str_replace('_', ' ', $code)),
                ],
            );

            ProductAttributeValue::query()->create([
                'product_id' => $product->id,
                'attribute_id' => $attribute->id,
                'value' => $value,
            ]);
        }
    }

    /**
     * @param  array{sku: string, price?: int}  $item
     */
    private function priceCentsForSeedItem(array $item): int
    {
        if (isset($item['price'])) {
            return (int) $item['price'];
        }

        $index = abs(crc32((string) $item['sku'])) % count(self::PRICE_TIERS_CENTS);

        return self::PRICE_TIERS_CENTS[$index];
    }

    /**
     * @return array<string, string>
     */
    private function defaultAttributesForCategory(string $categorySlug): array
    {
        $palette = ['natural', 'beige', 'grey', 'black', 'white', 'oak', 'walnut', 'sage', 'terracotta'];

        return match (true) {
            str_contains($categorySlug, 'sofa'), str_contains($categorySlug, 'bed') => [
                'color' => $palette[abs(crc32($categorySlug)) % count($palette)],
                'material' => 'fabric',
                'width_cm' => '200',
            ],
            str_contains($categorySlug, 'lamp'), str_contains($categorySlug, 'chandelier'), str_contains($categorySlug, 'light') => [
                'color' => 'brass',
                'bulb_type' => 'E27',
                'wattage' => '40',
            ],
            str_contains($categorySlug, 'bedding'), str_contains($categorySlug, 'blanket'), str_contains($categorySlug, 'curtain'), str_contains($categorySlug, 'towel') => [
                'color' => 'cream',
                'material' => 'cotton',
                'size' => 'king',
            ],
            default => [
                'color' => 'natural',
                'material' => 'wood',
                'width_cm' => '120',
            ],
        };
    }

    private function seedProductImage(Product $product): void
    {
        if (config('filesystems.default') !== 's3') {
            return;
        }

        $map = ProductImageMap::all();
        $image = $map[$product->slug] ?? 'sofa-modern.jpg';

        $product->forceFill(['image' => $image])->save();
    }
}
