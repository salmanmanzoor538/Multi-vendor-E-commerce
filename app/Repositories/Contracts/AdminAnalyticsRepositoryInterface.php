<?php

namespace App\Repositories\Contracts;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

interface AdminAnalyticsRepositoryInterface
{
    public function totalPaidSales(CarbonInterface $from, CarbonInterface $to): float;

    public function monthlyPaidSales(CarbonInterface $from, CarbonInterface $to): Collection;

    public function paidSalesByPeriod(CarbonInterface $from, CarbonInterface $to, string $granularity): Collection;

    public function ordersCount(CarbonInterface $from, CarbonInterface $to): int;

    public function ordersByPeriod(CarbonInterface $from, CarbonInterface $to, string $granularity): Collection;

    public function orderStatusDistribution(CarbonInterface $from, CarbonInterface $to): Collection;

    public function vendorPerformance(CarbonInterface $from, CarbonInterface $to, int $limit): Collection;

    public function resolveRangeCurrency(CarbonInterface $from, CarbonInterface $to): string;
}
