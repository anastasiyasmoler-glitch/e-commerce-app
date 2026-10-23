<?php

namespace App\Services;

use App\Contracts\Repositories\EventMetricsRepositoryInterface;
use Carbon\CarbonImmutable;

class SearchMetricsService
{
    public function __construct(
        private readonly EventMetricsRepositoryInterface $metrics,
    ) {}

    /**
     * @return array{total: int, top_queries: list<array{query: string, count: int}>}
     */
    public function report(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return $this->metrics->searchMetrics($from, $to);
    }
}
