{{-- Most Popular Product Grid - Large cards with bold names (from badlabco.com explore page) --}}
@php
    // $popularProducts provided by HomepageComposer
    $popImages = [
        'hair care' => asset('images/badlab/prod-colt45.png'),
        'hair styling' => asset('images/badlab/prod-likeaboss.png'),
        'body care' => asset('images/badlab/prod-caveman.png'),
        'facial care' => asset('images/badlab/prod-babyfaced.jpg'),
        'skin care' => asset('images/badlab/prod-depthcharged.jpg'),
    ];
@endphp

@if($popularProducts->count() > 0)
<section class="mb-20 bg-black py-16">
    <div class="container mx-auto px-4">
        <div class="reveal text-center mb-12">
            <p class="text-accent text-xs font-semibold uppercase tracking-[0.4em] mb-2">The Essentials</p>
            <h2 class="font-heading text-3xl md:text-5xl text-white tracking-wide">MOST POPULAR</h2>
            <div class="w-16 h-0.5 bg-accent mx-auto mt-4"></div>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-6 stagger-children">
            @foreach($popularProducts as $product)
            <a href="{{ route('product.show', $product->slug) }}" class="popular-product-card group block">
                <div class="pop-image">
                    @if($product->images->count() > 0)
                        <img src="{{ storage_asset($product->images->first()->image_path) }}" alt="{{ $product->name }}">
                    @else
                        @php
                            $fallback = $popImages[strtolower($product->category->name ?? '')] ?? asset('images/badlab/prod-colt45.png');
                        @endphp
                        <img src="{{ $fallback }}" alt="{{ $product->name }}">
                    @endif
                </div>
                <div class="pop-info">
                    <h3 class="pop-name group-hover:text-accent transition-colors">{{ strtoupper(\Illuminate\Support\Str::after($product->name, 'Bad Lab ')) }}</h3>
                    <p class="pop-desc">{{ $product->category->name ?? '' }}</p>
                </div>
            </a>
            @endforeach
        </div>
        
        <div class="text-center mt-10">
            <a href="{{ route('product.search') }}" class="inline-flex items-center px-8 py-3 border border-white/30 text-white text-sm font-bold uppercase tracking-widest hover:bg-accent hover:border-accent hover:text-black transition-all">
                VIEW ALL PRODUCTS
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </div>
</section>
@endif
