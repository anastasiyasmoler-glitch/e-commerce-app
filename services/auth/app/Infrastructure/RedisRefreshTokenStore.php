<?php

namespace App\Infrastructure;

use App\Contracts\RefreshTokenStore;
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

    public function forget(int $userId, string $plainToken): void
    {
        $hash = $this->hash($plainToken);
        Redis::del($this->tokenKey($userId, $hash), $this->lookupKey($hash));
    }

    private function hash(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    private function tokenKey(int $userId, string $hash): string
    {
        return 'auth:refresh:'.$userId.':'.$hash;
    }

    private function lookupKey(string $hash): string
    {
        return 'auth:refresh:lookup:'.$hash;
    }
}
