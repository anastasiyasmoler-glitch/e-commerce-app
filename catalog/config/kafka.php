<?php

return [
    'brokers' => env('KAFKA_BROKERS', 'kafka:9092'),
    'consumer_group' => env('KAFKA_CONSUMER_GROUP', 'catalog-service-group'),
    'consume_topics' => [
        'order.created',
        'stock.release_requested',
    ],
];
