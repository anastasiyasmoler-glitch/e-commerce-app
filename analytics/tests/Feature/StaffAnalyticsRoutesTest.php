<?php

namespace Tests\Feature;

use Tests\TestCase;

class StaffAnalyticsRoutesTest extends TestCase
{
    public function test_report_routes_require_gateway_identity_headers(): void
    {
        $this->getJson('/api/users')->assertUnauthorized();
        $this->getJson('/api/orders')->assertUnauthorized();
        $this->getJson('/api/search')->assertUnauthorized();
    }

    public function test_customer_cannot_access_report_routes(): void
    {
        $headers = [
            'X-Auth-User-Id' => '1',
            'X-Auth-User-Email' => 'buyer@example.com',
            'X-Auth-User-Roles' => 'customer',
        ];

        $this->getJson('/api/users', $headers)->assertForbidden();
        $this->getJson('/api/orders', $headers)->assertForbidden();
        $this->getJson('/api/search', $headers)->assertForbidden();
    }

    public function test_analyst_can_access_user_metrics_stub(): void
    {
        $this->getJson('/api/users', [
            'X-Auth-User-Id' => '5',
            'X-Auth-User-Email' => 'analyst@example.com',
            'X-Auth-User-Roles' => 'analyst',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'User metrics are not implemented yet.');
    }

    public function test_admin_can_access_order_metrics_stub(): void
    {
        $this->getJson('/api/orders', [
            'X-Auth-User-Id' => '2',
            'X-Auth-User-Email' => 'admin@example.com',
            'X-Auth-User-Roles' => 'admin',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Order metrics are not implemented yet.');
    }
}
