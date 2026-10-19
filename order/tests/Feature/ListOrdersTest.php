<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_sees_only_own_orders(): void
    {
        Order::query()->create([
            'user_id' => 10,
            'user_email' => 'mine@example.com',
            'status' => OrderStatus::Created->value,
            'total_cents' => 100,
            'currency' => 'USD',
        ]);

        Order::query()->create([
            'user_id' => 99,
            'user_email' => 'other@example.com',
            'status' => OrderStatus::Created->value,
            'total_cents' => 200,
            'currency' => 'USD',
        ]);

        $response = $this->getJson('/orders', [
            'X-Auth-User-Id' => '10',
            'X-Auth-User-Email' => 'mine@example.com',
            'X-Auth-User-Roles' => 'customer',
        ]);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.user_id', 10);
    }

    public function test_analyst_sees_all_orders(): void
    {
        Order::query()->create([
            'user_id' => 1,
            'user_email' => 'a@example.com',
            'status' => OrderStatus::Created->value,
            'total_cents' => 100,
            'currency' => 'USD',
        ]);

        Order::query()->create([
            'user_id' => 2,
            'user_email' => 'b@example.com',
            'status' => OrderStatus::Created->value,
            'total_cents' => 200,
            'currency' => 'USD',
        ]);

        $this->getJson('/orders', [
            'X-Auth-User-Id' => '5',
            'X-Auth-User-Email' => 'analyst@example.com',
            'X-Auth-User-Roles' => 'analyst',
        ])
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_show_returns_403_for_another_users_order(): void
    {
        $order = Order::query()->create([
            'user_id' => 3,
            'user_email' => 'owner@example.com',
            'status' => OrderStatus::Created->value,
            'total_cents' => 100,
            'currency' => 'USD',
        ]);

        $this->getJson('/orders/'.$order->id, [
            'X-Auth-User-Id' => '4',
            'X-Auth-User-Email' => 'stranger@example.com',
            'X-Auth-User-Roles' => 'customer',
        ])->assertForbidden();
    }
}
