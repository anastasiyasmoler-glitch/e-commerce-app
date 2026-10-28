<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatewayAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_gateway_admin_returns_identity_headers_for_admin(): void
    {
        $user = User::factory()->create(['email' => 'gate-admin@example.com']);
        $user->assignRole('admin');

        $token = (string) $this->postJson('/login', [
            'email' => 'gate-admin@example.com',
            'password' => 'password',
        ])->json('access_token');

        $this->get('/_gateway/admin', [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])
            ->assertNoContent()
            ->assertHeader('X-Auth-User-Id', (string) $user->id)
            ->assertHeader('X-Auth-User-Roles', 'admin');
    }

    public function test_gateway_admin_rejects_analyst_role(): void
    {
        $user = User::factory()->create(['email' => 'gate-analyst@example.com']);
        $user->assignRole('analyst');

        $token = (string) $this->postJson('/login', [
            'email' => 'gate-analyst@example.com',
            'password' => 'password',
        ])->json('access_token');

        $this->get('/_gateway/admin', [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->assertForbidden();
    }

    public function test_gateway_admin_without_bearer_returns_empty_no_content(): void
    {
        $this->get('/_gateway/admin', ['Accept' => 'application/json'])
            ->assertNoContent()
            ->assertHeaderMissing('X-Auth-User-Id');
    }
}
