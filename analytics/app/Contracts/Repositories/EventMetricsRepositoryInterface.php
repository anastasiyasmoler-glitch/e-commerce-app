<?php

namespace App\Contracts\Repositories;

use Carbon\CarbonImmutable;

interface EventMetricsRepositoryInterface
{
    /**
     * @return array{total: int, by_day: list<array{date: string, count: int}>}
     */
    public function userRegistrations(CarbonImmutable $from, CarbonImmutable $to): array;

    /**
     * @return array{created: int, paid: int, cancelled: int, conversion_rate: float|null}
     */
    public function orderFunnel(CarbonImmutable $from, CarbonImmutable $to): array;

    /**
     * @return array{total: int, top_queries: list<array{query: string, count: int}>}
     */
    public function searchMetrics(CarbonImmutable $from, CarbonImmutable $to): array;
}
