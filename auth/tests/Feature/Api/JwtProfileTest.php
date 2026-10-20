<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_requires_bearer_token(): void
    {
        $this->getJson('/profile')->assertUnauthorized();
        $this->patchJson('/profile', [
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'phone' => '375291000000',
        ])->assertUnauthorized();
    }

    public function test_show_and_update_profile(): void
    {
        $headers = $this->bearerForEmail('profile@example.com');

        $this->getJson('/profile', $headers)
            ->assertOk()
            ->assertJsonPath('email', 'profile@example.com')
            ->assertJsonPath('phone', '375293333333');

        $this->patchJson('/profile', [
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

    public function test_patch_profile_allows_partial_update(): void
    {
        $headers = $this->bearerForEmail('partial@example.com');

        $this->patchJson('/profile', [
            'name' => 'Only Name Changed',
        ], $headers)
            ->assertOk()
            ->assertJsonPath('name', 'Only Name Changed')
            ->assertJsonPath('email', 'partial@example.com')
            ->assertJsonPath('phone', '375293333333');
    }

    public function test_change_password_and_login_with_new_one(): void
    {
        $headers = $this->bearerForEmail('pwd@example.com');

        $this->putJson('/profile/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ], $headers)->assertOk();

        $this->postJson('/login', [
            'email' => 'pwd@example.com',
            'password' => 'password',
        ])->assertUnauthorized();

        $this->postJson('/login', [
            'email' => 'pwd@example.com',
            'password' => 'new-password',
        ])->assertOk();
    }

    public function test_delete_soft_deletes_account(): void
    {
        $login = $this->postJson('/register', [
            'name' => 'Gone',
            'email' => 'gone@example.com',
            'phone' => '375295555555',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $access = $login->json('access_token');
        $cookie = $login->getCookie((string) config('auth.refresh_cookie.name'), false);
        $this->assertNotNull($cookie);

        $this->withUnencryptedCookie($cookie->getName(), (string) $cookie->getValue())
            ->deleteJson('/profile', [
                'password' => 'password',
            ], [
                'Authorization' => 'Bearer '.$access,
            ])
            ->assertOk();

        $this->assertSoftDeleted('users', ['email' => 'gone@example.com']);

        $this->getJson('/me', ['Authorization' => 'Bearer '.$access])
            ->assertUnauthorized();
    }

    public function test_password_change_revokes_refresh_tokens(): void
    {
        $login = $this->postJson('/register', [
            'name' => 'Revoked',
            'email' => 'revoked@example.com',
            'phone' => '375297777777',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $access = (string) $login->json('access_token');
        $refresh = (string) $login->getCookie((string) config('auth.refresh_cookie.name'), false)?->getValue();

        $this->putJson('/profile/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ], ['Authorization' => 'Bearer '.$access])->assertOk();

        $this->withUnencryptedCookie((string) config('auth.refresh_cookie.name'), $refresh)
            ->postJson('/refresh')
            ->assertUnauthorized();
    }

    public function test_oauth_user_can_delete_account_without_password(): void
    {
        $user = User::factory()->create([
            'email' => 'oauth@example.com',
            'password' => null,
            'provider' => 'google',
            'provider_id' => 'sub-oauth',
        ]);
        $user->assignRole('customer');

        $token = JWTAuth::fromUser($user);

        $this->withToken($token)
            ->deleteJson('/profile')
            ->assertOk();

        $this->assertSoftDeleted('users', ['email' => 'oauth@example.com']);
    }

    public function test_phone_must_match_expected_format(): void
    {
        $this->postJson('/register', [
            'name' => 'Bad Phone',
            'email' => 'bad-phone@example.com',
            'phone' => 'abc123',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertUnprocessable()->assertJsonValidationErrors(['phone']);
    }

    public function test_email_verification_flow(): void
    {
        $headers = $this->bearerForEmail('verify@example.com');

        $this->patchJson('/profile', [
            'name' => 'Verify Me',
            'email' => 'verify-new@example.com',
            'phone' => '375293333333',
        ], $headers)->assertOk();

        $this->assertNull(User::query()->where('email', 'verify-new@example.com')->firstOrFail()->email_verified_at);

        $this->postJson('/profile/email-verification', [], $headers)
            ->assertOk()
            ->assertJsonMissingPath('token');

        $user = User::query()->where('email', 'verify-new@example.com')->firstOrFail();
        $token = Cache::get('email_verify:'.$user->id);
        $this->assertIsString($token);

        $this->postJson('/profile/verify-email', [
            'token' => '000000',
        ], $headers)->assertUnprocessable();

        $this->postJson('/profile/verify-email', [
            'token' => $token,
        ], $headers)->assertOk();

        $this->assertNotNull(User::query()->where('email', 'verify-new@example.com')->firstOrFail()->email_verified_at);
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

        $login = $this->postJson('/login', [
            'email' => $email,
            'password' => 'password',
        ]);

        return ['Authorization' => 'Bearer '.$login->json('access_token')];
    }
}
