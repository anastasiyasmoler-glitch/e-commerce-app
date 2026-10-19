<?php

namespace App\Repositories;

use App\Models\OutboxMessage;
use App\Repositories\Contracts\OutboxMessageRepositoryInterface;

class EloquentOutboxMessageRepository implements OutboxMessageRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function enqueue(string $topic, array $payload): OutboxMessage
    {
        return OutboxMessage::query()->create([
            'topic' => $topic,
            'payload' => $payload,
        ]);
    }

    public function nextPending(): ?OutboxMessage
    {
        $query = OutboxMessage::query()
            ->whereNull('published_at')
            ->orderBy('id');

        $driver = config('database.connections.'.config('database.default').'.driver');

        if ($driver === 'pgsql') {
            $query->lock('FOR UPDATE SKIP LOCKED');
        } else {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function markPublished(OutboxMessage $message): void
    {
        $message->published_at = now();
        $message->save();
    }
}
