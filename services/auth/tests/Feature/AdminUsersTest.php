<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_list_admin_users(): void
    {
        $this->getJson('/api/admin/users')->assertUnauthorized();
    }

    public function test_customer_cannot_view_admin_users(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $this->withToken($this->accessToken($user))
            ->getJson('/api/admin/users')
            ->assertForbidden();
    }

    public function test_admin_can_list_users(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->withToken($this->accessToken($admin))
            ->getJson('/api/admin/users')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_admin_can_assign_and_remove_analyst(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $user->assignRole('customer');

        $token = $this->accessToken($admin);

        $this->withToken($token)
            ->patchJson('/api/admin/users/'.$user->id.'/analyst', [
                'analyst' => true,
            ])
            ->assertOk();

        $this->assertTrue($user->fresh()->hasRole('analyst'));

        $this->withToken($token)
            ->patchJson('/api/admin/users/'.$user->id.'/analyst', [
                'analyst' => false,
            ])
            ->assertOk();

        $this->assertFalse($user->fresh()->hasRole('analyst'));
        $this->assertTrue($user->fresh()->hasRole('customer'));
    }

    public function test_admin_cannot_change_analyst_on_an_admin_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $otherAdmin = User::factory()->create();
        $otherAdmin->assignRole('admin');

        $this->withToken($this->accessToken($admin))
            ->patchJson('/api/admin/users/'.$otherAdmin->id.'/analyst', [
                'analyst' => true,
            ])
            ->assertForbidden();

        $this->assertFalse($otherAdmin->fresh()->hasRole('analyst'));
    }

    public function test_customer_cannot_patch_analyst(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $target = User::factory()->create();
        $target->assignRole('customer');

        $this->withToken($this->accessToken($customer))
            ->patchJson('/api/admin/users/'.$target->id.'/analyst', [
                'analyst' => true,
            ])
            ->assertForbidden();
    }

    public function test_api_register_still_assigns_only_customer(): void
    {
        $this->postJson('/api/register', [
            'name' => 'New User',
            'email' => 'new-user@example.com',
            'phone' => '375291111111',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertCreated();

        $user = User::query()->where('email', 'new-user@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('customer'));
        $this->assertFalse($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('analyst'));
    }

    private function accessToken(User $user): string
    {
        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $login->assertOk();

        return (string) $login->json('access_token');
    }
}
