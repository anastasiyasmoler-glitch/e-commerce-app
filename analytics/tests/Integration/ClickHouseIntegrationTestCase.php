<?php

namespace Tests\Integration;

use App\Infrastructure\ClickHouseClientFactory;
use Tests\TestCase;

abstract class ClickHouseIntegrationTestCase extends TestCase
{
    protected function setUp(): void
    {
        if (! getenv('ANALYTICS_CLICKHOUSE_TESTS')) {
            $this->markTestSkipped('Set ANALYTICS_CLICKHOUSE_TESTS=1 to run ClickHouse integration tests.');
        }

        parent::setUp();

        $this->bootstrapClickHouseSchema();
        $this->truncateClickHouseRollups();
    }

    protected function bootstrapClickHouseSchema(): void
    {
        $client = $this->app->make(ClickHouseClientFactory::class)->make();
        $initDir = base_path('docker/clickhouse/init');

        foreach (['01_schema.sql', '02_rollups.sql'] as $file) {
            $sql = file_get_contents($initDir.'/'.$file);
            $this->assertNotFalse($sql);
            foreach (explode(';', $sql) as $statement) {
                $statement = trim($statement);
                if ($statement !== '') {
                    $client->write($statement);
                }
            }
        }
    }

    protected function truncateClickHouseRollups(): void
    {
        $client = $this->app->make(ClickHouseClientFactory::class)->make();
        foreach (['events_raw', 'registrations_daily', 'orders_daily', 'search_daily', 'search_query_daily'] as $table) {
            $client->write("TRUNCATE TABLE IF EXISTS analytics.{$table}");
        }
    }
}
