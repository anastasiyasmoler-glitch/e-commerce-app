<?php

namespace Tests\Unit;

use App\Infrastructure\RedisRefreshTokenStore;
use Illuminate\Support\Facades\Redis;
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
}
