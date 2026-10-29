<?php

namespace Tests\Integration;

use App\Services\AnalyticsEventIngestService;
use App\Services\OrderMetricsService;
use App\Services\SearchMetricsService;
use App\Services\UserMetricsService;
use Carbon\CarbonImmutable;

class ClickHouseMetricsIntegrationTest extends ClickHouseIntegrationTestCase
{
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

    public function test_order_funnel_and_revenue_after_ingest(): void
    {
        $ingest = $this->app->make(AnalyticsEventIngestService::class);
        $day = CarbonImmutable::parse('2026-10-16 10:00:00', 'UTC');
        $iso = $day->toIso8601String();

        $ingest->handle('order.created', ['order_id' => 101, 'occurred_at' => $iso]);
        $ingest->handle('order.created', ['order_id' => 102, 'occurred_at' => $iso]);
        $ingest->handle('order.paid', [
            'order_id' => 101,
            'total_cents' => 5000,
            'currency' => 'USD',
            'occurred_at' => $iso,
        ]);
        $ingest->handle('order.paid', [
            'order_id' => 102,
            'total_cents' => 3000,
            'currency' => 'USD',
            'occurred_at' => $iso,
        ]);
        $ingest->handle('order.cancelled', ['order_id' => 102, 'occurred_at' => $iso]);

        $report = $this->app->make(OrderMetricsService::class)->report(
            CarbonImmutable::parse('2026-10-16', 'UTC')->startOfDay(),
            CarbonImmutable::parse('2026-10-16', 'UTC')->endOfDay(),
        );

        $this->assertSame(2, $report['created']);
        $this->assertSame(2, $report['paid']);
        $this->assertSame(1, $report['cancelled']);
        $this->assertSame(8000, $report['revenue_cents']);
        $this->assertSame(1.0, $report['conversion_rate']);
        $this->assertSame('USD', $report['currency']);
        $this->assertSame(4000, $report['average_order_value_cents']);
    }

    public function test_search_metrics_include_zero_result_queries(): void
    {
        $ingest = $this->app->make(AnalyticsEventIngestService::class);
        $day = CarbonImmutable::parse('2026-10-17 14:00:00', 'UTC');
        $iso = $day->toIso8601String();

        $ingest->handle('search.performed', [
            'query' => 'sofa',
            'filters' => ['store_id' => 1],
            'result_count' => 12,
            'timestamp' => $iso,
        ]);
        $ingest->handle('search.performed', [
            'query' => 'sofa',
            'filters' => ['store_id' => 1],
            'result_count' => 3,
            'timestamp' => $iso,
        ]);
        $ingest->handle('search.performed', [
            'query' => 'unknown-sku-xyz',
            'filters' => ['store_id' => 1],
            'result_count' => 0,
            'timestamp' => $iso,
        ]);

        $report = $this->app->make(SearchMetricsService::class)->report(
            CarbonImmutable::parse('2026-10-17', 'UTC')->startOfDay(),
            CarbonImmutable::parse('2026-10-17', 'UTC')->endOfDay(),
        );

        $this->assertSame(3, $report['total']);
        $this->assertSame(1, $report['zero_result_total']);
        $this->assertSame('sofa', $report['top_queries'][0]['query']);
        $this->assertSame(2, $report['top_queries'][0]['count']);
        $this->assertSame('unknown-sku-xyz', $report['zero_result_queries'][0]['query']);
        $this->assertSame(1, $report['zero_result_queries'][0]['count']);
    }
}
