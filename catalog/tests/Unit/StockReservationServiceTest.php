<?php

namespace Tests\Unit;

use App\Kafka\ArrayKafkaPublisher;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Store;
use App\Services\StockReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockReservationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_reserve_succeeds_and_release_restores_reserved(): void
    {
        $kafka = new ArrayKafkaPublisher;
        $this->app->instance(ArrayKafkaPublisher::class, $kafka);
        $this->app->instance(\App\Contracts\KafkaPublisherInterface::class, $kafka);

        $stock = $this->seedStock(5, 0);
        $service = $this->app->make(StockReservationService::class);

        $payload = [
            'order_id' => 10,
            'items' => [[
                'product_id' => $stock->product_id,
                'store_id' => $stock->store_id,
                'quantity' => 2,
            ]],
        ];

        $service->reserve($payload);

        $this->assertSame(2, $stock->fresh()->reserved);
        $this->assertSame('stock.reserved', $kafka->messages[array_key_last($kafka->messages)]['topic']);

        $service->release($payload);
        $this->assertSame(0, $stock->fresh()->reserved);
    }

    public function test_reserve_fails_when_not_enough_stock(): void
    {
        $kafka = new ArrayKafkaPublisher;
        $this->app->instance(\App\Contracts\KafkaPublisherInterface::class, $kafka);

        $stock = $this->seedStock(1, 0);
        $service = $this->app->make(StockReservationService::class);

        $service->reserve([
            'order_id' => 11,
            'items' => [[
                'product_id' => $stock->product_id,
                'store_id' => $stock->store_id,
                'quantity' => 5,
            ]],
        ]);

        $this->assertSame(0, $stock->fresh()->reserved);
        $this->assertSame('stock.reservation_failed', $kafka->messages[array_key_last($kafka->messages)]['topic']);
    }

    private function seedStock(int $quantity, int $reserved): Stock
    {
        $store = Store::query()->create(['name' => 'S', 'slug' => 's-'.uniqid()]);
        $category = Category::query()->create([
            'store_id' => $store->id,
            'name' => 'C',
            'slug' => 'c',
        ]);
        $product = Product::query()->create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'P',
            'slug' => 'p',
        ]);

        return Stock::query()->create([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'quantity' => $quantity,
            'reserved' => $reserved,
        ]);
    }
}
