<?php

namespace App\Repositories\Contracts;

use App\Models\OutboxMessage;

interface OutboxMessageRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function enqueue(string $topic, array $payload, ?string $key = null): OutboxMessage;

    public function claimPending(string $workerId, int $lockSeconds = 30): ?OutboxMessage;

    public function markPublished(OutboxMessage $message): void;

    public function markFailed(OutboxMessage $message, string $error): void;
}
