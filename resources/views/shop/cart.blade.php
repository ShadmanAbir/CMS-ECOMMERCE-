@extends('layouts.app')

@section('title', 'Shopping Cart')

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
    $inputBorder = theme('ui.input_border');
    $inputText = theme('ui.input_text');
    $divider = theme('ui.divider');
    $hoverBg = theme('ui.hover_bg');
@endphp
<div class="container mx-auto px-4 py-8">
    <h1 class="font-heading text-3xl md:text-4xl {{ $pgText }} mb-8 tracking-wide">Shopping Cart</h1>

    @if($viewModel->cartItems->count() > 0)
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Cart Items -->
        <div class="lg:col-span-2 space-y-4">
            @foreach($viewModel->cartItems as $item)
            <div class="{{ $cardBg }} border {{ $cardBorder }} p-4 flex items-center space-x-4">
                <!-- Product Image -->
                <div class="w-24 h-24 flex-shrink-0 {{ $inputBg }}">
                    @if($item->product->images->count() > 0)
                        <img src="{{ storage_asset($item->product->images->first()->image_path) }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                    @else
                        <img src="{{ asset('images/placeholder/product_placeholder.jpg') }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                    @endif
                </div>

                <!-- Product Info -->
                <div class="flex-1">
                    <h3 class="font-medium {{ $cardText }}">{{ $item->product->name }}</h3>
                    @if($item->product->volume)
                    <p class="text-xs {{ $muted2 }} mt-1">{{ $item->product->volume }}</p>
                    @endif
                    <p class="text-lg font-bold text-accent mt-2">৳{{ number_format($item->product->getDiscountedPrice(), 2) }}</p>
                </div>

                <!-- Quantity -->
                <div class="flex items-center space-x-2">
                    <button onclick="updateCart({{ $item->id }}, {{ $item->quantity - 1 }})" class="w-8 h-8 border {{ $inputBorder }} hover:border-accent hover:text-accent {{ $muted }} transition">-</button>
                    <input type="number" value="{{ $item->quantity }}" min="1" max="{{ $item->productStock->quantity ?? 999 }}" class="w-14 text-center bg-transparent border {{ $inputBorder }} {{ $inputText }} text-sm py-1 focus:outline-none focus:border-accent" onchange="updateCart({{ $item->id }}, this.value)">
                    <button onclick="updateCart({{ $item->id }}, {{ $item->quantity + 1 }})" class="w-8 h-8 border {{ $inputBorder }} hover:border-accent hover:text-accent {{ $muted }} transition">+</button>
                </div>

                <!-- Subtotal -->
                <div class="text-right">
                    <p class="text-lg font-bold {{ $cardText }}">৳{{ number_format($item->subtotal, 2) }}</p>
                    <button onclick="removeFromCart({{ $item->id }})" class="text-danger text-xs hover:underline mt-2">Remove</button>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Order Summary -->
        <div>
            <div class="{{ $cardBg }} border {{ $cardBorder }} p-6 sticky top-24">
                <h2 class="text-sm font-bold {{ $cardText }} uppercase tracking-widest mb-4">Order Summary</h2>

                <!-- Coupon -->
                <div class="mb-4 pb-4 border-b {{ $divider }}">
                    @if($viewModel->couponCode)
                        <div class="flex items-center justify-between bg-green-500/10 border border-green-500/30 rounded-lg px-3 py-2">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                <span class="font-mono text-sm font-bold text-green-600">{{ $viewModel->couponCode }}</span>
                            </div>
                            <button onclick="removeCoupon()" class="text-xs text-red-400 hover:underline">Remove</button>
                        </div>
                    @else
                        <div class="flex gap-2">
                            <input type="text" id="coupon-code" placeholder="Coupon code" class="flex-1 px-3 py-2 {{ $inputBg }} border {{ $inputBorder }} {{ $inputText }} text-sm uppercase focus:outline-none focus:border-accent transition">
                            <button onclick="applyCoupon()" class="px-4 py-2 bg-accent text-black text-sm font-bold uppercase tracking-wider hover:bg-accent/90 transition">Apply</button>
                        </div>
                        <p id="coupon-error" class="text-red-500 text-xs mt-1 hidden"></p>
                    @endif
                </div>

                <div class="space-y-3 mb-4">
                    <div class="flex justify-between">
                        <span class="{{ $muted }}">Subtotal</span>
                        <span class="{{ $cardText }} font-medium">৳{{ number_format($viewModel->subtotal, 2) }}</span>
                    </div>
                    @if($viewModel->discountAmount > 0)
                    <div class="flex justify-between text-green-500">
                        <span>Discount{{ $viewModel->couponCode ? ' (' . $viewModel->couponCode . ')' : '' }}</span>
                        <span>-৳{{ number_format($viewModel->discountAmount, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="{{ $muted }}">VAT</span>
                        <span class="{{ $cardText }} font-medium">৳{{ number_format($viewModel->vatAmount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="{{ $muted }}">Shipping</span>
                        <span class="{{ $cardText }} font-medium">{{ $viewModel->shippingAmount > 0 ? '৳' . number_format($viewModel->shippingAmount, 2) : 'Free' }}</span>
                    </div>
                    <div class="border-t {{ $divider }} pt-3 flex justify-between text-lg font-bold">
                        <span class="{{ $cardText }}">Total</span>
                        <span class="text-accent">৳{{ number_format($viewModel->payableAmount, 2) }}</span>
                    </div>
                </div>

                <a href="{{ route('checkout.index') }}" class="block w-full bg-accent text-black text-center py-3 hover:bg-accent/90 transition font-bold text-sm uppercase tracking-widest">
                    Proceed to Checkout
                </a>

                <a href="{{ route('home') }}" class="block w-full text-center py-3 mt-3 text-accent hover:underline text-sm">
                    Continue Shopping
                </a>
            </div>
        </div>
    </div>

    @else
    <div class="text-center py-16">
        <svg class="w-24 h-24 mx-auto {{ $muted3 }} mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <h2 class="text-2xl font-heading {{ $pgText }} mb-2 tracking-wide">Your cart is empty</h2>
        <p class="{{ $muted2 }} mb-6">Add some products to get started!</p>
        <a href="{{ route('home') }}" class="inline-block bg-accent text-black px-8 py-3 hover:bg-accent/90 transition font-bold text-sm uppercase tracking-widest">
            Start Shopping
        </a>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
function updateCart(itemId, quantity) {
    if (quantity < 1) return;
    
    fetch(`/cart/update/${itemId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ quantity: quantity })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) location.reload();
    });
}

function removeFromCart(itemId) {
    if (!confirm('Remove this item from cart?')) return;
    
    fetch(`/cart/remove/${itemId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) location.reload();
    });
}

function applyCoupon() {
    const input = document.getElementById('coupon-code');
    const errorEl = document.getElementById('coupon-error');
    const code = input.value.trim();
    if (!code) return;

    fetch('{{ route('cart.coupon.apply') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ code: code })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            errorEl.textContent = data.message;
            errorEl.classList.remove('hidden');
        }
    });
}

function removeCoupon() {
    fetch('{{ route('cart.coupon.remove') }}', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) location.reload();
    });
}
</script>
@endsection
