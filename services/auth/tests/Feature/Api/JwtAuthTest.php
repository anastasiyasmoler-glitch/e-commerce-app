<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JwtAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_returns_tokens_and_assigns_customer(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Api User',
            'email' => 'api@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['access_token', 'refresh_token', 'token_type', 'expires_in']);

        $user = User::query()->where('email', 'api@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('customer'));
    }

    public function test_login_and_me_return_user_with_roles(): void
    {
        $user = User::factory()->create(['email' => 'jwt@example.com']);
        $user->assignRole('customer');

        $login = $this->postJson('/api/login', [
            'email' => 'jwt@example.com',
            'password' => 'password',
        ]);

        $login->assertOk();
        $access = $login->json('access_token');

        $this->getJson('/api/me', ['Authorization' => 'Bearer '.$access])
            ->assertOk()
            ->assertJsonPath('email', 'jwt@example.com')
            ->assertJsonPath('roles.0', 'customer');
    }

    public function test_refresh_rotates_tokens(): void
    {
        $login = $this->registerAndLogin();
        $oldRefresh = $login['refresh_token'];

        $refresh = $this->postJson('/api/refresh', [
            'refresh_token' => $oldRefresh,
        ]);

        $refresh->assertOk()
            ->assertJsonStructure(['access_token', 'refresh_token']);

        $this->assertNotSame($oldRefresh, $refresh->json('refresh_token'));

        $this->postJson('/api/refresh', [
            'refresh_token' => $oldRefresh,
        ])->assertUnauthorized();
    }

    public function test_logout_blacklists_access_and_refresh(): void
    {
        $tokens = $this->registerAndLogin();

        $this->postJson('/api/logout', [
            'refresh_token' => $tokens['refresh_token'],
        ], [
            'Authorization' => 'Bearer '.$tokens['access_token'],
        ])->assertOk();

        $this->getJson('/api/me', [
            'Authorization' => 'Bearer '.$tokens['access_token'],
        ])->assertUnauthorized();

        $this->postJson('/api/refresh', [
            'refresh_token' => $tokens['refresh_token'],
        ])->assertUnauthorized();
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $this->postJson('/api/login', [
            'email' => 'nobody@example.com',
            'password' => 'wrong',
        ])->assertUnauthorized();
    }

    /**
     * @return array{access_token: string, refresh_token: string}
     */
    private function registerAndLogin(): array
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Api User',
            'email' => 'rotate@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        return [
            'access_token' => $response->json('access_token'),
            'refresh_token' => $response->json('refresh_token'),
        ];
    }
}
