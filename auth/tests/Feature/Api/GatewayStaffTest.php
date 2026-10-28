<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatewayStaffTest extends TestCase
{
    use RefreshDatabase;

    public function test_gateway_staff_returns_identity_headers_for_admin(): void
    {
        $user = User::factory()->create(['email' => 'staff-admin@example.com']);
        $user->assignRole('admin');

        $token = (string) $this->postJson('/login', [
            'email' => 'staff-admin@example.com',
            'password' => 'password',
        ])->json('access_token');

        $this->get('/_gateway/staff', [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])
            ->assertNoContent()
            ->assertHeader('X-Auth-User-Id', (string) $user->id)
            ->assertHeader('X-Auth-User-Roles', 'admin');
    }

    public function test_gateway_staff_returns_identity_headers_for_analyst(): void
    {
        $user = User::factory()->create(['email' => 'staff-analyst@example.com']);
        $user->assignRole('analyst');

        $token = (string) $this->postJson('/login', [
            'email' => 'staff-analyst@example.com',
            'password' => 'password',
        ])->json('access_token');

        $this->get('/_gateway/staff', [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])
            ->assertNoContent()
            ->assertHeader('X-Auth-User-Roles', 'analyst');
    }

    public function test_gateway_staff_rejects_customer_role(): void
    {
        $user = User::factory()->create(['email' => 'buyer@example.com']);
        $user->assignRole('customer');

        $token = (string) $this->postJson('/login', [
            'email' => 'buyer@example.com',
            'password' => 'password',
        ])->json('access_token');

        $this->get('/_gateway/staff', [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->assertForbidden();
    }

    public function test_gateway_staff_rejects_missing_bearer_token(): void
    {
        $this->get('/_gateway/staff', ['Accept' => 'application/json'])
            ->assertUnauthorized();
    }
}
