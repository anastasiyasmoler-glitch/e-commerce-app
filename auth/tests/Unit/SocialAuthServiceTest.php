<?php

namespace Tests\Unit;

use App\Contracts\Repositories\SocialUserRepositoryInterface;
use App\Models\User;
use App\Services\SocialAuthService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Events\Dispatcher;
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
        $existing = new User;
        $existing->id = 3;

        $users = Mockery::mock(SocialUserRepositoryInterface::class);
        $users->shouldReceive('findByProvider')->once()->with('google', 'sub-1')->andReturn($existing);
        $users->shouldNotReceive('findByEmail');
        $users->shouldNotReceive('createOAuthUser');
        $users->shouldNotReceive('assignRole');

        $events = Mockery::mock(Dispatcher::class);
        $events->shouldNotReceive('dispatch');

        $result = (new SocialAuthService($users, $events))->findOrCreateFromOAuth(
            'Ada',
            'ada@example.com',
            'google',
            'sub-1',
            true,
        );

        $this->assertSame($existing, $result);
    }

    public function test_returns_user_found_by_email_when_provider_email_is_verified(): void
    {
        $existing = new User;
        $existing->id = 4;

        $users = Mockery::mock(SocialUserRepositoryInterface::class);
        $users->shouldReceive('findByProvider')->once()->andReturn(null);
        $users->shouldReceive('findByEmail')->once()->with('ada@example.com')->andReturn($existing);
        $users->shouldReceive('linkProvider')->once()->with($existing, 'google', 'sub-1')->andReturn($existing);
        $users->shouldNotReceive('createOAuthUser');
        $users->shouldNotReceive('assignRole');

        $events = Mockery::mock(Dispatcher::class);
        $events->shouldNotReceive('dispatch');

        $result = (new SocialAuthService($users, $events))->findOrCreateFromOAuth(
            'Ada',
            '  ADA@example.com ',
            'google',
            'sub-1',
            true,
        );

        $this->assertSame($existing, $result);
    }

    public function test_does_not_link_by_email_when_provider_email_is_unverified(): void
    {
        $created = new User;
        $created->id = 8;

        $users = Mockery::mock(SocialUserRepositoryInterface::class);
        $users->shouldReceive('findByProvider')->once()->andReturn(null);
        $users->shouldNotReceive('findByEmail');
        $users->shouldReceive('createOAuthUser')
            ->once()
            ->with('Ada', 'ada@example.com', 'google', 'sub-1')
            ->andReturn($created);
        $users->shouldReceive('assignRole')->once()->with(8, 'customer');

        $events = Mockery::mock(Dispatcher::class);
        $events->shouldReceive('dispatch')->once();

        $result = (new SocialAuthService($users, $events))->findOrCreateFromOAuth(
            'Ada',
            'ada@example.com',
            'google',
            'sub-1',
            false,
        );

        $this->assertSame($created, $result);
    }

    public function test_creates_customer_when_user_does_not_exist(): void
    {
        $created = new User;
        $created->id = 9;

        $users = Mockery::mock(SocialUserRepositoryInterface::class);
        $users->shouldReceive('findByProvider')->once()->andReturn(null);
        $users->shouldReceive('findByEmail')->once()->andReturn(null);
        $users->shouldReceive('createOAuthUser')
            ->once()
            ->with('Ada', 'ada@example.com', 'google', 'sub-1')
            ->andReturn($created);
        $users->shouldReceive('assignRole')->once()->with(9, 'customer');

        $events = Mockery::mock(Dispatcher::class);
        $events->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::on(fn (mixed $event): bool => $event instanceof Registered && $event->user === $created));

        $result = (new SocialAuthService($users, $events))->findOrCreateFromOAuth(
            'Ada',
            'ada@example.com',
            'google',
            'sub-1',
            true,
        );

        $this->assertSame($created, $result);
    }

    public function test_creates_customer_when_email_is_missing(): void
    {
        $created = new User;
        $created->id = 10;

        $users = Mockery::mock(SocialUserRepositoryInterface::class);
        $users->shouldReceive('findByProvider')->once()->andReturn(null);
        $users->shouldNotReceive('findByEmail');
        $users->shouldReceive('createOAuthUser')
            ->once()
            ->with('Ada', null, 'google', 'sub-1')
            ->andReturn($created);
        $users->shouldReceive('assignRole')->once()->with(10, 'customer');

        $events = Mockery::mock(Dispatcher::class);
        $events->shouldReceive('dispatch')->once();

        $result = (new SocialAuthService($users, $events))->findOrCreateFromOAuth(
            'Ada',
            '  ',
            'google',
            'sub-1',
            true,
        );

        $this->assertSame($created, $result);
    }

    public function test_throws_when_provider_id_is_missing(): void
    {
        $users = Mockery::mock(SocialUserRepositoryInterface::class);
        $users->shouldNotReceive('findByProvider');

        $events = Mockery::mock(Dispatcher::class);
        $events->shouldNotReceive('dispatch');

        $this->expectException(InvalidArgumentException::class);

        (new SocialAuthService($users, $events))->findOrCreateFromOAuth('Ada', 'ada@example.com', 'google', '');
    }
}
