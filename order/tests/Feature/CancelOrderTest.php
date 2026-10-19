<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OutboxMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_cancel_own_created_order(): void
    {
        $order = $this->seedOrderForUser(7, OrderStatus::Created);

        $this->postJson('/orders/'.$order->id.'/cancel', [], [
            'X-Auth-User-Id' => '7',
            'X-Auth-User-Email' => 'c@example.com',
            'X-Auth-User-Roles' => 'customer',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', OrderStatus::Cancelled->value);

        $this->assertSame('order.cancelled', OutboxMessage::query()->value('topic'));
        $this->assertDatabaseMissing('outbox_messages', ['topic' => 'stock.release_requested']);
    }

    public function test_cancel_after_stock_reserved_requests_stock_release(): void
    {
        $order = $this->seedOrderForUser(7, OrderStatus::StockReserved, withItem: true);

        $this->postJson('/orders/'.$order->id.'/cancel', [], [
            'X-Auth-User-Id' => '7',
            'X-Auth-User-Email' => 'c@example.com',
            'X-Auth-User-Roles' => 'customer',
        ])->assertOk();

        $topics = OutboxMessage::query()->orderBy('id')->pluck('topic')->all();
        $this->assertSame(['order.cancelled', 'stock.release_requested'], $topics);
    }

    public function test_cannot_cancel_another_users_order(): void
    {
        $order = $this->seedOrderForUser(1, OrderStatus::Created);

        $this->postJson('/orders/'.$order->id.'/cancel', [], [
            'X-Auth-User-Id' => '2',
            'X-Auth-User-Email' => 'other@example.com',
            'X-Auth-User-Roles' => 'customer',
        ])->assertForbidden();
    }

    public function test_cannot_cancel_already_cancelled_order(): void
    {
        $order = $this->seedOrderForUser(7, OrderStatus::Cancelled);

        $this->postJson('/orders/'.$order->id.'/cancel', [], [
            'X-Auth-User-Id' => '7',
            'X-Auth-User-Email' => 'c@example.com',
            'X-Auth-User-Roles' => 'customer',
        ])->assertUnprocessable();
    }

    private function seedOrderForUser(int $userId, OrderStatus $status, bool $withItem = false): Order
    {
        $order = Order::query()->create([
            'user_id' => $userId,
            'user_email' => 'u@example.com',
            'status' => $status->value,
            'total_cents' => 500,
            'currency' => 'USD',
        ]);

        if ($withItem) {
            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => 10,
                'store_id' => 1,
                'product_name' => 'Widget',
                'sku' => 'W-1',
                'price_cents' => 500,
                'quantity' => 1,
            ]);
        }

        return $order;
    }
}
