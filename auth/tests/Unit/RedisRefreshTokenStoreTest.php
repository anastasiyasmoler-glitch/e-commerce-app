<?php

namespace Tests\Unit;

use App\Infrastructure\RedisRefreshTokenStore;
use Illuminate\Redis\Connections\PhpRedisConnection;
use Illuminate\Support\Facades\Redis;
use Mockery;
use Tests\TestCase;

class RedisRefreshTokenStoreTest extends TestCase
{
    public function test_put_stores_hashed_refresh_keys(): void
    {
        $token = 'plain-refresh-token';
        $hash = hash('sha256', $token);

        Redis::shouldReceive('setex')
            ->once()
            ->with('auth:refresh:7:'.$hash, 60, '1')
            ->andReturnTrue();
        Redis::shouldReceive('setex')
            ->once()
            ->with('auth:refresh:lookup:'.$hash, 60, '7')
            ->andReturnTrue();

        (new RedisRefreshTokenStore)->put(7, $token, 60);
    }

    public function test_user_id_for_requires_both_keys(): void
    {
        $token = 'plain-refresh-token';
        $hash = hash('sha256', $token);

        Redis::shouldReceive('get')
            ->once()
            ->with('auth:refresh:lookup:'.$hash)
            ->andReturn('7');
        Redis::shouldReceive('exists')
            ->once()
            ->with('auth:refresh:7:'.$hash)
            ->andReturn(1);

        $this->assertSame(7, (new RedisRefreshTokenStore)->userIdFor($token));
    }

    public function test_forget_deletes_token_and_lookup_keys(): void
    {
        $token = 'plain-refresh-token';
        $hash = hash('sha256', $token);

        Redis::shouldReceive('del')
            ->once()
            ->with('auth:refresh:7:'.$hash, 'auth:refresh:lookup:'.$hash)
            ->andReturn(2);

        (new RedisRefreshTokenStore)->forget(7, $token);
    }

    public function test_claim_atomically_validates_and_consumes_a_token(): void
    {
        $token = 'plain-refresh-token';
        $hash = hash('sha256', $token);

        $connection = Mockery::mock(PhpRedisConnection::class);
        $connection->shouldReceive('command')
            ->once()
            ->with(
                'eval',
                Mockery::on(function (array $parameters) use ($hash): bool {
                    return ($parameters[2] ?? null) === 2
                        && ($parameters[1] ?? null) === [
                            'auth:refresh:lookup:'.$hash,
                            'auth:refresh:',
                            $hash,
                        ];
                }),
            )
            ->andReturn('7');
        Redis::shouldReceive('connection')->once()->andReturn($connection);

        $this->assertSame(7, (new RedisRefreshTokenStore)->claim($token));
    }

    public function test_claim_returns_null_for_unknown_token(): void
    {
        $connection = Mockery::mock(PhpRedisConnection::class);
        $connection->shouldReceive('command')->once()->andReturn(false);
        Redis::shouldReceive('connection')->once()->andReturn($connection);

        $this->assertNull((new RedisRefreshTokenStore)->claim('unknown-token'));
    }

    public function test_forget_all_deletes_every_token_of_a_user(): void
    {
        $connection = Mockery::mock(PhpRedisConnection::class);
        $connection->shouldReceive('command')
            ->once()
            ->with(
                'eval',
                Mockery::on(function (array $parameters): bool {
                    return ($parameters[2] ?? null) === 3
                        && ($parameters[1] ?? null) === [
                            'auth:refresh:7:*',
                            'auth:refresh:7:',
                            'auth:refresh:lookup:',
                        ];
                }),
            )
            ->andReturnNull();
        Redis::shouldReceive('connection')->once()->andReturn($connection);

        (new RedisRefreshTokenStore)->forgetAll(7);
    }
}
