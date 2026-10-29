<?php

namespace Tests\Integration;

use App\Models\OutboxMessage;
use App\Models\ProductSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('integration')]
class OrderPostgresIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (env('RUN_ORDER_INTEGRATION') !== '1') {
            $this->markTestSkipped('Set RUN_ORDER_INTEGRATION=1 with PostgreSQL (see order README).');
        }

        if (config('database.default') !== 'pgsql') {
            $this->markTestSkipped('Integration tests require DB_CONNECTION=pgsql.');
        }

        parent::setUp();
    }

    public function test_create_order_persists_relational_rows_and_outbox_on_postgres(): void
    {
        ProductSnapshot::query()->create([
            'product_id' => 501,
            'store_id' => 1,
            'name' => 'Integration chair',
            'sku' => 'INT-501',
            'price_cents' => 9900,
        ]);

        $response = $this->postJson('/orders', [
            'items' => [
                ['product_id' => 501, 'quantity' => 1],
            ],
        ], [
            'X-Auth-User-Id' => '9001',
            'X-Auth-User-Email' => 'integration@example.com',
            'X-Auth-User-Roles' => 'customer',
            'Idempotency-Key' => 'integration-'.uniqid(),
        ]);

        $response->assertCreated();
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseHas('outbox_messages', [
            'topic' => 'order.created',
        ]);

        $outbox = OutboxMessage::query()->first();
        $this->assertNotNull($outbox);
        $this->assertNull($outbox->published_at);
    }
}
