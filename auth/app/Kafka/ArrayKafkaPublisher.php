<?php

namespace App\Kafka;

use App\Contracts\KafkaPublisherInterface;

class ArrayKafkaPublisher implements KafkaPublisherInterface
{
    /** @var list<array{topic: string, body: array<string, mixed>}> */
    public array $messages = [];

    /**
     * @param  array<string, mixed>  $body
     */
    public function publish(string $topic, array $body): void
    {
        $this->messages[] = [
            'topic' => $topic,
            'body' => $body,
        ];
    }
}
