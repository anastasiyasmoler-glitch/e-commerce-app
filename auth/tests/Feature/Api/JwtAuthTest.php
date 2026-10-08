<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class JwtAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->disableCookieEncryption();
        $this->withCredentials();
    }

    public function test_register_returns_access_and_sets_refresh_cookie(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Api User',
            'email' => 'api@example.com',
            'phone' => '375291111111',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['access_token', 'token_type', 'expires_in'])
            ->assertJsonMissingPath('refresh_token')
            ->assertCookie((string) config('jwt.refresh_cookie'));

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
            ->assertJsonPath('phone', $user->phone)
            ->assertJsonPath('roles.0', 'customer');
    }

    public function test_refresh_rotates_tokens(): void
    {
        $login = $this->registerUser();
        $oldRefresh = $this->refreshCookieValue($login);

        $refresh = $this->withUnencryptedCookie((string) config('jwt.refresh_cookie'), $oldRefresh)
            ->postJson('/api/refresh');

        $refresh->assertOk()
            ->assertJsonStructure(['access_token', 'token_type', 'expires_in'])
            ->assertJsonMissingPath('refresh_token')
            ->assertCookie((string) config('jwt.refresh_cookie'));

        $this->assertNotSame($oldRefresh, $this->refreshCookieValue($refresh));

        $this->withUnencryptedCookie((string) config('jwt.refresh_cookie'), $oldRefresh)
            ->postJson('/api/refresh')
            ->assertUnauthorized();
    }

    public function test_logout_blacklists_access_and_refresh(): void
    {
        $tokens = $this->registerUser();
        $refresh = $this->refreshCookieValue($tokens);

        $this->withUnencryptedCookie((string) config('jwt.refresh_cookie'), $refresh)
            ->postJson('/api/logout', [], [
                'Authorization' => 'Bearer '.$tokens->json('access_token'),
            ])
            ->assertOk();

        $this->getJson('/api/me', [
            'Authorization' => 'Bearer '.$tokens->json('access_token'),
        ])->assertUnauthorized();

        $this->withUnencryptedCookie((string) config('jwt.refresh_cookie'), $refresh)
            ->postJson('/api/refresh')
            ->assertUnauthorized();
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $this->postJson('/api/login', [
            'email' => 'nobody@example.com',
            'password' => 'wrong',
        ])->assertUnauthorized();
    }

    public function test_refresh_without_cookie_is_unauthorized(): void
    {
        $this->postJson('/api/refresh')->assertUnauthorized();
    }

    private function registerUser(): TestResponse
    {
        return $this->postJson('/api/register', [
            'name' => 'Api User',
            'email' => 'rotate@example.com',
            'phone' => '375292222222',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
    }

    private function refreshCookieValue(TestResponse $response): string
    {
        $cookie = $response->getCookie((string) config('jwt.refresh_cookie'), false);
        $this->assertNotNull($cookie);

        return (string) $cookie->getValue();
    }
}
