<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_customer_cannot_view_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_can_view_users_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users')
                ->has('users'));
    }

    public function test_admin_can_assign_and_remove_analyst(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $user->assignRole('customer');

        $this->actingAs($admin)
            ->patch(route('admin.users.analyst', $user), [
                'analyst' => true,
            ])
            ->assertRedirect(route('admin.users'));

        $this->assertTrue($user->fresh()->hasRole('analyst'));

        $this->actingAs($admin)
            ->patch(route('admin.users.analyst', $user), [
                'analyst' => false,
            ])
            ->assertRedirect(route('admin.users'));

        $this->assertFalse($user->fresh()->hasRole('analyst'));
        $this->assertTrue($user->fresh()->hasRole('customer'));
    }

    public function test_admin_cannot_change_analyst_on_an_admin_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $otherAdmin = User::factory()->create();
        $otherAdmin->assignRole('admin');

        $this->actingAs($admin)
            ->patch(route('admin.users.analyst', $otherAdmin), [
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

        $this->actingAs($customer)
            ->patch(route('admin.users.analyst', $target), [
                'analyst' => true,
            ])
            ->assertForbidden();
    }

    public function test_self_register_still_assigns_only_customer(): void
    {
        $this->post('/register', [
            'name' => 'New User',
            'email' => 'new-user@example.com',
            'phone' => '+15550001111',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $user = User::query()->where('email', 'new-user@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('customer'));
        $this->assertFalse($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('analyst'));
    }
}
