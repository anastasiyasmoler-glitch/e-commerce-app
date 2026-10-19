<?php

namespace App\Contracts;

interface OrderIdempotencyStore
{
    public function findOrderId(int $userId, string $idempotencyKey): ?int;

    public function remember(int $userId, string $idempotencyKey, int $orderId, int $ttlSeconds): void;
}
