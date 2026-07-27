@extends('layouts.app')

@section('title', 'Order Details')

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
        <a href="{{ route('user.orders') }}" class="inline-flex items-center {{ $muted2 }} hover:text-accent transition text-sm mb-6">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Back to Orders
        </a>

        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-heading tracking-wider {{ $pgText }}">ORDER <span class="text-accent">{{ $order->order_number }}</span></h1>
                <p class="{{ $muted2 }} text-sm mt-1">{{ $order->created_at->format('M d, Y \a\t g:i A') }}</p>
            </div>
            <div class="mt-4 md:mt-0 flex gap-3">
                <span class="px-4 py-2 text-xs font-bold uppercase tracking-wider border
                    @if($order->status === 'delivered') text-green-500 border-green-400/30
                    @elseif($order->status === 'cancelled') text-red-500 border-red-400/30
                    @elseif($order->status === 'processing') text-blue-500 border-blue-400/30
                    @elseif($order->status === 'shipped') text-purple-500 border-purple-400/30
                    @else text-yellow-500 border-yellow-400/30 @endif">
                    {{ $order->status }}
                </span>
                <span class="px-4 py-2 text-xs font-bold uppercase tracking-wider border
                    @if($order->payment_status === 'paid') text-green-500 border-green-400/30
                    @elseif($order->payment_status === 'failed') text-red-500 border-red-400/30
                    @elseif($order->payment_status === 'refunded') text-purple-500 border-purple-400/30
                    @else text-yellow-500 border-yellow-400/30 @endif">
                    {{ $order->payment_status }}
                </span>
                @if(in_array($order->status, ['pending', 'processing']))
                <form action="{{ route('user.order.cancel', $order->order_number) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order? Stock will be restored.');">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-xs font-bold uppercase tracking-wider border border-red-400/30 text-red-500 hover:bg-red-400/10 transition">
                        Cancel Order
                    </button>
                </form>
                @endif
            </div>
        </div>

        <!-- Delivery Tracking -->
        @php
            $flow = ['pending' => 'Placed', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered'];
            $flowKeys = array_keys($flow);
            $isCancelled = $order->status === 'cancelled';
            $currentIndex = $isCancelled ? -1 : array_search($order->status, $flowKeys);
            $statusLabels = ['pending' => 'Order Placed', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'];
        @endphp
        <div class="{{ $cardBg }} border {{ $cardBorder }} p-6 mb-8">
            <h2 class="text-sm font-bold {{ $cardText }} uppercase tracking-widest mb-6">Delivery Tracking</h2>

            @if($isCancelled)
                <div class="flex items-center gap-3 mb-6">
                    <span class="w-10 h-10 rounded-full bg-red-500/15 border border-red-400/40 flex items-center justify-center text-red-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </span>
                    <div>
                        <p class="text-red-500 font-bold uppercase tracking-wider text-sm">Order Cancelled</p>
                        <p class="{{ $muted2 }} text-xs">This order was cancelled and will not be delivered.</p>
                    </div>
                </div>
            @else
                {{-- Progress Tracker --}}
                <div class="flex items-center justify-between mb-8">
                    @foreach($flow as $key => $label)
                        @php $done = $loop->index <= $currentIndex; $isCurrent = $loop->index === $currentIndex; @endphp
                        <div class="flex-1 flex flex-col items-center relative">
                            @unless($loop->first)
                                <div class="absolute top-4 right-1/2 w-full h-0.5 {{ $loop->index <= $currentIndex ? 'bg-accent' : $divider }}"></div>
                            @endunless
                            <span class="relative z-10 w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2
                                {{ $done ? 'bg-accent border-accent text-white' : "{$inputBg} ".$cardBorder." {$muted3}" }}">
                                @if($done)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                @else
                                    {{ $loop->iteration }}
                                @endif
                            </span>
                            <span class="mt-2 text-xs uppercase tracking-wider {{ $isCurrent ? 'text-accent font-bold' : ($done ? $cardText : $muted3) }}">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Timeline --}}
            @if($order->statusHistory->count() > 0)
                <div class="border-t {{ $divider }} pt-6 space-y-4">
                    @foreach($order->statusHistory as $entry)
                        <div class="flex items-start gap-3">
                            <span class="mt-1 w-2 h-2 rounded-full flex-shrink-0 {{ $entry->status === 'cancelled' ? 'bg-red-500' : 'bg-accent' }}"></span>
                            <div class="flex-1">
                                <p class="{{ $cardText }} text-sm font-medium">{{ $statusLabels[$entry->status] ?? ucfirst($entry->status) }}</p>
                                @if($entry->note)<p class="{{ $muted2 }} text-xs">{{ $entry->note }}</p>@endif
                            </div>
                            <span class="{{ $muted3 }} text-xs whitespace-nowrap">{{ $entry->created_at->format('M d, Y g:i A') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Order Items -->
            <div class="lg:col-span-2">
                <div class="{{ $cardBg }} border {{ $cardBorder }}">
                    <h2 class="text-sm font-bold {{ $cardText }} uppercase tracking-widest p-6 pb-4">Items</h2>
                    <div class="divide-y {{ $divider }}">
                        @foreach($order->items as $item)
                        <div class="p-6 flex items-center gap-4">
                            <div class="w-16 h-16 flex-shrink-0 {{ $inputBg }}">
                                @if($item->product && $item->product->images->count() > 0)
                                    <img src="{{ storage_asset($item->product->images->first()->image_path) }}" class="w-full h-full object-cover">
                                @else
                                    <img src="{{ asset('images/placeholder/product_placeholder.jpg') }}" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="{{ $cardText }} font-medium truncate">{{ $item->product->name ?? 'Product unavailable' }}</h3>
                                @if($item->variant)
                                <p class="text-xs text-accent">Variant: {{ $item->variant->name }}</p>
                                @endif
                                @if($item->size)<p class="text-xs {{ $muted2 }}">Size: {{ $item->size }}</p>@endif
                                @if($item->color)<p class="text-xs {{ $muted2 }}">Color: {{ $item->color }}</p>@endif
                                <p class="text-xs {{ $muted2 }}">Qty: {{ $item->quantity }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-accent font-bold">৳{{ number_format($item->subtotal, 2) }}</p>
                                <p class="text-xs {{ $muted3 }}">৳{{ number_format($item->price, 2) }} each</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="space-y-6">
                <!-- Totals -->
                <div class="{{ $cardBg }} border {{ $cardBorder }} p-6">
                    <h2 class="text-sm font-bold {{ $cardText }} uppercase tracking-widest mb-4">Summary</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="{{ $muted }}">Subtotal</span>
                            <span class="{{ $cardText }}">৳{{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        @if($order->discount_amount > 0)
                        <div class="flex justify-between text-green-500">
                            <span>Discount</span>
                            <span>-৳{{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="{{ $muted }}">VAT</span>
                            <span class="{{ $cardText }}">৳{{ number_format($order->vat_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="{{ $muted }}">Shipping</span>
                            <span class="{{ $cardText }}">৳{{ number_format($order->shipping_amount, 2) }}</span>
                        </div>
                        <div class="border-t {{ $divider }} pt-3 flex justify-between text-lg font-bold">
                            <span class="{{ $cardText }}">Total</span>
                            <span class="text-accent">৳{{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Info -->
                <div class="{{ $cardBg }} border {{ $cardBorder }} p-6">
                    <h2 class="text-sm font-bold {{ $cardText }} uppercase tracking-widest mb-4">Payment</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="{{ $muted }}">Method</span>
                            <span class="{{ $cardText }} uppercase">{{ $order->payment_method }}</span>
                        </div>
                        @if($order->transaction_id)
                        <div class="flex justify-between">
                            <span class="{{ $muted }}">Transaction ID</span>
                            <span class="{{ $cardText }} font-mono text-xs">{{ $order->transaction_id }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Shipping Address -->
                @if($order->shippingAddress)
                <div class="{{ $cardBg }} border {{ $cardBorder }} p-6">
                    <h2 class="text-sm font-bold {{ $cardText }} uppercase tracking-widest mb-4">Shipping Address</h2>
                    <p class="{{ $muted }} text-sm leading-relaxed">{{ $order->shippingAddress->full_address }}</p>
                </div>
                @endif

                @if($order->notes)
                <div class="{{ $cardBg }} border {{ $cardBorder }} p-6">
                    <h2 class="text-sm font-bold {{ $cardText }} uppercase tracking-widest mb-4">Notes</h2>
                    <p class="{{ $muted }} text-sm">{{ $order->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
