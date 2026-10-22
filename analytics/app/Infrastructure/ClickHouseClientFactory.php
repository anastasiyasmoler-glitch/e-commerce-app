<?php

namespace App\Infrastructure;

use ClickHouseDB\Client;

class ClickHouseClientFactory
{
    public function make(): Client
    {
        $client = new Client([
            'host' => config('clickhouse.host'),
            'port' => config('clickhouse.port'),
            'username' => config('clickhouse.username'),
            'password' => config('clickhouse.password'),
        ]);

        $client->database((string) config('clickhouse.database'));
        $client->setTimeout(10);
        $client->setConnectTimeOut(5);

        return $client;
    }
}
