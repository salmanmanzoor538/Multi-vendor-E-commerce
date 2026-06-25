<?php

namespace App\Services;

use App\Repositories\Contracts\AdminAnalyticsRepositoryInterface;
use App\Support\PerformanceCache;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AdminAnalyticsService
{
    public function __construct(
        private readonly AdminAnalyticsRepositoryInterface $adminAnalyticsRepository,
    ) {
    }

    /**
     * @param  array{range?: string, from?: string, to?: string, limit?: int}  $filters
     * @return array<string, mixed>
     */
    public function buildDashboardData(array $filters): array
    {
        $resolved = $this->resolveRange($filters);
        $from = $resolved['from'];
        $to = $resolved['to'];
        $range = $resolved['range'];
        $granularity = $resolved['granularity'];
        $limit = $resolved['limit'];
        $namespace = (string) config('performance.cache.namespaces.analytics', 'analytics');
        $ttl = (int) config('performance.cache.ttl.analytics_seconds', 300);
        $cachePayload = json_encode([
            'range' => $range,
            'from' => $from->toDateTimeString(),
            'to' => $to->toDateTimeString(),
            'granularity' => $granularity,
            'limit' => $limit,
        ]);
        $cacheSuffix = 'dashboard:'.md5($cachePayload ?: '');

        return PerformanceCache::remember($namespace, $cacheSuffix, $ttl, function () use ($from, $to, $range, $granularity, $limit): array {
            $currency = $this->adminAnalyticsRepository->resolveRangeCurrency($from, $to);

            $monthlyPaidSalesRows = $this->adminAnalyticsRepository->monthlyPaidSales($from, $to);
            $paidSalesRows = $this->adminAnalyticsRepository->paidSalesByPeriod($from, $to, $granularity);
            $ordersRows = $this->adminAnalyticsRepository->ordersByPeriod($from, $to, $granularity);
            $statusRows = $this->adminAnalyticsRepository->orderStatusDistribution($from, $to);
            $vendorRows = $this->adminAnalyticsRepository->vendorPerformance($from, $to, $limit);

            $monthlySeries = $this->normalizeSeries($from, $to, 'month', $monthlyPaidSalesRows, 'total_sales');
            $salesSeries = $this->normalizeSeries($from, $to, $granularity, $paidSalesRows, 'total_sales');
            $ordersSeries = $this->normalizeSeries($from, $to, $granularity, $ordersRows, 'orders_count');

            $currentMonthStart = $to->copy()->startOfMonth();
            $currentMonthEnd = $to->copy()->endOfMonth();

            return [
                'kpis' => [
                    'mrr' => round($this->adminAnalyticsRepository->totalPaidSales($currentMonthStart, $currentMonthEnd), 2),
                    'total_sales' => round($this->adminAnalyticsRepository->totalPaidSales($from, $to), 2),
                    'orders_count' => $this->adminAnalyticsRepository->ordersCount($from, $to),
                ],
                'trends' => [
                    'mrr_by_month' => $monthlySeries,
                    'sales_over_time' => $salesSeries,
                    'orders_over_time' => $ordersSeries,
                    'order_status_distribution' => [
                        'labels' => $statusRows->pluck('status')->map(fn (string $status) => Str::title($status))->values()->all(),
                        'data' => $statusRows->pluck('count')->map(fn ($count) => (int) $count)->values()->all(),
                    ],
                ],
                'vendor_performance' => $vendorRows->map(function (object $row): array {
                    return [
                        'vendor_id' => (int) $row->vendor_id,
                        'store_name' => (string) $row->store_name,
                        'total_sales' => round((float) $row->total_sales, 2),
                        'orders_count' => (int) $row->orders_count,
                    ];
                })->values()->all(),
                'meta' => [
                    'range' => $range,
                    'from' => $from->toDateString(),
                    'to' => $to->toDateString(),
                    'granularity' => $granularity,
                    'currency' => $currency,
                    'vendor_limit' => $limit,
                ],
            ];
        });
    }

    /**
     * @param  array{range?: string, from?: string, to?: string, limit?: int}  $filters
     * @return array{range: string, from: Carbon, to: Carbon, granularity: string, limit: int}
     */
    private function resolveRange(array $filters): array
    {
        $settings = config('analytics.dashboard');
        $configuredRanges = $settings['ranges'] ?? [];
        $defaultRange = (string) ($settings['default_range'] ?? '12m');
        $maxLimit = (int) ($settings['max_vendor_leaderboard_limit'] ?? 50);
        $defaultLimit = (int) ($settings['default_vendor_leaderboard_limit'] ?? 10);

        $range = (string) ($filters['range'] ?? $defaultRange);
        $limit = (int) ($filters['limit'] ?? $defaultLimit);
        $limit = min(max($limit, 1), max($maxLimit, 1));

        if ($range === 'custom') {
            $from = Carbon::parse((string) $filters['from'])->startOfDay();
            $to = Carbon::parse((string) $filters['to'])->endOfDay();

            if ($from->gt($to)) {
                throw new InvalidArgumentException('Custom range "from" date must be before or equal to "to" date.');
            }

            return [
                'range' => $range,
                'from' => $from,
                'to' => $to,
                'granularity' => $from->diffInDays($to) > 120 ? 'month' : 'day',
                'limit' => $limit,
            ];
        }

        $definition = $configuredRanges[$range] ?? $configuredRanges[$defaultRange] ?? ['months' => 12, 'granularity' => 'month'];
        $now = now();
        $granularity = (string) ($definition['granularity'] ?? 'day');

        if (isset($definition['months'])) {
            $months = max((int) $definition['months'], 1);
            $from = $now->copy()->startOfMonth()->subMonths($months - 1);
            $to = $now->copy()->endOfDay();
        } else {
            $days = max((int) ($definition['days'] ?? 30), 1);
            $from = $now->copy()->subDays($days - 1)->startOfDay();
            $to = $now->copy()->endOfDay();
        }

        return [
            'range' => $range,
            'from' => $from,
            'to' => $to,
            'granularity' => $granularity,
            'limit' => $limit,
        ];
    }

    /**
     * @param Collection<int, object> $rows
     * @return array{labels: array<int, string>, data: array<int, float|int>}
     */
    private function normalizeSeries(
        CarbonInterface $from,
        CarbonInterface $to,
        string $granularity,
        Collection $rows,
        string $valueKey
    ): array {
        $lookup = $rows->mapWithKeys(function (object $row) use ($valueKey): array {
            return [(string) $row->period => (float) $row->{$valueKey}];
        });

        $labels = [];
        $data = [];

        $periodStep = $granularity === 'month' ? '1 month' : '1 day';
        $cursorStart = $granularity === 'month' ? $from->copy()->startOfMonth() : $from->copy()->startOfDay();
        $cursorEnd = $granularity === 'month' ? $to->copy()->startOfMonth() : $to->copy()->startOfDay();

        /** @var Carbon $point */
        foreach (CarbonPeriod::create($cursorStart, $periodStep, $cursorEnd) as $point) {
            $key = $granularity === 'month' ? $point->format('Y-m') : $point->format('Y-m-d');
            $labels[] = $key;

            $value = $lookup->get($key, 0);
            $data[] = $valueKey === 'orders_count' ? (int) $value : round((float) $value, 2);
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}
