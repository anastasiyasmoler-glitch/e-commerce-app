<?php

namespace Tests\Unit;

use App\Exceptions\CannotModifyAdminRolesException;
use App\Repositories\Contracts\AdminUserRepositoryInterface;
use App\Services\AdminService;
use Mockery;
use PHPUnit\Framework\TestCase;

class AdminServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_set_analyst_throws_when_user_is_admin(): void
    {
        $users = Mockery::mock(AdminUserRepositoryInterface::class);
        $users->shouldReceive('hasRole')->once()->with(1, 'admin')->andReturn(true);
        $users->shouldNotReceive('assignRole');
        $users->shouldNotReceive('removeRole');

        $this->expectException(CannotModifyAdminRolesException::class);

        (new AdminService($users))->setAnalyst(1, true);
    }

    public function test_set_analyst_assigns_role_when_not_admin(): void
    {
        $users = Mockery::mock(AdminUserRepositoryInterface::class);
        $users->shouldReceive('hasRole')->once()->with(2, 'admin')->andReturn(false);
        $users->shouldReceive('assignRole')->once()->with(2, 'analyst');

        (new AdminService($users))->setAnalyst(2, true);

        $this->addToAssertionCount(1);
    }
}
