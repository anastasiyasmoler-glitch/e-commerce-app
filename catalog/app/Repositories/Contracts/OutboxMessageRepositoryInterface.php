<?php

namespace App\Repositories\Contracts;

use App\Models\OutboxMessage;

interface OutboxMessageRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function enqueue(string $topic, array $payload): OutboxMessage;

    public function nextPending(): ?OutboxMessage;

    public function markPublished(OutboxMessage $message): void;
}
