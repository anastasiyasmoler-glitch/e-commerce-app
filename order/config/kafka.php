<?php

return [
    'outbox_relay_enabled' => env('ORDER_OUTBOX_RELAY_ENABLED', true),
    'brokers' => env('KAFKA_BROKERS', 'kafka:9092'),
    'consumer_group' => env('KAFKA_CONSUMER_GROUP', 'order-service-group'),
    'consume_topics' => [
        'product.created',
        'product.updated',
        'stock.reserved',
        'stock.reservation_failed',
    ],
];
