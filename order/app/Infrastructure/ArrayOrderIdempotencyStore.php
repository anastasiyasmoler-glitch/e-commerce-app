<?php

namespace App\Infrastructure;

use App\Contracts\OrderIdempotencyStore;

class ArrayOrderIdempotencyStore implements OrderIdempotencyStore
{
    /** @var array<string, int> */
    private array $keys = [];

    public function findOrderId(int $userId, string $idempotencyKey): ?int
    {
        $key = $this->storageKey($userId, $idempotencyKey);

        return $this->keys[$key] ?? null;
    }

    public function remember(int $userId, string $idempotencyKey, int $orderId, int $ttlSeconds): void
    {
        $this->keys[$this->storageKey($userId, $idempotencyKey)] = $orderId;
    }

    private function storageKey(int $userId, string $idempotencyKey): string
    {
        return $userId.':'.hash('sha256', $idempotencyKey);
    }
}
