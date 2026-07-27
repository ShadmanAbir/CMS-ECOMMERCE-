@extends('layouts.app')

@section('title', 'My Wishlist')

@section('content')
@php
    $pgText   = theme('ui.page_text');
    $cardBg   = theme('ui.card_bg');
    $cardText = theme('ui.card_text');
    $cardBorder = theme('ui.card_border');
    $muted    = theme('ui.muted_text');
    $muted2   = theme('ui.muted_text_2');
    $divider  = theme('ui.divider');
    $imgBg    = theme('ui.img_placeholder_bg');
@endphp
<div class="min-h-screen">
    <div class="max-w-6xl mx-auto px-4 py-12">
        <h1 class="text-4xl font-heading tracking-wider {{ $pgText }} mb-8">MY <span class="text-accent">WISHLIST</span></h1>

        @if($wishlist->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
            @foreach($wishlist as $item)
            @if($item->product)
            <div class="{{ $cardBg }} border {{ $cardBorder }} group hover:border-accent/30 transition-all duration-300">
                <a href="{{ route('product.show', $item->product->slug) }}" class="block aspect-square {{ $imgBg }} overflow-hidden">
                    @if($item->product->images->count() > 0)
                        <img src="{{ storage_asset($item->product->images->first()->image_path) }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                        <img src="{{ asset('images/placeholder/product_placeholder.jpg') }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                    @endif
                </a>
                <div class="p-4">
                    <a href="{{ route('product.show', $item->product->slug) }}">
                        <h3 class="text-sm {{ $cardText }} font-medium truncate hover:text-accent transition">{{ $item->product->name }}</h3>
                    </a>
                    @if($item->product->volume)
                    <p class="text-xs {{ $muted2 }} mt-1">{{ $item->product->volume }}</p>
                    @endif
                    <div class="flex items-center justify-between mt-3">
                        <p class="text-accent font-bold">৳{{ number_format($item->product->getDiscountedPrice(), 2) }}</p>
                        @if($item->product->isInStock())
                        <button onclick="addToCart({{ $item->product->id }})" class="text-xs bg-accent/10 border border-accent/30 text-accent px-3 py-1 hover:bg-accent hover:text-black transition uppercase tracking-wider">Add</button>
                        @else
                        <span class="text-xs text-red-400 uppercase">Out of Stock</span>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            @endforeach
        </div>

        @if($wishlist->hasPages())
        <div class="mt-8">
            {{ $wishlist->links() }}
        </div>
        @endif

        @else
        <div class="text-center py-16">
            <svg class="w-24 h-24 mx-auto {{ $muted2 }} mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
            </svg>
            <h2 class="text-2xl font-heading {{ $pgText }} mb-2 tracking-wide">Your wishlist is empty</h2>
            <p class="{{ $muted2 }} mb-6">Save products you love to check them out later</p>
            <a href="{{ route('home') }}" class="inline-block bg-accent text-black px-8 py-3 hover:bg-accent/90 transition font-bold text-sm uppercase tracking-widest">
                Start Shopping
            </a>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
function addToCart(productId) {
    fetch('{{ route("cart.add") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ product_id: productId, quantity: 1 })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) alert('Added to cart!');
        else alert(data.message || 'Failed to add to cart');
    })
    .catch(() => alert('Error adding to cart'));
}
</script>
@endsection
