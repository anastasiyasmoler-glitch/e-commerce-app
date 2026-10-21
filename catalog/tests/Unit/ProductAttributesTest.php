<?php

namespace Tests\Unit;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAttributesTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_attributes_map_is_built_from_relational_rows(): void
    {
        $store = Store::query()->create(['name' => 'Nordic Home', 'slug' => 'nordic-home']);
        $category = Category::query()->create([
            'store_id' => $store->id,
            'name' => 'Sofas',
            'slug' => 'sofas',
        ]);

        $product = Product::query()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Linen Sofa',
            'slug' => 'linen-sofa',
            'price' => 1000,
        ]);

        $color = Attribute::query()->create([
            'store_id' => $store->id,
            'code' => 'color',
            'name' => 'Color',
        ]);

        ProductAttributeValue::query()->create([
            'product_id' => $product->id,
            'attribute_id' => $color->id,
            'value' => 'beige',
        ]);

        $fresh = $product->fresh();
        $this->assertSame(['color' => 'beige'], $fresh->attributesMap());
        $this->assertSame('beige', $fresh->toEventPayload()['attributes']['color']);
    }
}
