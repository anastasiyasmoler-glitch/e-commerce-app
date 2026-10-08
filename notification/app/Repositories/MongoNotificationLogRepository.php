<?php

namespace App\Repositories;

use App\Models\NotificationLog;
use App\Repositories\Contracts\NotificationLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

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

    public function listLatest(): Collection
    {
        return NotificationLog::query()
            ->orderByDesc('created_at')
            ->get();
    }

    public function markSent(NotificationLog $log): NotificationLog
    {
        $log->status = NotificationLog::STATUS_SENT;
        $log->sent_at = now();
        $log->error_message = null;
        $log->save();

        return $log;
    }

    public function markFailed(NotificationLog $log, string $errorMessage): NotificationLog
    {
        $log->status = NotificationLog::STATUS_FAILED;
        $log->attempts = (int) $log->attempts + 1;
        $log->error_message = $errorMessage;
        $log->save();

        return $log;
    }

    public function markDlq(NotificationLog $log): NotificationLog
    {
        $log->status = NotificationLog::STATUS_FAILED;
        $log->is_dlq = true;
        $log->save();

        return $log;
    }
}
