<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_update_and_delete_own_profile(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $this->assertTrue($customer->can('update', $customer));
        $this->assertTrue($customer->can('delete', $customer));
    }

    public function test_customer_cannot_update_or_delete_another_profile(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $other = User::factory()->create();
        $other->assignRole('customer');

        $this->assertFalse($customer->can('update', $other));
        $this->assertFalse($customer->can('delete', $other));
    }

    public function test_admin_cannot_update_or_delete_a_customer_profile(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $this->assertFalse($admin->can('update', $customer));
        $this->assertFalse($admin->can('delete', $customer));
    }

    public function test_admin_can_update_and_delete_own_profile(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->assertTrue($admin->can('update', $admin));
        $this->assertTrue($admin->can('delete', $admin));
    }

    public function test_admin_can_view_any_users_and_customer_cannot(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $this->assertTrue($admin->can('viewAny', User::class));
        $this->assertFalse($customer->can('viewAny', User::class));
    }
}
