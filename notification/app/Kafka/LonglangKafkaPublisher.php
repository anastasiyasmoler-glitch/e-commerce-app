<?php

namespace App\Kafka;

use App\Contracts\KafkaPublisherInterface;
use JsonException;
use longlang\phpkafka\Producer\Producer;
use longlang\phpkafka\Producer\ProducerConfig;

class LonglangKafkaPublisher implements KafkaPublisherInterface
{
    /**
     * @param  array<string, mixed>  $body
     *
     * @throws JsonException
     */
    public function publish(string $topic, array $body): void
    {
        $config = new ProducerConfig;
        $config->setBootstrapServer((string) config('kafka.brokers'));
        $config->setUpdateBrokers(true);
        $config->setAcks(-1);

        $producer = new Producer($config);

        try {
            $producer->send(
                $topic,
                json_encode($body, JSON_THROW_ON_ERROR),
                (string) ($body['notification_log_id'] ?? $body['event'] ?? $topic),
            );
        } finally {
            $producer->close();
        }
    }
}
