<?php

namespace Tests\Feature\Api;

use App\Contracts\RefreshTokenStore;
use App\Infrastructure\RedisRefreshTokenStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;
use Throwable;

class RedisJwtAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cache.default' => 'redis',
            'database.redis.default.database' => '15',
            'database.redis.cache.database' => '14',
        ]);

        Redis::purge();
        $this->app->forgetInstance('cache');
        $this->app->forgetInstance('cache.store');

        foreach ([
            'tymon.jwt',
            'tymon.jwt.auth',
            'tymon.jwt.providers.jwt',
            'tymon.jwt.providers.auth',
            'tymon.jwt.providers.storage',
            'tymon.jwt.blacklist',
            'tymon.jwt.manager',
            'tymon.jwt.parser',
        ] as $abstract) {
            $this->app->forgetInstance($abstract);
        }

        $this->app->bind(RefreshTokenStore::class, RedisRefreshTokenStore::class);

        try {
            Redis::connection('default')->ping();
            Redis::connection('cache')->ping();
        } catch (Throwable $e) {
            $this->markTestSkipped('Redis is not reachable: '.$e->getMessage());
        }

        Redis::connection('default')->flushdb();
        Redis::connection('cache')->flushdb();
    }

    public function test_login_writes_hashed_refresh_keys(): void
    {
        $response = $this->registerUser();
        $user = User::query()->where('email', 'redis@example.com')->first();
        $this->assertNotNull($user);

        $refresh = $this->refreshCookieValue($response);
        $hash = hash('sha256', $refresh);

        $this->assertGreaterThan(0, Redis::exists('auth:refresh:'.$user->id.':'.$hash));
        $this->assertSame((string) $user->id, Redis::get('auth:refresh:lookup:'.$hash));
    }

    public function test_logout_drops_refresh_keys_and_blacklists_access_in_redis(): void
    {
        $response = $this->registerUser();
        $user = User::query()->where('email', 'redis@example.com')->first();
        $this->assertNotNull($user);

        $access = $response->json('access_token');
        $refresh = $this->refreshCookieValue($response);
        $hash = hash('sha256', $refresh);
        $jti = $this->jwtClaim($access, 'jti');

        $this->withUnencryptedCookie((string) config('jwt.refresh_cookie'), $refresh)
            ->postJson('/logout', [], [
                'Authorization' => 'Bearer '.$access,
            ])
            ->assertOk();

        $this->assertSame(0, Redis::exists('auth:refresh:'.$user->id.':'.$hash));
        $this->assertNull(Redis::get('auth:refresh:lookup:'.$hash));
        $this->assertTrue(Cache::store('redis')->tags('tymon.jwt')->has($jti));

        $this->getJson('/me', ['Authorization' => 'Bearer '.$access])
            ->assertUnauthorized();
    }

    private function registerUser()
    {
        return $this->postJson('/register', [
            'name' => 'Redis User',
            'email' => 'redis@example.com',
            'phone' => '375296666666',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
    }

    private function refreshCookieValue($response): string
    {
        $cookie = $response->getCookie((string) config('jwt.refresh_cookie'), false);
        $this->assertNotNull($cookie);

        return (string) $cookie->getValue();
    }

    private function jwtClaim(string $jwt, string $claim): string
    {
        $parts = explode('.', $jwt);
        $this->assertCount(3, $parts);

        $b64 = strtr($parts[1], '-_', '+/');
        $b64 .= str_repeat('=', (4 - strlen($b64) % 4) % 4);
        $payload = json_decode((string) base64_decode($b64, true), true);
        $this->assertIsArray($payload);
        $this->assertArrayHasKey($claim, $payload);

        return (string) $payload[$claim];
    }
}
