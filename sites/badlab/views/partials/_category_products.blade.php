{{-- Category-wise Product Sections with Banners - BAD LAB --}}
@php
    $parentCategories = $parentCategories ?? \App\Models\Category::active()->parents()->with('children')->get();
    $bannerImages = $categoryImageMap ?? [];
    
    $bannerTaglines = [
        'hair care' => ['title' => 'STYLE YOUR WAY', 'subtitle' => 'From clay to pomade, find your hold. Engineered for every hair type and style.'],
        'body care' => ['title' => 'BUILT DIFFERENT', 'subtitle' => 'Shower gels, deodorants, and 3-in-1 formulas. One routine, zero compromises.'],
        'facial care' => ['title' => 'FACE THE WORLD', 'subtitle' => 'Cleansers, moisturizers, and treatments. Keep your game face on.'],
    ];
@endphp

@foreach($parentCategories as $parentCategory)
@php
    $childCategories = $parentCategory->children()->active()->get();
    $categoryProducts = \App\Models\Product::whereHas('category', function($q) use ($childCategories) {
            $q->whereIn('id', $childCategories->pluck('id'));
        })
        ->with(['category', 'images'])
        ->take(6)
        ->get();
    
    $categoryNameLower = strtolower($parentCategory->name);
    $bannerImage = $bannerImages[$categoryNameLower] ?? asset('images/badlab/cat-hair.png');
    $bannerInfo = $bannerTaglines[$categoryNameLower] ?? ['title' => 'EXPLORE', 'subtitle' => 'Discover the full range.'];
@endphp

@if($categoryProducts->count() > 0)
<!-- {{ $parentCategory->name }} Category Section -->
<section class="mb-0">
    <!-- Parallax Banner -->
    <div class="parallax-divider">
        <div class="parallax-bg" style="background-image: url('{{ $bannerImage }}');"></div>
        <div class="parallax-overlay bg-black/70"></div>
        <div class="parallax-content h-full flex items-center justify-center text-center px-4" data-parallax-speed="0.15">
            <div>
                <p class="text-accent text-xs uppercase tracking-[0.5em] mb-3 font-semibold">{{ $parentCategory->name }} Collection</p>
                <h2 class="font-heading text-white text-4xl md:text-5xl lg:text-6xl tracking-wide mb-4">{{ $bannerInfo['title'] }}</h2>
                <p class="text-white/50 max-w-lg mx-auto">{{ $bannerInfo['subtitle'] }}</p>
            </div>
        </div>
    </div>
    
    <!-- Products Grid -->
    <div class="container mx-auto px-4 py-16">
        <div class="reveal flex items-center justify-between mb-10">
            <div>
                <p class="text-accent text-xs font-semibold uppercase tracking-[0.4em] mb-2">{{ $parentCategory->name }}</p>
                <h3 class="font-heading text-2xl md:text-3xl lg:text-4xl text-white tracking-wide">{{ $parentCategory->name }} Essentials</h3>
            </div>
            <a href="{{ route('category.show', $parentCategory->slug) }}" class="text-accent hover:text-accent/80 transition font-semibold text-sm uppercase tracking-wider flex items-center group">
                View All
                <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 stagger-children">
            @foreach($categoryProducts as $product)
                @include('shop.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
@endif
@endforeach
