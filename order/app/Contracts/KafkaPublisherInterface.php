<?php

namespace App\Contracts;

interface KafkaPublisherInterface
{
    /**
     * @param  array<string, mixed>  $body
     */
    public function publish(string $topic, array $body): void;
}
