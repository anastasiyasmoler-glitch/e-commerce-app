<?php

namespace App\Infrastructure;

use App\Contracts\OrderIdempotencyStore;
use Illuminate\Support\Facades\Redis;

class RedisOrderIdempotencyStore implements OrderIdempotencyStore
{
    public function findOrderId(int $userId, string $idempotencyKey): ?int
    {
        $value = Redis::get($this->key($userId, $idempotencyKey));

        return is_numeric($value) ? (int) $value : null;
    }

    public function remember(int $userId, string $idempotencyKey, int $orderId, int $ttlSeconds): void
    {
        Redis::setex($this->key($userId, $idempotencyKey), max(1, $ttlSeconds), (string) $orderId);
    }

    private function key(int $userId, string $idempotencyKey): string
    {
        return 'order:idempotency:'.$userId.':'.hash('sha256', $idempotencyKey);
    }
}
