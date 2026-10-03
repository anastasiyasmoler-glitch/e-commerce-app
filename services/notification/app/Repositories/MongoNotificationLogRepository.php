<?php

namespace App\Repositories;

use App\Models\NotificationLog;
use App\Repositories\Contracts\NotificationLogRepositoryInterface;

class MongoNotificationLogRepository implements NotificationLogRepositoryInterface
{
    public function createPending(string $event, string $channel, string $recipient, array $payload): NotificationLog
    {
        return NotificationLog::query()->create([
            'event' => $event,
            'channel' => $channel,
            'recipient' => $recipient,
            'payload' => $payload,
            'status' => NotificationLog::STATUS_PENDING,
            'attempts' => 0,
            'error_message' => null,
            'is_dlq' => false,
            'sent_at' => null,
        ]);
    }

    public function findById(string $id): ?NotificationLog
    {
        return NotificationLog::query()->find($id);
    }
}
