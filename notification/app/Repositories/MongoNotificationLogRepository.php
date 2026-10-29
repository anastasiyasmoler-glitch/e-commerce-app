<?php

namespace App\Repositories;

use App\Data\NotificationHistoryFilters;
use App\Models\NotificationLog;
use App\Repositories\Contracts\NotificationLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

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
        return $this->listFiltered(new NotificationHistoryFilters);
    }

    public function listFiltered(NotificationHistoryFilters $filters): Collection
    {
        $query = NotificationLog::query()->orderByDesc('created_at');

        if ($filters->status !== null) {
            $query->where('status', $filters->status);
        }

        if ($filters->channel !== null && $filters->channel !== '') {
            $query->where('channel', $filters->channel);
        }

        if ($filters->recipient !== null && $filters->recipient !== '') {
            $escaped = addcslashes($filters->recipient, '%_\\');
            $query->where('recipient', 'like', '%'.$escaped.'%');
        }

        if ($filters->from !== null && $filters->from !== '') {
            $query->where('created_at', '>=', Carbon::parse($filters->from)->startOfDay());
        }

        if ($filters->to !== null && $filters->to !== '') {
            $query->where('created_at', '<=', Carbon::parse($filters->to)->endOfDay());
        }

        return $query->get();
    }

    public function markSent(NotificationLog $log): NotificationLog
    {
        $log->status = NotificationLog::STATUS_SENT;
        $log->sent_at = now();
        $log->error_message = null;
        $log->is_dlq = false;
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

    public function resetForReplay(NotificationLog $log): NotificationLog
    {
        $log->status = NotificationLog::STATUS_PENDING;
        $log->is_dlq = false;
        $log->error_message = null;
        $log->attempts = 0;
        $log->sent_at = null;
        $log->save();

        return $log;
    }
}
