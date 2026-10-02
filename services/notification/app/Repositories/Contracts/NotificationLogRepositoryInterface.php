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

    public function markSent(NotificationLog $log): NotificationLog;

    public function markFailed(NotificationLog $log, string $errorMessage): NotificationLog;

    public function markDlq(NotificationLog $log): NotificationLog;
}
