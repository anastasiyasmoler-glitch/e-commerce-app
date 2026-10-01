<?php

namespace Tests\Unit;

use App\Models\User;
use App\Repositories\Contracts\SocialUserRepositoryInterface;
use App\Services\SocialAuthService;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;

class SocialAuthServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_returns_user_found_by_provider_without_creating(): void
    {
        $existing = new User();
        $existing->id = 3;

        $users = Mockery::mock(SocialUserRepositoryInterface::class);
        $users->shouldReceive('findByProvider')->once()->with('google', 'sub-1')->andReturn($existing);
        $users->shouldNotReceive('findByEmail');
        $users->shouldNotReceive('createOAuthUser');
        $users->shouldNotReceive('assignRole');

        $result = (new SocialAuthService($users))->findOrCreateFromOAuth(
            'Ada',
            'ada@example.com',
            'google',
            'sub-1',
        );

        $this->assertSame($existing, $result);
    }

    public function test_returns_user_found_by_email_without_creating(): void
    {
        $existing = new User();
        $existing->id = 4;

        $users = Mockery::mock(SocialUserRepositoryInterface::class);
        $users->shouldReceive('findByProvider')->once()->andReturn(null);
        $users->shouldReceive('findByEmail')->once()->with('ada@example.com')->andReturn($existing);
        $users->shouldNotReceive('createOAuthUser');
        $users->shouldNotReceive('assignRole');

        $result = (new SocialAuthService($users))->findOrCreateFromOAuth(
            'Ada',
            '  ADA@example.com ',
            'google',
            'sub-1',
        );

        $this->assertSame($existing, $result);
    }

    public function test_creates_customer_when_user_does_not_exist(): void
    {
        $created = new User();
        $created->id = 9;

        $users = Mockery::mock(SocialUserRepositoryInterface::class);
        $users->shouldReceive('findByProvider')->once()->andReturn(null);
        $users->shouldReceive('findByEmail')->once()->andReturn(null);
        $users->shouldReceive('createOAuthUser')
            ->once()
            ->with('Ada', 'ada@example.com', 'google', 'sub-1')
            ->andReturn($created);
        $users->shouldReceive('assignRole')->once()->with(9, 'customer');

        $result = (new SocialAuthService($users))->findOrCreateFromOAuth(
            'Ada',
            'ada@example.com',
            'google',
            'sub-1',
        );

        $this->assertSame($created, $result);
    }

    public function test_throws_when_email_is_missing(): void
    {
        $users = Mockery::mock(SocialUserRepositoryInterface::class);
        $users->shouldNotReceive('findByProvider');

        $this->expectException(InvalidArgumentException::class);

        (new SocialAuthService($users))->findOrCreateFromOAuth('Ada', '  ', 'google', 'sub-1');
    }
}
