@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
@php
    $aCardBg    = theme('admin.card_bg');
    $aCardBorder = theme('admin.card_border');
    $aCardText  = theme('admin.card_text');
    $aMuted     = theme('admin.muted_text');
    $aMuted2    = theme('admin.muted_text_2');
    $aMuted3    = theme('admin.muted_text_3');
    $aHeadText  = theme('admin.heading_text');
    $aBorderDiv = theme('admin.border_divider');
@endphp

{{-- ─── Revenue Summary Cards ─────────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    @php
        $revenueCards = [
            ['label' => "Today's Revenue", 'data' => $revenue['today'], 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'accent'],
            ['label' => 'This Week', 'data' => $revenue['this_week'], 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'color' => 'blue'],
            ['label' => 'This Month', 'data' => $revenue['this_month'], 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'color' => 'green'],
            ['label' => 'All Time', 'data' => $revenue['all_time'], 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', 'color' => 'purple'],
        ];
        $colorMap = ['accent' => 'text-accent bg-accent/10 border-accent/20', 'blue' => 'text-blue-400 bg-blue-500/10 border-blue-500/20', 'green' => 'text-green-400 bg-green-500/10 border-green-500/20', 'purple' => 'text-purple-400 bg-purple-500/10 border-purple-500/20'];
    @endphp

    @foreach($revenueCards as $card)
        <div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="{{ $aMuted }} text-sm uppercase tracking-wider">{{ $card['label'] }}</p>
                    <p class="text-2xl font-heading {{ $aCardText }} mt-1">৳{{ number_format($card['data']['amount'], 2) }}</p>
                    @if($card['data']['growth'] !== null)
                        <p class="text-sm mt-1 {{ $card['data']['growth'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                            {{ $card['data']['growth'] >= 0 ? '↑' : '↓' }} {{ abs($card['data']['growth']) }}% vs prev
                        </p>
                    @endif
                </div>
                <div class="w-12 h-12 {{ $colorMap[$card['color']] }} border rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"></path>
                    </svg>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- ─── Order Stats Row ───────────────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg p-5 flex items-center gap-4">
        <div class="w-10 h-10 bg-orange-500/10 border border-orange-500/20 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
        </div>
        <div>
            <p class="text-xl font-heading {{ $aCardText }}">{{ $orderStats['today_orders'] }}</p>
            <p class="{{ $aMuted }} text-xs uppercase">Orders Today</p>
        </div>
    </div>

    <div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg p-5 flex items-center gap-4">
        <div class="w-10 h-10 bg-yellow-500/10 border border-yellow-500/20 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <p class="text-xl font-heading {{ $aCardText }}">{{ $orderStats['pending_orders'] }}</p>
            <p class="{{ $aMuted }} text-xs uppercase">Pending</p>
        </div>
    </div>

    <div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg p-5 flex items-center gap-4">
        <div class="w-10 h-10 bg-purple-500/10 border border-purple-500/20 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </div>
        <div>
            <p class="text-xl font-heading {{ $aCardText }}">{{ $orderStats['total_customers'] }}</p>
            <p class="{{ $aMuted }} text-xs uppercase">Customers</p>
        </div>
    </div>

    <div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg p-5 flex items-center gap-4">
        <div class="w-10 h-10 bg-green-500/10 border border-green-500/20 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
        </div>
        <div>
            <p class="text-xl font-heading {{ $aCardText }}">{{ $orderStats['new_customers_today'] }}</p>
            <p class="{{ $aMuted }} text-xs uppercase">New Today</p>
        </div>
    </div>
</div>

{{-- ─── Charts Row ────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- Revenue Chart (2/3 width) --}}
    <div class="lg:col-span-2 {{ $aCardBg }} {{ $aCardBorder }} rounded-lg">
        <div class="px-6 py-4 border-b {{ $aBorderDiv }} flex items-center justify-between">
            <h3 class="text-lg font-heading tracking-wide {{ $aCardText }}">REVENUE <span class="text-accent">LAST 7 DAYS</span></h3>
        </div>
        <div class="p-6">
            <canvas id="revenueChart" height="100"></canvas>
        </div>
    </div>

    {{-- Order Status Doughnut (1/3 width) --}}
    <div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg">
        <div class="px-6 py-4 border-b {{ $aBorderDiv }}">
            <h3 class="text-lg font-heading tracking-wide {{ $aCardText }}">ORDER <span class="text-accent">STATUS</span></h3>
        </div>
        <div class="p-6 flex flex-col items-center">
            <canvas id="statusChart" height="180"></canvas>
            <div class="mt-4 grid grid-cols-2 gap-x-6 gap-y-1 text-xs {{ $aMuted }}">
                <span><span class="inline-block w-2 h-2 rounded-full bg-yellow-400 mr-1"></span>Pending ({{ $statusBreakdown['pending'] }})</span>
                <span><span class="inline-block w-2 h-2 rounded-full bg-blue-400 mr-1"></span>Processing ({{ $statusBreakdown['processing'] }})</span>
                <span><span class="inline-block w-2 h-2 rounded-full bg-purple-400 mr-1"></span>Shipped ({{ $statusBreakdown['shipped'] }})</span>
                <span><span class="inline-block w-2 h-2 rounded-full bg-green-400 mr-1"></span>Delivered ({{ $statusBreakdown['delivered'] }})</span>
                <span><span class="inline-block w-2 h-2 rounded-full bg-red-400 mr-1"></span>Cancelled ({{ $statusBreakdown['cancelled'] }})</span>
            </div>
        </div>
    </div>
</div>

{{-- ─── Bottom Row: Top Products + Recent Orders + Low Stock ──── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Top Products --}}
    <div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg">
        <div class="px-6 py-4 border-b {{ $aBorderDiv }}">
            <h3 class="text-lg font-heading tracking-wide {{ $aCardText }}">TOP <span class="text-accent">PRODUCTS</span></h3>
        </div>
        <div class="p-6">
            @if($topProducts->count() > 0)
                <div class="space-y-3">
                    @foreach($topProducts as $i => $tp)
                        <div class="flex items-center justify-between pb-3 border-b {{ $aBorderDiv }} last:border-0">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-accent/10 text-accent text-xs flex items-center justify-center font-bold">{{ $i + 1 }}</span>
                                <div>
                                    <p class="{{ $aCardText }} text-sm font-medium">{{ Str::limit($tp->name, 25) }}</p>
                                    <p class="{{ $aMuted }} text-xs">{{ $tp->total_quantity }} sold</p>
                                </div>
                            </div>
                            <p class="{{ $aCardText }} text-sm font-semibold">৳{{ number_format($tp->total_revenue, 0) }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="{{ $aMuted2 }} text-center py-4 text-sm">No sales data yet</p>
            @endif
        </div>
    </div>

    {{-- Recent Orders --}}
    <div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg">
        <div class="px-6 py-4 border-b {{ $aBorderDiv }}">
            <h3 class="text-lg font-heading tracking-wide {{ $aCardText }}">RECENT <span class="text-accent">ORDERS</span></h3>
        </div>
        <div class="p-6">
            @if($recentOrders->count() > 0)
                <div class="space-y-3">
                    @foreach($recentOrders as $order)
                        <div class="flex justify-between items-center pb-3 border-b {{ $aBorderDiv }} last:border-0">
                            <div>
                                <p class="{{ $aCardText }} text-sm font-medium">{{ $order->order_number }}</p>
                                <p class="{{ $aMuted }} text-xs">{{ $order->user->name ?? 'N/A' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="{{ $aCardText }} text-sm font-semibold">৳{{ number_format($order->total_amount, 0) }}</p>
                                <span class="inline-block px-2 py-0.5 text-xs rounded-full
                                    @if($order->status == 'pending') bg-yellow-500/20 text-yellow-300
                                    @elseif($order->status == 'processing') bg-blue-500/20 text-blue-300
                                    @elseif($order->status == 'shipped') bg-purple-500/20 text-purple-300
                                    @elseif($order->status == 'delivered') bg-green-500/20 text-green-300
                                    @elseif($order->status == 'cancelled') bg-red-500/20 text-red-300
                                    @else bg-gray-200 text-gray-500
                                    @endif">{{ ucfirst($order->status) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="{{ $aMuted2 }} text-center py-4 text-sm">No orders yet</p>
            @endif
        </div>
    </div>

    {{-- Low Stock Alerts --}}
    <div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg">
        <div class="px-6 py-4 border-b {{ $aBorderDiv }}">
            <h3 class="text-lg font-heading tracking-wide {{ $aCardText }}">LOW <span class="text-red-400">STOCK</span></h3>
        </div>
        <div class="p-6">
            @if($lowStock->count() > 0)
                <div class="space-y-3">
                    @foreach($lowStock as $item)
                        <div class="flex justify-between items-center pb-3 border-b {{ $aBorderDiv }} last:border-0">
                            <div>
                                <p class="{{ $aCardText }} text-sm font-medium">{{ Str::limit($item->name, 25) }}</p>
                                <p class="{{ $aMuted }} text-xs">{{ $item->sku }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full {{ $item->stock == 0 ? 'bg-red-500/20 text-red-300' : 'bg-yellow-500/20 text-yellow-300' }}">
                                {{ $item->stock == 0 ? 'Out of stock' : $item->stock . ' left' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="{{ $aMuted2 }} text-center py-4 text-sm">All products well stocked</p>
            @endif
        </div>
    </div>
</div>

{{-- ─── Coupon Performance ────────────────────────────────────── --}}
<div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg mt-6">
    <div class="px-6 py-4 border-b {{ $aBorderDiv }} flex items-center justify-between">
        <h3 class="text-lg font-heading tracking-wide {{ $aCardText }}">COUPON <span class="text-accent">PERFORMANCE</span></h3>
        <a href="{{ route('admin.coupons.index') }}" class="text-xs {{ $aMuted }} hover:text-accent transition">Manage &rarr;</a>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 p-6">
        {{-- Summary stats --}}
        <div class="space-y-4">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-accent/10 border border-accent/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z"></path></svg>
                </div>
                <div>
                    <p class="text-xl font-heading {{ $aCardText }}">{{ number_format($couponStats['total_redemptions']) }}</p>
                    <p class="{{ $aMuted }} text-xs uppercase">Total Redemptions</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-green-500/10 border border-green-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xl font-heading {{ $aCardText }}">৳{{ number_format($couponStats['total_discount'], 2) }}</p>
                    <p class="{{ $aMuted }} text-xs uppercase">Total Discount Given</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-blue-500/10 border border-blue-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <p class="text-xl font-heading {{ $aCardText }}">{{ number_format($couponStats['active_coupons']) }}</p>
                    <p class="{{ $aMuted }} text-xs uppercase">Active Coupons</p>
                </div>
            </div>
        </div>

        {{-- Top coupons (2/3 width) --}}
        <div class="lg:col-span-2">
            <p class="{{ $aMuted }} text-xs uppercase tracking-wider mb-3">Most Used Coupons</p>
            @if($couponStats['top']->count() > 0)
                <div class="space-y-3">
                    @foreach($couponStats['top'] as $i => $c)
                        <div class="flex items-center justify-between pb-3 border-b {{ $aBorderDiv }} last:border-0">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-accent/10 text-accent text-xs flex items-center justify-center font-bold">{{ $i + 1 }}</span>
                                <div>
                                    <p class="{{ $aCardText }} text-sm font-medium font-mono">{{ $c->coupon_code }}</p>
                                    <p class="{{ $aMuted }} text-xs">{{ $c->redemptions }} {{ Str::plural('redemption', $c->redemptions) }}</p>
                                </div>
                            </div>
                            <p class="{{ $aCardText }} text-sm font-semibold">৳{{ number_format($c->total_discount, 0) }} off</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="{{ $aMuted2 }} text-center py-4 text-sm">No coupons redeemed yet</p>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const accent = '{{ theme("colors.accent") }}';

    // Revenue Line Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: @json($chartData['labels']),
            datasets: [{
                label: 'Revenue (৳)',
                data: @json($chartData['revenue']),
                borderColor: accent,
                backgroundColor: accent + '20',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: accent,
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => '৳' + ctx.parsed.y.toLocaleString()
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: '#94a3b8', callback: v => '৳' + v },
                    grid: { color: 'rgba(255,255,255,0.05)' }
                },
                x: {
                    ticks: { color: '#94a3b8' },
                    grid: { display: false }
                }
            }
        }
    });

    // Order Status Doughnut
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'],
            datasets: [{
                data: @json(array_values($statusBreakdown)),
                backgroundColor: ['#facc15', '#60a5fa', '#a78bfa', '#4ade80', '#f87171'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            cutout: '65%',
            plugins: { legend: { display: false } }
        }
    });
});
</script>
@endsection
