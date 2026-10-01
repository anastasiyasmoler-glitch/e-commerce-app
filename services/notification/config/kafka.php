<?php

return [

    'brokers' => env('KAFKA_BROKERS', 'kafka:9092'),

    'consumer_group' => env('KAFKA_CONSUMER_GROUP', 'notification-service-group'),

    'topics' => [
        'user.registered',
        'order.paid',
        'order.confirmed',
        'order.cancelled',
    ],

    'dlq_topic' => env('KAFKA_DLQ_TOPIC', 'notifications.dlq'),

    'max_attempts' => (int) env('KAFKA_MAX_ATTEMPTS', 3),

    'backoff_base_ms' => (int) env('KAFKA_BACKOFF_BASE_MS', 200),

];
