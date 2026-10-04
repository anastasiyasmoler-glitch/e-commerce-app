<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JwtProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_requires_bearer_token(): void
    {
        $this->getJson('/api/profile')->assertUnauthorized();
        $this->patchJson('/api/profile', [
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'phone' => '375291000000',
        ])->assertUnauthorized();
    }

    public function test_show_and_update_profile(): void
    {
        $headers = $this->bearerForEmail('profile@example.com');

        $this->getJson('/api/profile', $headers)
            ->assertOk()
            ->assertJsonPath('email', 'profile@example.com')
            ->assertJsonPath('phone', '375293333333');

        $this->patchJson('/api/profile', [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'phone' => '375294444444',
        ], $headers)
            ->assertOk()
            ->assertJsonPath('name', 'Ada Lovelace')
            ->assertJsonPath('email', 'ada@example.com')
            ->assertJsonPath('phone', '375294444444');

        $this->assertDatabaseHas('users', [
            'email' => 'ada@example.com',
            'phone' => '375294444444',
        ]);
    }

    public function test_change_password_and_login_with_new_one(): void
    {
        $headers = $this->bearerForEmail('pwd@example.com');

        $this->putJson('/api/profile/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ], $headers)->assertOk();

        $this->postJson('/api/login', [
            'email' => 'pwd@example.com',
            'password' => 'password',
        ])->assertUnauthorized();

        $this->postJson('/api/login', [
            'email' => 'pwd@example.com',
            'password' => 'new-password',
        ])->assertOk();
    }

    public function test_delete_soft_deletes_account(): void
    {
        $login = $this->postJson('/api/register', [
            'name' => 'Gone',
            'email' => 'gone@example.com',
            'phone' => '375295555555',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $access = $login->json('access_token');
        $cookie = $login->getCookie((string) config('jwt.refresh_cookie'), false);
        $this->assertNotNull($cookie);

        $this->withUnencryptedCookie($cookie->getName(), (string) $cookie->getValue())
            ->deleteJson('/api/profile', [
                'password' => 'password',
            ], [
                'Authorization' => 'Bearer '.$access,
            ])
            ->assertOk();

        $this->assertSoftDeleted('users', ['email' => 'gone@example.com']);

        $this->getJson('/api/me', ['Authorization' => 'Bearer '.$access])
            ->assertUnauthorized();
    }

    /**
     * @return array{Authorization: string}
     */
    private function bearerForEmail(string $email): array
    {
        $user = User::factory()->create([
            'email' => $email,
            'phone' => '375293333333',
        ]);
        $user->assignRole('customer');

        $login = $this->postJson('/api/login', [
            'email' => $email,
            'password' => 'password',
        ]);

        return ['Authorization' => 'Bearer '.$login->json('access_token')];
    }
}
