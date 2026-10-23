<?php

namespace Tests\Feature;

use App\Contracts\Repositories\EventMetricsRepositoryInterface;
use Mockery\MockInterface;
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

    public function test_analyst_can_access_user_metrics(): void
    {
        $this->mock(EventMetricsRepositoryInterface::class, function (MockInterface $mock): void {
            $mock->shouldReceive('userRegistrations')->once()->andReturn([
                'total' => 2,
                'by_day' => [
                    ['date' => '2026-10-01', 'count' => 2],
                ],
            ]);
        });

        $this->getJson('/api/users', $this->analystHeaders())
            ->assertOk()
            ->assertJsonPath('data.total', 2)
            ->assertJsonPath('data.by_day.0.count', 2)
            ->assertJsonStructure(['meta' => ['from', 'to']]);
    }

    public function test_admin_can_access_order_metrics(): void
    {
        $this->mock(EventMetricsRepositoryInterface::class, function (MockInterface $mock): void {
            $mock->shouldReceive('orderFunnel')->once()->andReturn([
                'created' => 10,
                'paid' => 4,
                'shipped' => 3,
                'completed' => 2,
                'cancelled' => 1,
                'conversion_rate' => 0.4,
                'completion_rate' => 0.2,
                'revenue_cents' => 12000,
                'average_order_value_cents' => 3000,
                'currency' => 'USD',
                'revenue_by_day' => [],
            ]);
        });

        $this->getJson('/api/orders', $this->adminHeaders())
            ->assertOk()
            ->assertJsonPath('data.paid', 4)
            ->assertJsonPath('data.conversion_rate', 0.4)
            ->assertJsonPath('data.revenue_cents', 12000)
            ->assertJsonPath('data.average_order_value_cents', 3000);
    }

    /**
     * @return array<string, string>
     */
    private function analystHeaders(): array
    {
        return [
            'X-Auth-User-Id' => '5',
            'X-Auth-User-Email' => 'analyst@example.com',
            'X-Auth-User-Roles' => 'analyst',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function adminHeaders(): array
    {
        return [
            'X-Auth-User-Id' => '2',
            'X-Auth-User-Email' => 'admin@example.com',
            'X-Auth-User-Roles' => 'admin',
        ];
    }
}
