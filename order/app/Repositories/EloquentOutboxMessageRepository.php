<?php

namespace App\Repositories;

use App\Models\OutboxMessage;
use App\Repositories\Contracts\OutboxMessageRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentOutboxMessageRepository implements OutboxMessageRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function enqueue(string $topic, array $payload, ?string $key = null): OutboxMessage
    {
        return OutboxMessage::query()->create([
            'topic' => $topic,
            'key' => $key,
            'payload' => $payload,
        ]);
    }

    public function claimPending(string $workerId, int $lockSeconds = 30): ?OutboxMessage
    {
        return DB::transaction(function () use ($workerId, $lockSeconds): ?OutboxMessage {
            $query = OutboxMessage::query()
                ->whereNull('published_at')
                ->where(function ($builder): void {
                    $builder
                        ->whereNull('locked_until')
                        ->orWhere('locked_until', '<', now());
                })
                ->orderBy('id');

            $driver = config('database.connections.'.config('database.default').'.driver');

            if ($driver === 'pgsql') {
                $query->lock('FOR UPDATE SKIP LOCKED');
            } else {
                $query->lockForUpdate();
            }

            $message = $query->first();

            if ($message === null) {
                return null;
            }

            $message->locked_by = $workerId;
            $message->locked_until = now()->addSeconds($lockSeconds);
            $message->attempts = $message->attempts + 1;
            $message->last_error = null;
            $message->save();

            return $message->fresh();
        });
    }

    public function markPublished(OutboxMessage $message): void
    {
        $message->published_at = now();
        $message->locked_by = null;
        $message->locked_until = null;
        $message->last_error = null;
        $message->save();
    }

    public function markFailed(OutboxMessage $message, string $error): void
    {
        $message->locked_by = null;
        $message->locked_until = null;
        $message->last_error = $error;
        $message->save();
    }
}
