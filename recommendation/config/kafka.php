<?php

return [
    'brokers' => env('KAFKA_BROKERS', 'kafka:9092'),
    'consumer_group' => env('KAFKA_CONSUMER_GROUP', 'recommendation-service-group'),
    'consume_topics' => [
        'product.created',
        'product.updated',
        'product.deleted',
        'product.published',
        'product.unpublished',
        'stock.changed',
    ],
];
