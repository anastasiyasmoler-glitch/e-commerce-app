<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OutboxMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFulfillmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_ship_confirmed_order(): void
    {
        $order = $this->seedOrder(OrderStatus::Confirmed);

        $this->postJson("/orders/{$order->id}/ship", [], [
            'X-Auth-User-Id' => '1',
            'X-Auth-User-Email' => 'admin@example.com',
            'X-Auth-User-Roles' => 'admin',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', OrderStatus::Shipped->value);

        $this->assertSame('order.shipped', OutboxMessage::query()->latest('id')->value('topic'));
    }

    public function test_staff_can_complete_shipped_order(): void
    {
        $order = $this->seedOrder(OrderStatus::Shipped);

        $this->postJson("/orders/{$order->id}/complete", [], [
            'X-Auth-User-Id' => '1',
            'X-Auth-User-Email' => 'admin@example.com',
            'X-Auth-User-Roles' => 'admin',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', OrderStatus::Completed->value);

        $this->assertSame('order.completed', OutboxMessage::query()->latest('id')->value('topic'));
    }

    public function test_customer_cannot_ship_orders(): void
    {
        $order = $this->seedOrder(OrderStatus::Confirmed);

        $this->postJson("/orders/{$order->id}/ship", [], [
            'X-Auth-User-Id' => (string) $order->user_id,
            'X-Auth-User-Email' => $order->user_email,
            'X-Auth-User-Roles' => 'customer',
        ])->assertForbidden();
    }

    private function seedOrder(OrderStatus $status): Order
    {
        return Order::query()->create([
            'user_id' => 10,
            'user_email' => 'buyer@example.com',
            'status' => $status->value,
            'total_cents' => 5000,
            'currency' => 'USD',
        ]);
    }
}
