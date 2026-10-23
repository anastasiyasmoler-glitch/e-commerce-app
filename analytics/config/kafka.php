<?php

return [
    'brokers' => env('KAFKA_BROKERS', 'kafka:9092'),
    'consumer_group' => env('KAFKA_CONSUMER_GROUP', 'analytics-service-group'),
    'consume_topics' => [
        'user.registered',
        'order.created',
        'order.paid',
        'order.shipped',
        'order.completed',
        'order.cancelled',
        'search.performed',
    ],
];
