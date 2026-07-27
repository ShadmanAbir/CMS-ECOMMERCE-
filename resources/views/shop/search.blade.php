@extends('layouts.app')

@section('title', ($pageTitle ?? 'Search Results') . ' - ' . theme('name'))

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
@endphp
<div class="container mx-auto px-4 py-8">
    <!-- Search Header -->
    <div class="mb-8 pb-6 border-b {{ $divider }}">
        <h1 class="font-heading text-3xl md:text-4xl {{ $pgText }} mb-2 tracking-wide">{{ $pageTitle ?? 'Search Results' }}</h1>
        @if($keyword)
        <p class="{{ $muted }}">Showing results for "<span class="text-accent font-semibold">{{ $keyword }}</span>"</p>
        @endif
        <p class="{{ $muted3 }} text-sm mt-1">{{ $products->total() }} product{{ $products->total() !== 1 ? 's' : '' }} found</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Sidebar Filters -->
        <aside class="lg:col-span-1">
            <div class="{{ $cardBg }} border {{ $cardBorder }} p-6 sticky top-24 space-y-6">
                <!-- Search Box -->
                <form action="{{ route('product.search') }}" method="GET">
                    @if($categoryId)
                    <input type="hidden" name="category" value="{{ $categoryId }}">
                    @endif
                    <div class="relative">
                        <input type="text" name="q" value="{{ $keyword }}" placeholder="Search products..."
                               class="w-full px-4 py-2.5 {{ $inputBg }} border {{ $inputBorder }} rounded {{ $inputText }} text-sm focus:outline-none focus:border-accent transition pr-10">
                        <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 {{ $muted2 }} hover:text-accent transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </button>
                    </div>
                </form>

                <!-- Categories -->
                <div>
                    <h3 class="text-sm font-bold {{ $cardText }} uppercase tracking-widest mb-3">Categories</h3>
                    <ul class="space-y-1.5">
                        <li>
                            <a href="{{ route('product.search', array_merge(request()->query(), ['category' => null])) }}"
                               class="text-sm transition {{ !$categoryId ? 'text-accent font-medium' : $muted . ' hover:text-accent' }}">
                                All Categories
                            </a>
                        </li>
                        @foreach($categories as $category)
                        <li>
                            <a href="{{ route('product.search', array_merge(request()->query(), ['category' => $category->id])) }}"
                               class="text-sm transition {{ $categoryId == $category->id ? 'text-accent font-medium' : $muted . ' hover:text-accent' }}">
                                {{ $category->name }}
                            </a>
                            @if($category->children->count() > 0)
                            <ul class="ml-3 mt-1 space-y-1">
                                @foreach($category->children as $child)
                                <li>
                                    <a href="{{ route('product.search', array_merge(request()->query(), ['category' => $child->id])) }}"
                                       class="text-xs transition {{ $categoryId == $child->id ? 'text-accent font-medium' : $muted3 . ' hover:' . $muted }}">
                                        {{ $child->name }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Price Range -->
                <div>
                    <h3 class="text-sm font-bold {{ $cardText }} uppercase tracking-widest mb-3">Price Range</h3>
                    <form action="{{ route('product.search') }}" method="GET" class="space-y-3">
                        @if($keyword)
                        <input type="hidden" name="q" value="{{ $keyword }}">
                        @endif
                        @if($categoryId)
                        <input type="hidden" name="category" value="{{ $categoryId }}">
                        @endif
                        <div class="flex gap-2">
                            <input type="number" name="min_price" value="{{ $minPrice }}" placeholder="Min"
                                   class="w-full px-3 py-2 {{ $inputBg }} border {{ $inputBorder }} rounded {{ $inputText }} text-sm focus:outline-none focus:border-accent">
                            <input type="number" name="max_price" value="{{ $maxPrice }}" placeholder="Max"
                                   class="w-full px-3 py-2 {{ $inputBg }} border {{ $inputBorder }} rounded {{ $inputText }} text-sm focus:outline-none focus:border-accent">
                        </div>
                        <button type="submit" class="w-full {{ $cardBg }} border {{ $cardBorder }} {{ $cardText }} py-2 text-sm rounded hover:border-accent transition">Apply Price</button>
                    </form>
                </div>

                <!-- Sort -->
                <div>
                    <h3 class="text-sm font-bold {{ $cardText }} uppercase tracking-widest mb-3">Sort By</h3>
                    <form action="{{ route('product.search') }}" method="GET">
                        @if($keyword)
                        <input type="hidden" name="q" value="{{ $keyword }}">
                        @endif
                        @if($categoryId)
                        <input type="hidden" name="category" value="{{ $categoryId }}">
                        @endif
                        @if($minPrice)
                        <input type="hidden" name="min_price" value="{{ $minPrice }}">
                        @endif
                        @if($maxPrice)
                        <input type="hidden" name="max_price" value="{{ $maxPrice }}">
                        @endif
                        <select name="sort" onchange="this.form.submit()"
                                class="w-full px-3 py-2 {{ $inputBg }} border {{ $inputBorder }} rounded {{ $inputText }} text-sm focus:outline-none focus:border-accent">
                            <option value="latest" {{ $sortBy === 'latest' ? 'selected' : '' }}>Latest</option>
                            <option value="newest" {{ $sortBy === 'newest' ? 'selected' : '' }}>Newest First</option>
                            <option value="price_low" {{ $sortBy === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_high" {{ $sortBy === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="name" {{ $sortBy === 'name' ? 'selected' : '' }}>Name: A to Z</option>
                            <option value="rating" {{ $sortBy === 'rating' ? 'selected' : '' }}>Top Rated</option>
                        </select>
                    </form>
                </div>

                <!-- Quick Filters -->
                <div>
                    <h3 class="text-sm font-bold {{ $cardText }} uppercase tracking-widest mb-3">Quick Filters</h3>
                    <div class="space-y-2">
                        <a href="{{ route('product.search', array_merge(request()->query(), ['filter' => 'on_sale'])) }}"
                           class="block px-3 py-2 text-sm border {{ $cardBorder }} rounded transition {{ request('filter') === 'on_sale' ? 'bg-accent/10 border-accent text-accent' : $muted . ' hover:border-accent/50 hover:' . $cardText }}">
                            On Sale
                        </a>
                        <a href="{{ route('product.search', array_merge(request()->query(), ['filter' => 'new_arrivals'])) }}"
                           class="block px-3 py-2 text-sm border {{ $cardBorder }} rounded transition {{ request('filter') === 'new_arrivals' ? 'bg-accent/10 border-accent text-accent' : $muted . ' hover:border-accent/50 hover:' . $cardText }}">
                            New Arrivals
                        </a>
                        <a href="{{ route('product.search', array_merge(request()->query(), ['filter' => 'best_seller'])) }}"
                           class="block px-3 py-2 text-sm border {{ $cardBorder }} rounded transition {{ request('filter') === 'best_seller' ? 'bg-accent/10 border-accent text-accent' : $muted . ' hover:border-accent/50 hover:' . $cardText }}">
                            Best Sellers
                        </a>
                    </div>
                </div>

                {{-- Dynamic per-site attribute filters --}}
                @include('shop.partials._attribute_filters')

                <!-- Clear Filters -->
                @if($keyword || $categoryId || $minPrice || $maxPrice || request('filter') || !empty($attrFilters) || $sortBy !== 'latest')
                <div>
                    <a href="{{ route('product.search') }}" class="block text-center px-3 py-2 text-sm text-red-500 border border-red-400/30 rounded hover:bg-red-400/10 transition">
                        Clear All Filters
                    </a>
                </div>
                @endif
            </div>
        </aside>

        <!-- Products Grid -->
        <div class="lg:col-span-3">
            @if($products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                @foreach($products as $product)
                    @include('shop.partials.product-card', ['product' => $product])
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="flex justify-center">
                {{ $products->links() }}
            </div>
            @else
            <div class="{{ $cardBg }} border {{ $cardBorder }} p-12 text-center">
                <svg class="w-24 h-24 mx-auto {{ $muted3 }} mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <h3 class="text-xl font-semibold {{ $cardText }} mb-2">No products found</h3>
                <p class="{{ $muted2 }}">Try a different search term or adjust your filters</p>
                <a href="{{ route('product.search') }}" class="inline-block mt-4 px-6 py-2 bg-accent text-black font-bold text-sm uppercase tracking-wider hover:bg-accent/90 transition">Clear Filters</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
