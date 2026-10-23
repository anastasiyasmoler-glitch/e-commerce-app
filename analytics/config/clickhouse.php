<?php

return [
    'host' => env('CLICKHOUSE_HOST', 'clickhouse'),
    'port' => (int) env('CLICKHOUSE_PORT', 8123),
    'database' => env('CLICKHOUSE_DATABASE', 'analytics'),
    'username' => env('CLICKHOUSE_USERNAME', 'default'),
    'password' => env('CLICKHOUSE_PASSWORD', ''),
];
