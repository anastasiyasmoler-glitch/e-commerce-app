<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Repositories\Contracts\NotificationLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class NotificationHistoryService
{
    public function __construct(
        private NotificationLogRepositoryInterface $logs,
    ) {}

    /**
     * @return Collection<int, NotificationLog>
     */
    public function listLatest(): Collection
    {
        return $this->logs->listLatest();
    }

    public function find(string $id): ?NotificationLog
    {
        return $this->logs->findById($id);
    }
}
