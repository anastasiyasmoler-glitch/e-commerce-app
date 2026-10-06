<?php

namespace App\Repositories\Contracts;

use App\Models\NotificationLog;
use Illuminate\Database\Eloquent\Collection;

interface NotificationLogRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function createPending(string $event, string $channel, string $recipient, array $payload): NotificationLog;

    public function findById(string $id): ?NotificationLog;

    /**
     * @return Collection<int, NotificationLog>
     */
    public function listLatest(): Collection;

    public function markSent(NotificationLog $log): NotificationLog;

    public function markFailed(NotificationLog $log, string $errorMessage): NotificationLog;

    public function markDlq(NotificationLog $log): NotificationLog;
}
