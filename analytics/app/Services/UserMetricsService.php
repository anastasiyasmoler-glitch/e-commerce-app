<?php

namespace App\Services;

use App\Contracts\Repositories\EventMetricsRepositoryInterface;
use Carbon\CarbonImmutable;

class UserMetricsService
{
    public function __construct(
        private readonly EventMetricsRepositoryInterface $metrics,
    ) {}

    /**
     * @return array{total: int, by_day: list<array{date: string, count: int}>}
     */
    public function report(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return $this->metrics->userRegistrations($from, $to);
    }
}
