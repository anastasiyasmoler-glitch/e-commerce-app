<?php

namespace Tests\Integration;

use App\Infrastructure\ClickHouseClientFactory;
use App\Services\AnalyticsEventIngestService;
use App\Services\UserMetricsService;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class ClickHouseMetricsIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        if (! getenv('ANALYTICS_CLICKHOUSE_TESTS')) {
            $this->markTestSkipped('Set ANALYTICS_CLICKHOUSE_TESTS=1 to run ClickHouse integration tests.');
        }

        parent::setUp();

        $this->bootstrapSchema();
        $this->truncateRollupTables();
    }

    public function test_user_registrations_rollup_after_ingest(): void
    {
        $ingest = $this->app->make(AnalyticsEventIngestService::class);
        $day = CarbonImmutable::parse('2026-10-15 12:00:00', 'UTC');

        $ingest->handle('user.registered', [
            'user_id' => 1,
            'email' => 'a@example.com',
            'occurred_at' => $day->toIso8601String(),
        ]);
        $ingest->handle('user.registered', [
            'user_id' => 2,
            'email' => 'b@example.com',
            'occurred_at' => $day->addHours(2)->toIso8601String(),
        ]);

        $report = $this->app->make(UserMetricsService::class)->report(
            CarbonImmutable::parse('2026-10-15', 'UTC')->startOfDay(),
            CarbonImmutable::parse('2026-10-15', 'UTC')->endOfDay(),
        );

        $this->assertSame(2, $report['total']);
        $this->assertCount(1, $report['by_day']);
        $this->assertSame('2026-10-15', $report['by_day'][0]['date']);
        $this->assertSame(2, $report['by_day'][0]['count']);
    }

    private function bootstrapSchema(): void
    {
        $client = $this->app->make(ClickHouseClientFactory::class)->make();
        $initDir = base_path('docker/clickhouse/init');

        foreach (['01_schema.sql', '02_rollups.sql'] as $file) {
            $sql = file_get_contents($initDir.'/'.$file);
            $this->assertNotFalse($sql);
            $client->write($sql);
        }
    }

    private function truncateRollupTables(): void
    {
        $client = $this->app->make(ClickHouseClientFactory::class)->make();
        $client->write(
            <<<'SQL'
            TRUNCATE TABLE IF EXISTS analytics.events_raw;
            TRUNCATE TABLE IF EXISTS analytics.registrations_daily;
            TRUNCATE TABLE IF EXISTS analytics.orders_daily;
            TRUNCATE TABLE IF EXISTS analytics.search_daily;
            TRUNCATE TABLE IF EXISTS analytics.search_query_daily;
            SQL,
        );
    }
}
