<?php

namespace App\Kafka;

use App\Contracts\KafkaPublisherInterface;
use App\Repositories\Contracts\OutboxMessageRepositoryInterface;

class OutboxKafkaPublisher implements KafkaPublisherInterface
{
    public function __construct(
        private readonly OutboxMessageRepositoryInterface $outbox,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public function publish(string $topic, array $body): void
    {
        $key = isset($body['order_id']) ? (string) $body['order_id'] : null;

        $this->outbox->enqueue($topic, $body, $key);
    }
}
