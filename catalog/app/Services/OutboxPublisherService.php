<?php

namespace App\Services;

use App\Kafka\LonglangKafkaPublisher;
use App\Repositories\Contracts\OutboxMessageRepositoryInterface;
use Illuminate\Support\Facades\DB;

class OutboxPublisherService
{
    public function __construct(
        private readonly OutboxMessageRepositoryInterface $outbox,
        private readonly LonglangKafkaPublisher $kafka,
    ) {}

    public function flush(int $limit = 50): int
    {
        $sent = 0;

        for ($i = 0; $i < $limit; $i++) {
            $published = DB::transaction(function (): bool {
                $message = $this->outbox->nextPending();

                if ($message === null) {
                    return false;
                }

                /** @var array<string, mixed> $payload */
                $payload = is_array($message->payload) ? $message->payload : [];
                $this->kafka->publish($message->topic, $payload);
                $this->outbox->markPublished($message);

                return true;
            });

            if (! $published) {
                break;
            }

            $sent++;
        }

        return $sent;
    }
}
