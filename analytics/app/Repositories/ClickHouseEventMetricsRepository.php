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
        $fromDay = $this->formatDate($from);
        $toDay = $this->formatDate($to);

        $totalRow = $client->select(
            <<<'SQL'
            SELECT sum(registrations) AS total
            FROM registrations_daily
            WHERE day >= {from:Date}
              AND day <= {to:Date}
            SQL,
            [
                'from' => $fromDay,
                'to' => $toDay,
            ],
        )->fetchOne();

        $byDayRows = $client->select(
            <<<'SQL'
            SELECT day, sum(registrations) AS registrations
            FROM registrations_daily
            WHERE day >= {from:Date}
              AND day <= {to:Date}
            GROUP BY day
            ORDER BY day
            SQL,
            [
                'from' => $fromDay,
                'to' => $toDay,
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
        $fromDay = $this->formatDate($from);
        $toDay = $this->formatDate($to);

        $row = $this->clickHouse->make()->select(
            <<<'SQL'
            SELECT
                sum(created) AS created,
                sum(paid) AS paid,
                sum(shipped) AS shipped,
                sum(completed) AS completed,
                sum(cancelled) AS cancelled,
                sum(revenue_cents) AS revenue_cents
            FROM orders_daily
            WHERE day >= {from:Date}
              AND day <= {to:Date}
            SQL,
            [
                'from' => $fromDay,
                'to' => $toDay,
            ],
        )->fetchOne();

        $created = (int) ($row['created'] ?? 0);
        $paid = (int) ($row['paid'] ?? 0);
        $shipped = (int) ($row['shipped'] ?? 0);
        $completed = (int) ($row['completed'] ?? 0);
        $cancelled = (int) ($row['cancelled'] ?? 0);
        $revenueCents = (int) ($row['revenue_cents'] ?? 0);

        $currencyRow = $this->clickHouse->make()->select(
            <<<'SQL'
            SELECT anyLast(JSONExtractString(payload, 'currency')) AS currency
            FROM events_raw
            WHERE topic = 'order.paid'
              AND occurred_at >= {from:DateTime64(3)}
              AND occurred_at <= {to:DateTime64(3)}
              AND JSONExtractString(payload, 'currency') != ''
            SQL,
            [
                'from' => $this->formatDateTime($from),
                'to' => $this->formatDateTime($to),
            ],
        )->fetchOne();

        $currency = isset($currencyRow['currency']) && $currencyRow['currency'] !== ''
            ? (string) $currencyRow['currency']
            : null;

        $revenueByDayRows = $this->clickHouse->make()->select(
            <<<'SQL'
            SELECT day, sum(revenue_cents) AS revenue_cents
            FROM orders_daily
            WHERE day >= {from:Date}
              AND day <= {to:Date}
            GROUP BY day
            ORDER BY day
            SQL,
            [
                'from' => $fromDay,
                'to' => $toDay,
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
            'shipped' => $shipped,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'conversion_rate' => $created > 0 ? round($paid / $created, 4) : null,
            'completion_rate' => $created > 0 ? round($completed / $created, 4) : null,
            'revenue_cents' => $revenueCents,
            'average_order_value_cents' => $paid > 0 ? (int) round($revenueCents / $paid) : null,
            'currency' => $currency,
            'revenue_by_day' => $revenueByDay,
        ];
    }

    public function searchMetrics(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $client = $this->clickHouse->make();
        $fromDay = $this->formatDate($from);
        $toDay = $this->formatDate($to);

        $totalRow = $client->select(
            <<<'SQL'
            SELECT sum(searches) AS total
            FROM search_daily
            WHERE day >= {from:Date}
              AND day <= {to:Date}
            SQL,
            [
                'from' => $fromDay,
                'to' => $toDay,
            ],
        )->fetchOne();

        $topRows = $client->select(
            <<<'SQL'
            SELECT
                query,
                sum(searches) AS count
            FROM search_query_daily
            WHERE day >= {from:Date}
              AND day <= {to:Date}
            GROUP BY query
            ORDER BY count DESC
            LIMIT 10
            SQL,
            [
                'from' => $fromDay,
                'to' => $toDay,
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
            SELECT sum(zero_results) AS zero_result_total
            FROM search_daily
            WHERE day >= {from:Date}
              AND day <= {to:Date}
            SQL,
            [
                'from' => $fromDay,
                'to' => $toDay,
            ],
        )->fetchOne();

        $zeroTopRows = $client->select(
            <<<'SQL'
            SELECT
                query,
                sum(zero_results) AS count
            FROM search_query_daily
            WHERE day >= {from:Date}
              AND day <= {to:Date}
            GROUP BY query
            HAVING count > 0
            ORDER BY count DESC
            LIMIT 10
            SQL,
            [
                'from' => $fromDay,
                'to' => $toDay,
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

    private function formatDate(CarbonImmutable $value): string
    {
        return $value->utc()->format('Y-m-d');
    }

    private function formatDateTime(CarbonImmutable $value): string
    {
        return $value->utc()->format('Y-m-d H:i:s.u');
    }
}
