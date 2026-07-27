{{-- Featured Products - Raangalay --}}
@php
    $products = $featuredProducts ?? collect();
@endphp

@if($products->count() > 0)
<section class="mb-12 container mx-auto px-4">
    <div class="flex items-center justify-between mb-8">
        <div>
            <p class="text-primary text-xs font-semibold uppercase tracking-[0.4em] mb-2">Premium Selection</p>
            <h2 class="text-3xl md:text-4xl font-bold text-secondary">Featured Products</h2>
        </div>
        <a href="{{ route('product.search', ['featured' => true]) }}" class="text-primary hover:text-primary/80 transition font-semibold text-sm uppercase tracking-wider flex items-center">
            View All
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
        @foreach($products as $product)
            @include('shop.partials.product-card', ['product' => $product])
        @endforeach
    </div>
</section>
@endif
