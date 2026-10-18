<?php

namespace App\Kafka;

use JsonException;
use longlang\phpkafka\Producer\Producer;
use longlang\phpkafka\Producer\ProducerConfig;

/**
 * Publishes to Kafka topics (topic column = Kafka topic name in this monorepo).
 */
class LonglangKafkaPublisher
{
    /**
     * @param  array<string, mixed>  $body
     *
     * @throws JsonException
     */
    public function publish(string $topic, array $body, ?string $key = null): void
    {
        $config = new ProducerConfig;
        $config->setBootstrapServer((string) config('kafka.brokers'));
        $config->setUpdateBrokers(true);
        $config->setAcks(-1);

        $producer = new Producer($config);

        try {
            $messageKey = $key ?? (string) ($body['order_id'] ?? $body['product_id'] ?? $topic);
            $producer->send($topic, json_encode($body, JSON_THROW_ON_ERROR), $messageKey);
        } finally {
            $producer->close();
        }
    }
}
