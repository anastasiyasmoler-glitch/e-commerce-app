<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_the_identity_provider(): void
    {
        $this->get(route('auth.google'))
            ->assertRedirect();
    }

    public function test_authenticated_user_cannot_start_google_login(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $this->actingAs($user)
            ->get(route('auth.google'))
            ->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_callback_creates_customer_and_logs_in(): void
    {
        $this->fakeGoogleUser('Ada Lovelace', 'ada@example.com', 'sub-ada');

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();

        $user = User::query()->where('email', 'ada@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('customer'));
        $this->assertSame('google', $user->provider);
        $this->assertSame('sub-ada', $user->provider_id);
        $this->assertNull($user->password);
    }

    public function test_callback_logs_in_existing_user_with_the_same_email(): void
    {
        $existing = User::factory()->create([
            'email' => 'ada@example.com',
        ]);
        $existing->assignRole('customer');

        $this->fakeGoogleUser('Ada', 'ada@example.com', 'sub-ada');

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($existing);
        $this->assertSame(1, User::query()->where('email', 'ada@example.com')->count());
    }

    public function test_callback_redirects_to_login_when_email_is_missing(): void
    {
        $this->fakeGoogleUser('Ada', '', 'sub-ada');

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_callback_redirects_to_login_when_state_is_invalid(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andThrow(new InvalidStateException());
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    private function fakeGoogleUser(string $name, string $email, string $id): void
    {
        $oauthUser = Mockery::mock(SocialiteUser::class);
        $oauthUser->shouldReceive('getName')->andReturn($name);
        $oauthUser->shouldReceive('getEmail')->andReturn($email);
        $oauthUser->shouldReceive('getId')->andReturn($id);

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andReturn($oauthUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }
}
