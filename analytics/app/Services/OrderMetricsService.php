<?php

namespace App\Services;

use App\Contracts\Repositories\EventMetricsRepositoryInterface;
use Carbon\CarbonImmutable;

class OrderMetricsService
{
    public function __construct(
        private readonly EventMetricsRepositoryInterface $metrics,
    ) {}

    /**
     * @return array{created: int, paid: int, cancelled: int, conversion_rate: float|null}
     */
    public function report(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return $this->metrics->orderFunnel($from, $to);
    }
}
