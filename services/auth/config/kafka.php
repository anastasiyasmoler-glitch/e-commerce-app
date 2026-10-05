<?php

return [

    'brokers' => env('KAFKA_BROKERS', 'kafka:9092'),

    'user_registered_topic' => env('KAFKA_USER_REGISTERED_TOPIC', 'user.registered'),

];
