<?php

namespace Tests\Unit;

use App\Models\OutboxMessage;
use App\Models\ProductSnapshot;
use App\Services\CreateOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_outbox_row_is_created_inside_the_same_database_transaction(): void
    {
        ProductSnapshot::query()->create([
            'product_id' => 88,
            'store_id' => 2,
            'name' => 'Shelf',
            'sku' => 'SH-1',
            'price_cents' => 300,
        ]);

        $service = $this->app->make(CreateOrderService::class);

        $service->create(
            1,
            'user@example.com',
            'unit-tx-key',
            [['product_id' => 88, 'quantity' => 3]],
        );

        $this->assertDatabaseHas('orders', ['status' => 'created', 'total_cents' => 900]);
        $this->assertDatabaseHas('outbox_messages', [
            'topic' => 'order.created',
        ]);
        $this->assertSame(1, OutboxMessage::query()->count());
    }
}
