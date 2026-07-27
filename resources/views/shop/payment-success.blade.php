@extends('layouts.app')

@section('title', 'Order Confirmed')

@section('content')
<div class="{{ theme('ui.page_bg') }} min-h-screen flex items-center justify-center px-4">
    <div class="max-w-lg w-full text-center">
        <!-- Success Icon -->
        <div class="w-20 h-20 mx-auto mb-6 border-2 border-green-400 rounded-full flex items-center justify-center">
            <svg class="w-10 h-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        <h1 class="text-4xl font-heading tracking-wider {{ theme('ui.heading_text') }} mb-3">ORDER <span class="text-accent">CONFIRMED</span></h1>
        <p class="{{ theme('ui.muted_text') }} mb-2">Thank you for your order!</p>
        <p class="{{ theme('ui.muted_text_3') }} text-sm mb-8">Order #{{ $order->order_number }}</p>

        <!-- Order Summary -->
        <div class="{{ theme('ui.section_bg') }} border {{ theme('ui.card_border') }} p-6 text-left mb-8">
            <h2 class="text-sm font-bold {{ theme('ui.heading_text') }} uppercase tracking-widest mb-4">Order Summary</h2>

            <div class="space-y-3 mb-4">
                @foreach($order->items as $item)
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 flex-shrink-0 {{ theme('ui.img_placeholder_bg') }}">
                        @if($item->product && $item->product->images->count() > 0)
                            <img src="{{ storage_asset($item->product->images->first()->image_path) }}" class="w-full h-full object-cover">
                        @else
                            <img src="{{ asset('images/placeholder/product_placeholder.jpg') }}" class="w-full h-full object-cover">
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm {{ theme('ui.heading_text') }} truncate">{{ $item->product->name ?? 'Product unavailable' }}</p>
                        <p class="text-xs {{ theme('ui.muted_text_2') }}">Qty: {{ $item->quantity }}</p>
                    </div>
                    <p class="text-sm text-accent font-bold">৳{{ number_format($item->subtotal, 2) }}</p>
                </div>
                @endforeach
            </div>

            <div class="border-t {{ theme('ui.divider') }} pt-4 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="{{ theme('ui.muted_text') }}">Subtotal</span>
                    <span class="{{ theme('ui.heading_text') }}">৳{{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="{{ theme('ui.muted_text') }}">VAT</span>
                    <span class="{{ theme('ui.heading_text') }}">৳{{ number_format($order->vat_amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="{{ theme('ui.muted_text') }}">Shipping</span>
                    <span class="{{ theme('ui.heading_text') }}">৳{{ number_format($order->shipping_amount, 2) }}</span>
                </div>
                <div class="border-t {{ theme('ui.divider') }} pt-2 flex justify-between text-lg font-bold">
                    <span class="{{ theme('ui.heading_text') }}">Total</span>
                    <span class="text-accent">৳{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t {{ theme('ui.divider') }} text-sm">
                <div class="flex justify-between">
                    <span class="{{ theme('ui.muted_text') }}">Payment</span>
                    <span class="{{ theme('ui.heading_text') }} uppercase">{{ $order->payment_method }}</span>
                </div>
                <div class="flex justify-between mt-1">
                    <span class="{{ theme('ui.muted_text') }}">Status</span>
                    <span class="text-yellow-400 uppercase">{{ $order->status }}</span>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('user.orders') }}" class="bg-accent {{ theme('ui.btn_text') }} px-8 py-3 hover:bg-accent/90 transition font-bold text-sm uppercase tracking-widest">
                View My Orders
            </a>
            <a href="{{ route('home') }}" class="border {{ theme('ui.btn_outline_border') }} {{ theme('ui.btn_outline_text') }} px-8 py-3 hover:border-accent hover:text-accent transition font-bold text-sm uppercase tracking-widest">
                Continue Shopping
            </a>
        </div>
    </div>
</div>
@endsection
