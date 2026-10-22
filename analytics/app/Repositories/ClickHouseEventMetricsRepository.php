<?php

namespace App\Repositories;

use App\Contracts\Repositories\EventMetricsRepositoryInterface;
use App\Infrastructure\ClickHouseClientFactory;
use Carbon\CarbonImmutable;

class ClickHouseEventMetricsRepository implements EventMetricsRepositoryInterface
{
    public function __construct(
        private readonly ClickHouseClientFactory $clickHouse,
    ) {}

    public function userRegistrations(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $client = $this->clickHouse->make();

        $totalRow = $client->select(
            <<<'SQL'
            SELECT count() AS total
            FROM events_raw
            WHERE topic = {topic:String}
              AND occurred_at >= {from:DateTime64(3)}
              AND occurred_at <= {to:DateTime64(3)}
            SQL,
            [
                'topic' => 'user.registered',
                'from' => $this->formatDateTime($from),
                'to' => $this->formatDateTime($to),
            ],
        )->fetchOne();

        $byDayRows = $client->select(
            <<<'SQL'
            SELECT toDate(occurred_at) AS day, count() AS registrations
            FROM events_raw
            WHERE topic = {topic:String}
              AND occurred_at >= {from:DateTime64(3)}
              AND occurred_at <= {to:DateTime64(3)}
            GROUP BY day
            ORDER BY day
            SQL,
            [
                'topic' => 'user.registered',
                'from' => $this->formatDateTime($from),
                'to' => $this->formatDateTime($to),
            ],
        )->rows();

        $byDay = [];
        foreach ($byDayRows as $row) {
            $byDay[] = [
                'date' => (string) $row['day'],
                'count' => (int) $row['registrations'],
            ];
        }

        return [
            'total' => (int) ($totalRow['total'] ?? 0),
            'by_day' => $byDay,
        ];
    }

    public function orderFunnel(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $row = $this->clickHouse->make()->select(
            <<<'SQL'
            SELECT
                countIf(topic = 'order.created') AS created,
                countIf(topic = 'order.paid') AS paid,
                countIf(topic = 'order.cancelled') AS cancelled
            FROM events_raw
            WHERE topic IN ('order.created', 'order.paid', 'order.cancelled')
              AND occurred_at >= {from:DateTime64(3)}
              AND occurred_at <= {to:DateTime64(3)}
            SQL,
            [
                'from' => $this->formatDateTime($from),
                'to' => $this->formatDateTime($to),
            ],
        )->fetchOne();

        $created = (int) ($row['created'] ?? 0);
        $paid = (int) ($row['paid'] ?? 0);
        $cancelled = (int) ($row['cancelled'] ?? 0);

        $revenueRow = $this->clickHouse->make()->select(
            <<<'SQL'
            SELECT
                sum(JSONExtractInt(payload, 'total_cents')) AS revenue_cents,
                anyLast(JSONExtractString(payload, 'currency')) AS currency
            FROM events_raw
            WHERE topic = 'order.paid'
              AND occurred_at >= {from:DateTime64(3)}
              AND occurred_at <= {to:DateTime64(3)}
            SQL,
            [
                'from' => $this->formatDateTime($from),
                'to' => $this->formatDateTime($to),
            ],
        )->fetchOne();

        $revenueCents = (int) ($revenueRow['revenue_cents'] ?? 0);
        $currency = isset($revenueRow['currency']) && $revenueRow['currency'] !== ''
            ? (string) $revenueRow['currency']
            : null;

        $revenueByDayRows = $this->clickHouse->make()->select(
            <<<'SQL'
            SELECT
                toDate(occurred_at) AS day,
                sum(JSONExtractInt(payload, 'total_cents')) AS revenue_cents
            FROM events_raw
            WHERE topic = 'order.paid'
              AND occurred_at >= {from:DateTime64(3)}
              AND occurred_at <= {to:DateTime64(3)}
            GROUP BY day
            ORDER BY day
            SQL,
            [
                'from' => $this->formatDateTime($from),
                'to' => $this->formatDateTime($to),
            ],
        )->rows();

        $revenueByDay = [];
        foreach ($revenueByDayRows as $dayRow) {
            $revenueByDay[] = [
                'date' => (string) $dayRow['day'],
                'revenue_cents' => (int) $dayRow['revenue_cents'],
            ];
        }

        return [
            'created' => $created,
            'paid' => $paid,
            'cancelled' => $cancelled,
            'conversion_rate' => $created > 0 ? round($paid / $created, 4) : null,
            'revenue_cents' => $revenueCents,
            'average_order_value_cents' => $paid > 0 ? (int) round($revenueCents / $paid) : null,
            'currency' => $currency,
            'revenue_by_day' => $revenueByDay,
        ];
    }

