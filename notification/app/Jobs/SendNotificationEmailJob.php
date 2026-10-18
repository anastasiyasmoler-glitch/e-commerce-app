<?php

namespace App\Jobs;

use App\Services\NotificationDispatchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SendNotificationEmailJob implements ShouldQueue
{
    use Queueable;

    public int $tries;

    public function __construct(
        public readonly string $notificationLogId,
        public readonly bool $skipDlqPublish = false,
    ) {
        $this->tries = max(1, (int) config('kafka.max_attempts'));
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        $baseMs = max(0, (int) config('kafka.backoff_base_ms'));
        $seconds = [];

        for ($attempt = 1; $attempt < $this->tries; $attempt++) {
            $seconds[] = (int) max(1, ($baseMs * (2 ** ($attempt - 1))) / 1000);
        }

        return $seconds === [] ? [1] : $seconds;
    }

    public function handle(NotificationDispatchService $dispatcher): void
    {
        $dispatcher->deliver($this->notificationLogId);
    }

    public function failed(?Throwable $exception): void
    {
        app(NotificationDispatchService::class)->afterDeliveryFailed(
            $this->notificationLogId,
            $this->skipDlqPublish,
        );
    }
}
