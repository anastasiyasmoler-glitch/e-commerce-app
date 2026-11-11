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
            $attributeMap = $item['attributes'] ?? $this->defaultAttributesForCategory($item['cat'], $item['slug']);
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
    private function defaultAttributesForCategory(string $categorySlug, string $productSlug): array
    {
        $palette = ['natural', 'beige', 'grey', 'black', 'white', 'oak', 'walnut', 'sage', 'terracotta'];

        $color = $palette[abs(crc32($productSlug)) % count($palette)];

        return match (true) {
            str_contains($categorySlug, 'bed') && ! str_contains($categorySlug, 'bedding') => [
                'color' => $color,
                'material' => 'fabric',
                'size' => $this->bedSizeFromSlug($productSlug),
                'width_cm' => $this->bedWidthFromSlug($productSlug),
            ],
            str_contains($categorySlug, 'sofa') => [
                'color' => $color,
                'material' => 'fabric',
                'size' => str_contains($productSlug, 'two-seat') ? '2-seat' : (str_contains($productSlug, 'corner') ? 'corner' : '3-seat'),
                'width_cm' => str_contains($productSlug, 'two-seat') ? '160' : (str_contains($productSlug, 'corner') ? '280' : '220'),
            ],
            str_contains($categorySlug, 'lamp'), str_contains($categorySlug, 'chandelier'), str_contains($categorySlug, 'light') => [
                'color' => 'brass',
                'material' => 'metal',
                'bulb_type' => 'E27',
                'size' => str_contains($categorySlug, 'floor') ? 'floor' : (str_contains($categorySlug, 'table') ? 'table' : 'ceiling'),
            ],
            str_contains($categorySlug, 'bedding'), str_contains($categorySlug, 'blanket'), str_contains($categorySlug, 'curtain'), str_contains($categorySlug, 'towel') => [
                'color' => $color,
                'material' => 'cotton',
                'size' => $this->textileSizeFromSlug($productSlug),
            ],
            default => [
                'color' => $color,
                'material' => 'wood',
                'width_cm' => '120',
            ],
        };
    }

    private function bedSizeFromSlug(string $slug): string
    {
        if (str_contains($slug, '140')) {
            return '140×200';
        }
        if (str_contains($slug, '180')) {
            return '180×200';
        }

        return '160×200';
    }

    private function bedWidthFromSlug(string $slug): string
    {
        if (str_contains($slug, '140')) {
            return '140';
        }
        if (str_contains($slug, '180')) {
            return '180';
        }

        return '160';
    }

    private function textileSizeFromSlug(string $slug): string
    {
        if (str_contains($slug, 'single')) {
            return 'single';
        }
        if (str_contains($slug, 'double')) {
            return 'double';
        }
        if (str_contains($slug, 'king')) {
            return 'king';
        }

        return 'one size';
    }

    protected function refreshDemoProductImages(): void
    {
        Product::query()->each(fn (Product $product) => $this->seedProductImage($product));
    }

    private function seedProductImage(Product $product): void
    {
        if (config('filesystems.default') !== 's3') {
            return;
        }

        $source = ProductImageMap::pathForSlug($product->slug);
        if ($source === null) {
            return;
        }

        $filename = $product->slug.'.jpg';
        Storage::disk('s3')->put($filename, (string) file_get_contents($source));
        $product->forceFill(['image' => $filename])->save();
    }
}
