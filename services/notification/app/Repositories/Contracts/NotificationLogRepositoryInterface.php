<?php

namespace App\Repositories\Contracts;

use App\Models\NotificationLog;

interface NotificationLogRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function createPending(string $event, string $channel, string $recipient, array $payload): NotificationLog;

    public function findById(string $id): ?NotificationLog;
}
