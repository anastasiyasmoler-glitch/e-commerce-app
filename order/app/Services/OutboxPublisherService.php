<?php

namespace App\Services;

use App\Kafka\LonglangKafkaPublisher;
use App\Repositories\Contracts\OutboxMessageRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

class OutboxPublisherService
{
    public function __construct(
        private readonly OutboxMessageRepositoryInterface $outbox,
        private readonly LonglangKafkaPublisher $kafka,
    ) {}

    public function flush(string $workerId, int $limit = 50, int $lockSeconds = 30): int
    {
        $sent = 0;

        for ($i = 0; $i < $limit; $i++) {
            $message = $this->outbox->claimPending($workerId, $lockSeconds);

            if ($message === null) {
                break;
            }

            try {
                $payload = $message->payload;

                $this->kafka->publish(
                    (string) $message->topic,
                    $payload,
                    $message->key,
                );

                $this->outbox->markPublished($message);
                $sent++;
            } catch (Throwable $exception) {
                $this->outbox->markFailed($message, $exception->getMessage());

                Log::warning('Order outbox publish failed', [
                    'outbox_id' => $message->id,
                    'topic' => $message->topic,
                    'attempts' => $message->attempts,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $sent;
    }
}