    public function searchMetrics(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $client = $this->clickHouse->make();

        $totalRow = $client->select(
            <<<'SQL'
            SELECT count() AS total
            FROM events_raw
            WHERE topic = {topic:String}
              AND occurred_at >= {from:DateTime64(3)}
              AND occurred_at <= {to:DateTime64(3)}
            SQL,
            [
                'topic' => 'search.performed',
                'from' => $this->formatDateTime($from),
                'to' => $this->formatDateTime($to),
            ],
        )->fetchOne();

        $topRows = $client->select(
            <<<'SQL'
            SELECT
                JSONExtractString(payload, 'query') AS query,
                count() AS count
            FROM events_raw
            WHERE topic = {topic:String}
              AND occurred_at >= {from:DateTime64(3)}
              AND occurred_at <= {to:DateTime64(3)}
              AND JSONExtractString(payload, 'query') != ''
            GROUP BY query
            ORDER BY count DESC
            LIMIT 10
            SQL,
            [
                'topic' => 'search.performed',
                'from' => $this->formatDateTime($from),
                'to' => $this->formatDateTime($to),
            ],
        )->rows();

        $topQueries = [];
        foreach ($topRows as $row) {
            $topQueries[] = [
                'query' => (string) $row['query'],
                'count' => (int) $row['count'],
            ];
        }

        $zeroRow = $client->select(
            <<<'SQL'
            SELECT count() AS zero_result_total
            FROM events_raw
            WHERE topic = {topic:String}
              AND occurred_at >= {from:DateTime64(3)}
              AND occurred_at <= {to:DateTime64(3)}
              AND JSONExtractInt(payload, 'result_count') = 0
            SQL,
            [
                'topic' => 'search.performed',
                'from' => $this->formatDateTime($from),
                'to' => $this->formatDateTime($to),
            ],
        )->fetchOne();

        $zeroTopRows = $client->select(
            <<<'SQL'
            SELECT
                JSONExtractString(payload, 'query') AS query,
                count() AS count
            FROM events_raw
            WHERE topic = {topic:String}
              AND occurred_at >= {from:DateTime64(3)}
              AND occurred_at <= {to:DateTime64(3)}
              AND JSONExtractInt(payload, 'result_count') = 0
              AND JSONExtractString(payload, 'query') != ''
            GROUP BY query
            ORDER BY count DESC
            LIMIT 10
            SQL,
            [
                'topic' => 'search.performed',
                'from' => $this->formatDateTime($from),
                'to' => $this->formatDateTime($to),
            ],
        )->rows();

        $zeroResultQueries = [];
        foreach ($zeroTopRows as $row) {
            $zeroResultQueries[] = [
                'query' => (string) $row['query'],
                'count' => (int) $row['count'],
            ];
        }

        return [
            'total' => (int) ($totalRow['total'] ?? 0),
            'top_queries' => $topQueries,
            'zero_result_total' => (int) ($zeroRow['zero_result_total'] ?? 0),
            'zero_result_queries' => $zeroResultQueries,
        ];
    }

    private function formatDateTime(CarbonImmutable $value): string
    {
        return $value->utc()->format('Y-m-d H:i:s.u');
    }
}
