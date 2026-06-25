<?php

namespace App\Repositories\Eloquent;

use App\Enums\PaymentStatus;
use App\Repositories\Contracts\AdminAnalyticsRepositoryInterface;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminAnalyticsRepository implements AdminAnalyticsRepositoryInterface
{
    public function totalPaidSales(CarbonInterface $from, CarbonInterface $to): float
    {
        return (float) $this->paidOrdersBaseQuery($from, $to)
            ->sum('orders.grand_total');
    }

    public function monthlyPaidSales(CarbonInterface $from, CarbonInterface $to): Collection
    {
        $expression = $this->periodExpression('payments.paid_at', 'month');

        return $this->paidOrdersBaseQuery($from, $to)
            ->selectRaw($expression.' as period, SUM(orders.grand_total) as total_sales')
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    public function paidSalesByPeriod(CarbonInterface $from, CarbonInterface $to, string $granularity): Collection
    {
        $expression = $this->periodExpression('payments.paid_at', $granularity);

        return $this->paidOrdersBaseQuery($from, $to)
            ->selectRaw($expression.' as period, SUM(orders.grand_total) as total_sales')
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    public function ordersCount(CarbonInterface $from, CarbonInterface $to): int
    {
        return (int) DB::table('orders')
            ->whereBetween('created_at', [$from, $to])
            ->count();
    }

    public function ordersByPeriod(CarbonInterface $from, CarbonInterface $to, string $granularity): Collection
    {
        $expression = $this->periodExpression('created_at', $granularity);

        return DB::table('orders')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw($expression.' as period, COUNT(*) as orders_count')
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    public function orderStatusDistribution(CarbonInterface $from, CarbonInterface $to): Collection
    {
        return DB::table('orders')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->orderBy('status')
            ->get();
    }

    public function vendorPerformance(CarbonInterface $from, CarbonInterface $to, int $limit): Collection
    {
        return $this->paidOrdersBaseQuery($from, $to)
            ->join('vendors', 'vendors.id', '=', 'orders.vendor_id')
            ->selectRaw('vendors.id as vendor_id, vendors.store_name, SUM(orders.grand_total) as total_sales, COUNT(orders.id) as orders_count')
            ->groupBy('vendors.id', 'vendors.store_name')
            ->orderByDesc('total_sales')
            ->orderByDesc('orders_count')
            ->limit($limit)
            ->get();
    }

    public function resolveRangeCurrency(CarbonInterface $from, CarbonInterface $to): string
    {
        $row = $this->paidOrdersBaseQuery($from, $to)
            ->selectRaw('orders.currency, COUNT(*) as aggregate_count')
            ->groupBy('orders.currency')
            ->orderByDesc('aggregate_count')
            ->first();

        return (string) ($row->currency ?? config('analytics.dashboard.default_currency', 'USD'));
    }

    private function paidOrdersBaseQuery(CarbonInterface $from, CarbonInterface $to)
    {
        return DB::table('orders')
            ->join('payments', 'payments.checkout_batch_id', '=', 'orders.checkout_batch_id')
            ->where('payments.status', PaymentStatus::PAID->value)
            ->whereNotNull('payments.paid_at')
            ->whereBetween('payments.paid_at', [$from, $to]);
    }

    private function periodExpression(string $column, string $granularity): string
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            return $granularity === 'month'
                ? "DATE_FORMAT($column, '%Y-%m')"
                : "DATE_FORMAT($column, '%Y-%m-%d')";
        }

        if ($driver === 'sqlite') {
            return $granularity === 'month'
                ? "strftime('%Y-%m', $column)"
                : "strftime('%Y-%m-%d', $column)";
        }

        return $granularity === 'month'
            ? "to_char($column, 'YYYY-MM')"
            : "to_char($column, 'YYYY-MM-DD')";
    }
}
