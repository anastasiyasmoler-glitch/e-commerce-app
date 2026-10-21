<?php

namespace App\Infrastructure;

use App\Contracts\Auth\RefreshTokenStore;
use Illuminate\Support\Facades\Redis;

class RedisRefreshTokenStore implements RefreshTokenStore
{
    public function put(int $userId, string $plainToken, int $ttlSeconds): void
    {
        $hash = $this->hash($plainToken);
        $ttl = max(1, $ttlSeconds);

        Redis::setex($this->tokenKey($userId, $hash), $ttl, '1');
        Redis::setex($this->lookupKey($hash), $ttl, (string) $userId);
    }

    public function userIdFor(string $plainToken): ?int
    {
        $hash = $this->hash($plainToken);
        $userId = Redis::get($this->lookupKey($hash));

        if (! is_numeric($userId)) {
            return null;
        }

        $id = (int) $userId;

        if (Redis::exists($this->tokenKey($id, $hash)) === 0) {
            return null;
        }

        return $id;
    }

    /**
     * Atomically validates and consumes a refresh token via a Lua script so a
     * token cannot be claimed twice in a race.
     */
    public function claim(string $plainToken): ?int
    {
        $hash = $this->hash($plainToken);

        $script = <<<'LUA'
            local userId = redis.call("GET", KEYS[1])
            if not userId then
                return false
            end
            local tokenKey = KEYS[2] .. userId .. ":" .. ARGV[1]
            if redis.call("EXISTS", tokenKey) == 0 then
                return false
            end
            redis.call("DEL", KEYS[1], tokenKey)
            return userId
        LUA;

        $result = Redis::eval(
            $script,
            2,
            $this->lookupKey($hash),
            $this->tokenPrefix(),
            $hash,
        );

        return is_numeric($result) ? (int) $result : null;
    }

    public function forget(int $userId, string $plainToken): void
    {
        $hash = $this->hash($plainToken);
        Redis::del($this->tokenKey($userId, $hash), $this->lookupKey($hash));
    }

    public function forgetAll(int $userId): void
    {
        $pattern = $this->tokenPrefix().$userId.':*';

        $script = <<<'LUA'
            local cursor = "0"
            repeat
                local result = redis.call("SCAN", cursor, "MATCH", KEYS[1], "COUNT", 100)
                cursor = result[1]
                for _, key in ipairs(result[2]) do
                    local hash = string.sub(key, string.len(KEYS[2]) + 1)
                    redis.call("DEL", key)
                    if hash ~= "" then
                        redis.call("DEL", KEYS[3] .. hash)
                    end
                end
            until cursor == "0"
        LUA;

        Redis::eval(
            $script,
            3,
            $pattern,
            $this->tokenPrefix().$userId.':',
            $this->lookupPrefix(),
        );
    }

    private function hash(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    private function tokenPrefix(): string
    {
        return 'auth:refresh:';
    }

    private function lookupPrefix(): string
    {
        return 'auth:refresh:lookup:';
    }

    private function tokenKey(int $userId, string $hash): string
    {
        return $this->tokenPrefix().$userId.':'.$hash;
    }

    private function lookupKey(string $hash): string
    {
        return $this->lookupPrefix().$hash;
    }
}
