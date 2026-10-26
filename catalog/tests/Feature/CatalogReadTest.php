<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\Stock;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_list_categories_and_products_for_a_store(): void
    {
        $store = Store::query()->create(['name' => 'Nordic Home', 'slug' => 'nordic-home']);
        $other = Store::query()->create(['name' => 'Other', 'slug' => 'other']);

        $sofas = Category::query()->create([
            'store_id' => $store->id,
            'name' => 'Sofas',
            'slug' => 'sofas',
        ]);
        Category::query()->create([
            'store_id' => $other->id,
            'name' => 'Hidden',
            'slug' => 'hidden',
        ]);

        $product = Product::query()->create([
            'store_id' => $store->id,
            'category_id' => $sofas->id,
            'name' => 'Linen Sofa',
            'slug' => 'linen-sofa',
            'sku' => 'SOFA-1',
            'price' => 45900,
            'is_published' => true,
        ]);
        $color = Attribute::query()->create([
            'store_id' => $store->id,
            'code' => 'color',
            'name' => 'Color',
        ]);
        $material = Attribute::query()->create([
            'store_id' => $store->id,
            'code' => 'material',
            'name' => 'Material',
        ]);
        ProductAttributeValue::query()->create([
            'product_id' => $product->id,
            'attribute_id' => $color->id,
            'value' => 'beige',
        ]);
        ProductAttributeValue::query()->create([
            'product_id' => $product->id,
            'attribute_id' => $material->id,
            'value' => 'linen',
        ]);
        Stock::query()->create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'quantity' => 4,
            'reserved' => 1,
        ]);

        $this->getJson('/categories?store_id='.$store->id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'sofas');

        $this->getJson('/products?store_id='.$store->id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.stock.quantity', 4)
            ->assertJsonPath('data.0.price', 45900)
            ->assertJsonPath('data.0.attributes.color', 'beige');

        $this->getJson('/products/'.$product->id.'?store_id='.$store->id)
            ->assertOk()
            ->assertJsonPath('data.name', 'Linen Sofa')
            ->assertJsonPath('data.price', 45900)
            ->assertJsonPath('data.attributes.material', 'linen');

        $this->getJson('/products/'.$product->id.'?store_id='.$other->id)
            ->assertNotFound();
    }

    public function test_unpublished_products_are_hidden_from_public_api(): void
    {
        $store = Store::query()->create(['name' => 'Nordic Home', 'slug' => 'nordic-home']);
        $sofas = Category::query()->create([
            'store_id' => $store->id,
            'name' => 'Sofas',
            'slug' => 'sofas',
        ]);

        $draft = Product::query()->create([
            'store_id' => $store->id,
            'category_id' => $sofas->id,
            'name' => 'Draft Sofa',
            'slug' => 'draft-sofa',
            'price' => 1000,
            'is_published' => false,
        ]);

        $this->getJson('/products?store_id='.$store->id)
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->getJson('/products/'.$draft->id.'?store_id='.$store->id)
            ->assertNotFound();
    }

    public function test_stock_change_invalidates_cached_product_and_listing(): void
    {
        $store = Store::query()->create(['name' => 'Nordic Home', 'slug' => 'nordic-home']);
        $sofas = Category::query()->create([
            'store_id' => $store->id,
            'name' => 'Sofas',
            'slug' => 'sofas',
        ]);
        $product = Product::query()->create([
            'store_id' => $store->id,
            'category_id' => $sofas->id,
            'name' => 'Linen Sofa',
            'slug' => 'linen-sofa',
            'sku' => 'SOFA-1',
            'price' => 45900,
            'is_published' => true,
        ]);
        $stock = Stock::query()->create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'quantity' => 4,
            'reserved' => 0,
        ]);

        $this->getJson('/products/'.$product->id.'?store_id='.$store->id)
            ->assertOk()
            ->assertJsonPath('data.stock.quantity', 4);

        $stock->update(['quantity' => 9]);

        $this->getJson('/products/'.$product->id.'?store_id='.$store->id)
            ->assertOk()
            ->assertJsonPath('data.stock.quantity', 9);
    }
}
