@extends('layouts.admin')

@section('title', 'Orders')
@section('page-title', 'Order Management')

@section('content')
@php
    $aCardBg      = theme('admin.card_bg');
    $aCardBorder  = theme('admin.card_border');
    $aCardText    = theme('admin.card_text');
    $aMuted       = theme('admin.muted_text');
    $aMuted2      = theme('admin.muted_text_2');
    $aMuted3      = theme('admin.muted_text_3');
    $aTableHeadBg = theme('admin.table_head_bg');
    $aTableDiv    = theme('admin.table_divider');
    $aRowDiv      = theme('admin.table_row_divider');
    $aRowHover    = theme('admin.table_row_hover');
    $aInputBg     = theme('admin.input_bg');
    $aInputBorder = theme('admin.input_border');
    $aInputText   = theme('admin.input_text');

    $tabs = [
        null         => 'All',
        'pending'    => 'Pending',
        'processing' => 'Processing',
        'shipped'    => 'Shipped',
        'delivered'  => 'Delivered',
        'cancelled'  => 'Cancelled',
    ];

    $totalCount = $statusCounts->sum();

    // Badge palette keyed by status (mirrors the dashboard palette).
    $badge = [
        'pending'    => 'bg-yellow-500/15 text-yellow-600 border border-yellow-500/30',
        'processing' => 'bg-blue-500/15 text-blue-600 border border-blue-500/30',
        'shipped'    => 'bg-purple-500/15 text-purple-600 border border-purple-500/30',
        'delivered'  => 'bg-green-500/15 text-green-600 border border-green-500/30',
        'cancelled'  => 'bg-red-500/15 text-red-600 border border-red-500/30',
    ];

    $payBadge = [
        'pending'  => 'bg-yellow-500/15 text-yellow-600 border border-yellow-500/30',
        'paid'     => 'bg-green-500/15 text-green-600 border border-green-500/30',
        'refunded' => 'bg-gray-500/15 text-gray-600 border border-gray-500/30',
        'failed'   => 'bg-red-500/15 text-red-600 border border-red-500/30',
    ];
@endphp

<div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
    <div>
        <h1 class="text-2xl font-heading tracking-wide {{ $aCardText }}">ORDER <span class="text-accent">MANAGEMENT</span></h1>
        <p class="text-sm {{ $aMuted }} mt-1">Track, fulfil and update every customer order.</p>
    </div>

    <!-- Search + Date Range -->
    <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-wrap items-center gap-3">
        @if($status)
            <input type="hidden" name="status" value="{{ $status }}">
        @endif
        <div class="relative">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 {{ $aMuted2 }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input type="text" name="q" value="{{ $search }}" placeholder="Search order # or customer…"
                   class="pl-9 pr-4 py-2 w-64 rounded-lg {{ $aInputBg }} border {{ $aInputBorder }} {{ $aInputText }} text-sm focus:outline-none focus:ring-2 focus:ring-accent/40 transition">
        </div>
        <div class="flex items-center gap-2">
            <input type="date" name="from" value="{{ $from }}" title="From date"
                   class="px-3 py-2 rounded-lg {{ $aInputBg }} border {{ $aInputBorder }} {{ $aInputText }} text-sm focus:outline-none focus:ring-2 focus:ring-accent/40 transition">
            <span class="{{ $aMuted2 }} text-sm">to</span>
            <input type="date" name="to" value="{{ $to }}" title="To date"
                   class="px-3 py-2 rounded-lg {{ $aInputBg }} border {{ $aInputBorder }} {{ $aInputText }} text-sm focus:outline-none focus:ring-2 focus:ring-accent/40 transition">
        </div>
        <button type="submit" class="px-4 py-2 bg-accent text-white rounded-lg text-sm font-semibold hover:bg-accent/90 transition">Filter</button>
        @if($from || $to)
            <a href="{{ route('admin.orders.index', array_filter(['status' => $status, 'q' => $search])) }}" class="text-sm {{ $aMuted2 }} hover:text-accent transition">Clear dates</a>
        @endif
    </form>
</div>

<!-- Status filter tabs -->
<div class="mb-6 flex flex-wrap gap-2">
    @foreach($tabs as $key => $label)
        @php
            $count = $key === null ? $totalCount : ($statusCounts[$key] ?? 0);
            $isActive = $status === $key || ($key === null && !$status);
            $tabParams = array_filter(['status' => $key, 'q' => $search ?: null, 'from' => $from ?: null, 'to' => $to ?: null]);
        @endphp
        <a href="{{ route('admin.orders.index', $tabParams) }}"
           class="group inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold uppercase tracking-wider transition
                  {{ $isActive ? 'bg-accent text-white shadow-sm' : $aCardBg . ' ' . $aCardBorder . ' ' . $aMuted3 . ' hover:text-accent hover:border-accent/40' }}">
            {{ $label }}
            <span class="text-xs px-1.5 py-0.5 rounded-full {{ $isActive ? 'bg-white/20 text-white' : 'bg-accent/10 text-accent' }}">{{ $count }}</span>
        </a>
    @endforeach
</div>

<div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg overflow-hidden">
    <table class="min-w-full divide-y {{ $aTableDiv }}">
        <thead class="{{ $aTableHeadBg }}">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Order</th>
                <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Customer</th>
                <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Items</th>
                <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Total</th>
                <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Payment</th>
                <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y {{ $aRowDiv }}">
            @forelse($orders as $order)
                <tr class="{{ $aRowHover }} transition">
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="text-sm font-bold text-accent hover:underline">{{ $order->order_number }}</a>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium {{ $aCardText }}">{{ $order->user->name ?? 'Guest' }}</div>
                        <div class="text-xs {{ $aMuted2 }}">{{ $order->user->email ?? '—' }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm {{ $aMuted3 }}">{{ $order->created_at->format('M d, Y') }}</td>
                    <td class="px-6 py-4 text-sm {{ $aMuted3 }}">{{ $order->items_count }} item{{ $order->items_count == 1 ? '' : 's' }}</td>
                    <td class="px-6 py-4 text-sm font-semibold {{ $aCardText }}">৳{{ number_format($order->total_amount, 2) }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-block px-2 py-1 text-xs rounded-full {{ $payBadge[$order->payment_status] ?? 'bg-gray-200 text-gray-500' }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-block px-2 py-1 text-xs rounded-full {{ $badge[$order->status] ?? 'bg-gray-200 text-gray-500' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="text-accent hover:text-accent/80">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center {{ $aMuted2 }}">
                        <svg class="w-10 h-10 mx-auto mb-3 {{ $aMuted2 }} opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        No orders found{{ $search ? ' for "' . $search . '"' : '' }}.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $orders->links() }}
</div>
@endsection
