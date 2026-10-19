<?php

namespace App\Repositories\Contracts;

interface OrderIdempotencyRepositoryInterface
{
    /**
     * Call only inside an open database transaction.
     *
     * Ensures a row exists for the key, locks it, and returns an existing order id when the key was already fulfilled.
     */
    public function lockForKey(int $userId, string $idempotencyKey): ?int;

    /**
     * Call only inside the same transaction after the order row was created.
     */
    public function attachOrderId(int $userId, string $idempotencyKey, int $orderId): void;
}
