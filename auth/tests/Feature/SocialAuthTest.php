<?php

namespace Tests\Feature;

use App\Kafka\ArrayKafkaPublisher;
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
        $this->get('/auth/google')
            ->assertRedirect();
    }

    public function test_callback_creates_customer_and_issues_jwt(): void
    {
        $this->fakeGoogleUser('Ada Lovelace', 'ada@example.com', 'sub-ada');

        $this->getJson('/auth/google/callback')
            ->assertOk()
            ->assertJsonStructure(['access_token', 'token_type', 'expires_in'])
            ->assertJsonMissingPath('refresh_token')
            ->assertCookie((string) config('jwt.refresh_cookie'));

        $this->assertGuest();

        $user = User::query()->where('email', 'ada@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('customer'));
        $this->assertSame('google', $user->provider);
        $this->assertSame('sub-ada', $user->provider_id);
        $this->assertNull($user->password);
    }

    public function test_callback_issues_jwt_for_existing_user_with_the_same_email(): void
    {
        $existing = User::factory()->create([
            'email' => 'ada@example.com',
        ]);
        $existing->assignRole('customer');

        $this->fakeGoogleUser('Ada', 'ada@example.com', 'sub-ada');

        $this->getJson('/auth/google/callback')
            ->assertOk()
            ->assertJsonStructure(['access_token']);

        $this->assertSame(1, User::query()->where('email', 'ada@example.com')->count());
        $this->assertTrue($existing->fresh()->hasRole('customer'));
    }

    public function test_callback_rejects_when_email_is_missing(): void
    {
        $this->fakeGoogleUser('Ada', '', 'sub-ada');

        $this->getJson('/auth/google/callback')
            ->assertUnauthorized();

        $this->assertDatabaseMissing('users', ['provider_id' => 'sub-ada']);
    }

    public function test_callback_rejects_when_state_is_invalid(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andThrow(new InvalidStateException);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->getJson('/auth/google/callback')
            ->assertUnauthorized();
    }

    public function test_callback_publishes_user_registered_for_a_new_google_user(): void
    {
        $publisher = $this->app->make(ArrayKafkaPublisher::class);

        $this->fakeGoogleUser('Ada Lovelace', 'ada@example.com', 'sub-ada');

        $this->getJson('/auth/google/callback')->assertOk();

        $this->assertCount(1, $publisher->messages);
        $this->assertSame('user.registered', $publisher->messages[0]['topic']);
        $this->assertSame('ada@example.com', $publisher->messages[0]['body']['email']);
    }

    public function test_callback_does_not_publish_user_registered_for_an_existing_email(): void
    {
        $existing = User::factory()->create([
            'email' => 'ada@example.com',
        ]);
        $existing->assignRole('customer');

        $publisher = $this->app->make(ArrayKafkaPublisher::class);
        $publisher->messages = [];

        $this->fakeGoogleUser('Ada', 'ada@example.com', 'sub-ada');

        $this->getJson('/auth/google/callback')->assertOk();

        $this->assertSame([], $publisher->messages);
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
