<?php

namespace Tests\Feature;

use App\Models\OutboxMessage;
use App\Models\ProductSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_order_persists_order_and_outbox_in_one_flow(): void
    {
        ProductSnapshot::query()->create([
            'product_id' => 101,
            'store_id' => 1,
            'name' => 'Test chair',
            'sku' => 'CH-001',
            'price_cents' => 2500,
        ]);

        $response = $this->postJson('/orders', [
            'items' => [
                ['product_id' => 101, 'quantity' => 2],
            ],
        ], [
            'X-Auth-User-Id' => '42',
            'X-Auth-User-Email' => 'buyer@example.com',
            'Idempotency-Key' => 'checkout-attempt-1',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'created')
            ->assertJsonPath('data.total_cents', 5000)
            ->assertJsonPath('data.items.0.product_name', 'Test chair');

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('order_idempotency_keys', 1);

        $outbox = OutboxMessage::query()->first();
        $this->assertNotNull($outbox);
        $this->assertSame('order.created', $outbox->topic);
        $this->assertSame('1', $outbox->key);
        $this->assertSame(1, $outbox->payload['order_id']);
        $this->assertSame(101, $outbox->payload['items'][0]['product_id']);
        $this->assertNull($outbox->published_at);
    }

    public function test_idempotency_key_returns_same_order_without_duplicates(): void
    {
        ProductSnapshot::query()->create([
            'product_id' => 5,
            'store_id' => 1,
            'name' => 'Lamp',
            'sku' => null,
            'price_cents' => 1000,
        ]);

        $headers = [
            'X-Auth-User-Id' => '7',
            'X-Auth-User-Email' => 'repeat@example.com',
            'Idempotency-Key' => 'same-key',
        ];

        $first = $this->postJson('/orders', [
            'items' => [['product_id' => 5, 'quantity' => 1]],
        ], $headers);

        $second = $this->postJson('/orders', [
            'items' => [['product_id' => 5, 'quantity' => 1]],
        ], $headers);

        $first->assertCreated();
        $second->assertCreated();
        $this->assertSame($first->json('data.id'), $second->json('data.id'));
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('outbox_messages', 1);
        $this->assertDatabaseCount('order_idempotency_keys', 1);
    }
}
