<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\AnalyticsService;

class DashboardController extends Controller
{
    public function __construct(
        protected AnalyticsService $analytics
    ) {}

    public function index()
    {
        $revenue = $this->analytics->revenueSummary();
        $orderStats = $this->analytics->orderStats();
        $chartData = $this->analytics->revenueChart(7);
        $statusBreakdown = $this->analytics->orderStatusBreakdown();
        $topProducts = $this->analytics->topProducts(5);
        $lowStock = $this->analytics->lowStockProducts(5, 5);
        $couponStats = $this->analytics->couponStats(5);
        $recentOrders = Order::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'revenue',
            'orderStats',
            'chartData',
            'statusBreakdown',
            'topProducts',
            'lowStock',
            'couponStats',
            'recentOrders'
        ));
    }
}
