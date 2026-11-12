<?php

namespace App\Kafka;

use App\Contracts\KafkaPublisherInterface;
use App\Repositories\Contracts\OutboxMessageRepositoryInterface;
use App\Tracing\Tracer;

class OutboxKafkaPublisher implements KafkaPublisherInterface
{
    public function __construct(
        private readonly OutboxMessageRepositoryInterface $outbox,
        private readonly Tracer $tracer,
    ) {}

    /**
     * @param  array<string, mixed>  $body
     */
    public function publish(string $topic, array $body): void
    {
        $key = isset($body['order_id']) ? (string) $body['order_id'] : null;

        $this->outbox->enqueue($topic, $this->tracer->inject($body), $key);
    }
}
