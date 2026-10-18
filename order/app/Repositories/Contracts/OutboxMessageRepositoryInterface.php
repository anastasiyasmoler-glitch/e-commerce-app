<?php

namespace App\Repositories\Contracts;

use App\Models\OutboxMessage;

interface OutboxMessageRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function enqueue(string $topic, array $payload, ?string $key = null): OutboxMessage;

    /**
     * Claim one pending row for relay (short DB transaction, no broker I/O).
     * At-least-once: the same event may be published again if the worker dies after Kafka accepts it.
     */
    public function claimPending(string $workerId, int $lockSeconds = 30): ?OutboxMessage;

    public function markPublished(OutboxMessage $message): void;

    public function markFailed(OutboxMessage $message, string $error): void;
}
