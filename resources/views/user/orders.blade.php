@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
@php
    $pgText = theme('ui.page_text');
    $cardText = theme('ui.card_text');
    $cardBg = theme('ui.card_bg');
    $cardBorder = theme('ui.card_border');
    $muted = theme('ui.muted_text');
    $muted2 = theme('ui.muted_text_2');
    $muted3 = theme('ui.muted_text_3');
    $inputBg = theme('ui.input_bg');
    $divider = theme('ui.divider');
@endphp
<div class="min-h-screen">
    <div class="max-w-5xl mx-auto px-4 py-12">
        <h1 class="text-4xl font-heading tracking-wider {{ $pgText }} mb-8">MY <span class="text-accent">ORDERS</span></h1>

        {{-- Search by order number --}}
        <form method="GET" action="{{ route('user.orders') }}" class="mb-6">
            @if($status)
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <div class="flex gap-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by order number..."
                       class="flex-1 px-4 py-2.5 text-sm {{ $cardText }} {{ $inputBg }} border {{ $cardBorder }} focus:outline-none focus:border-accent transition">
                <button type="submit" class="px-6 py-2.5 bg-accent text-black text-xs font-bold uppercase tracking-widest hover:bg-accent/90 transition">
                    Search
                </button>
                @if($search)
                <a href="{{ $status ? route('user.orders', ['status' => $status]) : route('user.orders') }}"
                   class="px-4 py-2.5 text-xs font-bold uppercase tracking-widest border {{ $cardText }} {{ $cardBorder }} hover:border-accent/40 transition">
                    Clear
                </a>
                @endif
            </div>
        </form>

        {{-- Status filter tabs --}}
        @php
            $filterTabs = [
                'all' => 'All',
                'pending' => 'Pending',
                'processing' => 'Processing',
                'shipped' => 'Shipped',
                'delivered' => 'Delivered',
                'cancelled' => 'Cancelled',
            ];
            $activeStatus = $status ?: 'all';
        @endphp
        <div class="flex flex-wrap items-center gap-2 mb-8">
            @foreach($filterTabs as $key => $label)
                @php
                    $count = $statusCounts[$key] ?? 0;
                    $tabParams = array_filter(['status' => $key === 'all' ? null : $key, 'search' => $search ?: null]);
                @endphp
                <a href="{{ route('user.orders', $tabParams) }}"
                   class="px-4 py-2 text-xs font-bold uppercase tracking-wider border transition
                          {{ $activeStatus === $key ? 'bg-accent text-black border-accent' : "{$cardText} {$cardBorder} hover:border-accent/40" }}">
                    {{ $label }} ({{ $count }})
                </a>
            @endforeach
        </div>

        @if($orders->count() > 0)
        <div class="space-y-4">
            @foreach($orders as $order)
            <a href="{{ route('user.order.details', $order->order_number) }}" class="block {{ $cardBg }} border {{ $cardBorder }} p-6 hover:border-accent/30 transition-all duration-300 group">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <p class="text-xs {{ $muted2 }} uppercase tracking-widest mb-1">Order</p>
                        <p class="{{ $cardText }} font-heading tracking-wide text-lg">{{ $order->order_number }}</p>
                        <p class="{{ $muted2 }} text-sm mt-1">{{ $order->created_at->format('M d, Y \a\t g:i A') }}</p>
                    </div>
                    <div class="flex items-center gap-6">
                        <div class="text-center">
                            <p class="text-xs {{ $muted2 }} uppercase tracking-widest mb-1">Status</p>
                            <span class="inline-block px-3 py-1 text-xs font-bold uppercase tracking-wider border
                                @if($order->status === 'delivered') text-green-500 border-green-400/30
                                @elseif($order->status === 'cancelled') text-red-500 border-red-400/30
                                @elseif($order->status === 'processing') text-blue-500 border-blue-400/30
                                @elseif($order->status === 'shipped') text-purple-500 border-purple-400/30
                                @else text-yellow-500 border-yellow-400/30 @endif">
                                {{ $order->status }}
                            </span>
                        </div>
                        <div class="text-center">
                            <p class="text-xs {{ $muted2 }} uppercase tracking-widest mb-1">Payment</p>
                            <span class="inline-block px-3 py-1 text-xs font-bold uppercase tracking-wider border
                                @if($order->payment_status === 'paid') text-green-500 border-green-400/30
                                @elseif($order->payment_status === 'failed') text-red-500 border-red-400/30
                                @else text-yellow-500 border-yellow-400/30 @endif">
                                {{ $order->payment_status }}
                            </span>
                        </div>
                        <div class="text-right">
                            <p class="text-xs {{ $muted2 }} uppercase tracking-widest mb-1">Total</p>
                            <p class="text-accent font-bold text-lg">৳{{ number_format($order->total_amount, 2) }}</p>
                        </div>
                        <svg class="w-5 h-5 {{ $muted3 }} group-hover:text-accent transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </div>
                <!-- Items preview -->
                <div class="mt-4 pt-4 border-t {{ $divider }} flex items-center gap-3">
                    @foreach($order->items->take(3) as $item)
                    <div class="w-10 h-10 {{ $inputBg }} flex-shrink-0">
                        @if($item->product && $item->product->images->count() > 0)
                            <img src="{{ storage_asset($item->product->images->first()->image_path) }}" class="w-full h-full object-cover">
                        @else
                            <img src="{{ asset('images/placeholder/product_placeholder.jpg') }}" class="w-full h-full object-cover">
                        @endif
                    </div>
                    @endforeach
                    @if($order->items->count() > 3)
                    <span class="{{ $muted3 }} text-sm">+{{ $order->items->count() - 3 }} more</span>
                    @endif
                    <span class="{{ $muted3 }} text-sm ml-auto">{{ $order->items->sum('quantity') }} item(s)</span>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div class="text-center py-16">
            <svg class="w-24 h-24 mx-auto {{ $muted3 }} mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            @if($search)
            <h2 class="text-2xl font-heading {{ $pgText }} mb-2 tracking-wide">No matching orders</h2>
            <p class="{{ $muted2 }} mb-6">No orders match &ldquo;{{ $search }}&rdquo;. Try a different order number.</p>
            <a href="{{ $status ? route('user.orders', ['status' => $status]) : route('user.orders') }}" class="inline-block bg-accent text-black px-8 py-3 hover:bg-accent/90 transition font-bold text-sm uppercase tracking-widest">
                Clear Search
            </a>
            @elseif($status)
            <h2 class="text-2xl font-heading {{ $pgText }} mb-2 tracking-wide">No {{ $status }} orders</h2>
            <p class="{{ $muted2 }} mb-6">You have no orders with this status.</p>
            <a href="{{ $search ? route('user.orders', ['search' => $search]) : route('user.orders') }}" class="inline-block bg-accent text-black px-8 py-3 hover:bg-accent/90 transition font-bold text-sm uppercase tracking-widest">
                View All Orders
            </a>
            @else
            <h2 class="text-2xl font-heading {{ $pgText }} mb-2 tracking-wide">No orders yet</h2>
            <p class="{{ $muted2 }} mb-6">Start shopping to see your orders here</p>
            <a href="{{ route('home') }}" class="inline-block bg-accent text-black px-8 py-3 hover:bg-accent/90 transition font-bold text-sm uppercase tracking-widest">
                Start Shopping
            </a>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection
