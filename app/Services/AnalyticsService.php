<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get revenue summary cards with growth indicators.
     */
    public function revenueSummary(): array
    {
        $today = $this->revenueForPeriod(now()->startOfDay(), now()->endOfDay());
        $yesterday = $this->revenueForPeriod(now()->subDay()->startOfDay(), now()->subDay()->endOfDay());

        $thisWeek = $this->revenueForPeriod(now()->startOfWeek(), now()->endOfWeek());
        $lastWeek = $this->revenueForPeriod(now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek());

        $thisMonth = $this->revenueForPeriod(now()->startOfMonth(), now()->endOfMonth());
        $lastMonth = $this->revenueForPeriod(now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth());

        $allTime = $this->validOrders()->sum('total_amount') ?? 0;

        return [
            'today' => [
                'amount' => (float) $today,
                'growth' => $this->growthPercent($today, $yesterday),
            ],
            'this_week' => [
                'amount' => (float) $thisWeek,
                'growth' => $this->growthPercent($thisWeek, $lastWeek),
            ],
            'this_month' => [
                'amount' => (float) $thisMonth,
                'growth' => $this->growthPercent($thisMonth, $lastMonth),
            ],
            'all_time' => [
                'amount' => (float) $allTime,
                'growth' => null,
            ],
        ];
    }

    /**
     * Get daily revenue data for chart rendering.
     *
     * @param int $days Number of days to look back (7, 30, or 90)
     */
    public function revenueChart(int $days = 7): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $rows = $this->validOrders()
            ->where('created_at', '>=', $start)
            ->select(
                DB::raw("DATE(created_at) as date"),
                DB::raw("SUM(total_amount) as revenue"),
                DB::raw("COUNT(*) as orders")
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $revenue = [];
        $orders = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format($days <= 7 ? 'D d' : 'M d');
            $revenue[] = (float) ($rows[$date]->revenue ?? 0);
            $orders[] = (int) ($rows[$date]->orders ?? 0);
        }

        return [
            'labels' => $labels,
            'revenue' => $revenue,
            'orders' => $orders,
        ];
    }

    /**
     * Get order count breakdown by status.
     */
    public function orderStatusBreakdown(): array
    {
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

        $counts = Order::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $result = [];
        foreach ($statuses as $status) {
            $result[$status] = (int) ($counts[$status] ?? 0);
        }

        return $result;
    }

    /**
     * Get top selling products by revenue.
     */
    public function topProducts(int $limit = 5): Collection
    {
        return OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.status', '!=', 'cancelled')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();
    }

    /**
     * Get order statistics (counts + growth).
     */
    public function orderStats(): array
    {
        $todayOrders = $this->validOrders()
            ->whereDate('created_at', today())
            ->count();

        $yesterdayOrders = $this->validOrders()
            ->whereDate('created_at', today()->subDay())
            ->count();

        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $totalCustomers = User::where('role', 'customer')->count();

        $newCustomersToday = User::where('role', 'customer')
            ->whereDate('created_at', today())
            ->count();

        return [
            'today_orders' => $todayOrders,
            'orders_growth' => $this->growthPercent($todayOrders, $yesterdayOrders),
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'total_customers' => $totalCustomers,
            'new_customers_today' => $newCustomersToday,
        ];
    }

    /**
     * Get quick inventory alerts (low stock products).
     */
    public function lowStockProducts(int $threshold = 5, int $limit = 5): Collection
    {
        return Product::where('status', 'active')
            ->where('stock', '<=', $threshold)
            ->orderBy('stock', 'asc')
            ->limit($limit)
            ->get(['id', 'name', 'sku', 'stock']);
    }

    /**
     * Get coupon redemption stats: totals plus the most-used coupons.
     * Only counts coupons applied to valid (non-cancelled) orders.
     */
    public function couponStats(int $limit = 5): array
    {
        $totalRedemptions = $this->validOrders()->whereNotNull('coupon_code')->count();
        $totalDiscount = (float) $this->validOrders()->whereNotNull('coupon_code')->sum('discount_amount');

        $top = $this->validOrders()
            ->whereNotNull('coupon_code')
            ->select(
                'coupon_code',
                DB::raw('COUNT(*) as redemptions'),
                DB::raw('SUM(discount_amount) as total_discount')
            )
            ->groupBy('coupon_code')
            ->orderByDesc('redemptions')
            ->limit($limit)
            ->get();

        return [
            'total_redemptions' => $totalRedemptions,
            'total_discount' => $totalDiscount,
            'active_coupons' => Coupon::active()->count(),
            'top' => $top,
        ];
    }

    // ─── Private Helpers ─────────────────────────────────────────────

    /**
     * Base query for valid (non-cancelled) orders.
     */
    private function validOrders()
    {
        return Order::where('status', '!=', 'cancelled');
    }

    /**
     * Sum revenue for a date range (excluding cancelled orders).
     */
    private function revenueForPeriod(Carbon $start, Carbon $end): float
    {
        return (float) $this->validOrders()
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_amount');
    }

    /**
     * Calculate growth percentage between current and previous period.
     */
    private function growthPercent(float|int $current, float|int $previous): ?float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
