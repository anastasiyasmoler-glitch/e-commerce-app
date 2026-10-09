<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
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
            ->assertJsonPath('data.0.stock.quantity', 4);

        $this->getJson('/products/'.$product->id.'?store_id='.$store->id)
            ->assertOk()
            ->assertJsonPath('data.name', 'Linen Sofa');

        $this->getJson('/products/'.$product->id.'?store_id='.$other->id)
            ->assertNotFound();
    }
}
