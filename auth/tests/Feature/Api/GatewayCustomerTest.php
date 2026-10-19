<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatewayCustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_gateway_customer_returns_identity_headers_for_valid_access_token(): void
    {
        $user = User::factory()->create(['email' => 'buyer@example.com']);
        $user->assignRole('customer');

        $login = $this->postJson('/login', [
            'email' => 'buyer@example.com',
            'password' => 'password',
        ]);

        $token = (string) $login->json('access_token');

        $response = $this->get('/_gateway/customer', [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ]);

        $response->assertNoContent()
            ->assertHeader('X-Auth-User-Id', (string) $user->id)
            ->assertHeader('X-Auth-User-Email', 'buyer@example.com')
            ->assertHeader('X-Auth-User-Name', $user->name);
    }

    public function test_gateway_customer_rejects_missing_bearer_token(): void
    {
        $this->get('/_gateway/customer', ['Accept' => 'application/json'])
            ->assertUnauthorized();
    }
}
