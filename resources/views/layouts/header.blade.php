@php
    $hBg      = theme('header.bg_class');
    $hText    = theme('header.text_class');
    $hBorder  = theme('header.border_class');
    $hMuted   = theme('header.muted_text_class');
    $hMuted2  = theme('header.muted_text_class_2');
    $hMuted3  = theme('header.muted_text_class_3');
    $hInputBg = theme('header.input_bg_class');
    $hInputBorder = theme('header.input_border_class');
    $hInputPh = theme('header.input_placeholder_class');
    $hHoverBg = theme('header.hover_bg_class');
    $soBg          = theme('ui.search_overlay_bg');
    $soText        = theme('ui.search_overlay_text');
    $soMuted       = theme('ui.search_overlay_muted');
    $soBorder      = theme('ui.search_overlay_border');
    $soInputBg     = theme('ui.search_overlay_input_bg');
    $soPlaceholder = theme('ui.search_overlay_placeholder');
@endphp
<div x-data="{ navOpen: false, searchOpen: false }">

<!-- Slide-Out Left Navbar -->
<div x-show="navOpen" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="-translate-x-full"
     x-transition:enter-end="translate-x-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="translate-x-0"
     x-transition:leave-end="-translate-x-full"
     class="fixed inset-y-0 left-0 w-80 {{ $hBg }} backdrop-blur-lg z-[100] border-r {{ $hBorder }} overflow-y-auto"
     style="display: none;">
    
    <!-- Nav Header -->
    <div class="flex items-center justify-between px-6 py-5 border-b {{ $hBorder }}">
        <span class="font-heading text-xl {{ $hText }} tracking-wider">MENU</span>
        <button @click="navOpen = false" class="p-2 hover:text-accent transition {{ $hMuted }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    
    <!-- Nav Links -->
    <nav class="px-6 py-6 space-y-1">
        <a href="{{ route('home') }}" @click="navOpen = false" class="block py-3 text-sm font-medium {{ $hMuted2 }} hover:text-accent transition tracking-wider uppercase border-b {{ $hBorder }}">Home</a>
        
        <!-- Shop with subcategories -->
        <div x-data="{ shopOpen: false }">
            <button @click="shopOpen = !shopOpen" class="flex items-center justify-between w-full py-3 text-sm font-medium {{ $hMuted2 }} hover:text-accent transition tracking-wider uppercase border-b {{ $hBorder }}">
                <span>Shop</span>
                <svg class="w-4 h-4 transform transition-transform duration-200" :class="{ 'rotate-90': shopOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            <div x-show="shopOpen" x-transition class="pl-4 space-y-1 py-2">
                <a href="{{ route('product.search', ['q' => '']) }}" @click="navOpen = false" class="block py-2 text-sm {{ $hMuted }} hover:text-accent transition tracking-wider uppercase">All Products</a>
                @foreach($navCategories as $category)
                    <a href="{{ route('category.show', $category->slug) }}" @click="navOpen = false" class="block py-2 text-sm {{ $hMuted }} hover:text-accent transition tracking-wider uppercase">{{ $category->name }}</a>
                    @if($category->children->count() > 0)
                        <div class="pl-4 space-y-1">
                            @foreach($category->children as $child)
                                <a href="{{ route('category.show', $child->slug) }}" @click="navOpen = false" class="block py-1.5 text-xs {{ $hMuted3 }} hover:text-accent transition tracking-wider uppercase">{{ $child->name }}</a>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        
        <a href="{{ route('about') }}" @click="navOpen = false" class="block py-3 text-sm font-medium {{ $hMuted2 }} hover:text-accent transition tracking-wider uppercase border-b {{ $hBorder }}">About</a>
        <a href="{{ route('contact') }}" @click="navOpen = false" class="block py-3 text-sm font-medium {{ $hMuted2 }} hover:text-accent transition tracking-wider uppercase border-b {{ $hBorder }}">Contact</a>
        
        @auth
            <div class="pt-4 mt-4 border-t {{ $hBorder }} space-y-1">
                <a href="{{ route('user.orders') }}" @click="navOpen = false" class="flex items-center justify-between py-2 text-sm {{ $hMuted }} hover:text-accent transition tracking-wider uppercase">
                    <span>My Orders</span>
                    @if($orderCount > 0)
                    <span class="bg-accent text-black text-xs rounded-full h-4 min-w-4 px-1 flex items-center justify-center font-bold">{{ $orderCount }}</span>
                    @endif
                </a>
                <a href="{{ route('user.profile') }}" @click="navOpen = false" class="block py-2 text-sm {{ $hMuted }} hover:text-accent transition tracking-wider uppercase">Profile</a>
                <a href="{{ route('user.wishlist') }}" @click="navOpen = false" class="block py-2 text-sm {{ $hMuted }} hover:text-accent transition tracking-wider uppercase">Wishlist</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left py-2 text-sm {{ $hMuted }} hover:text-danger transition tracking-wider uppercase">Logout</button>
                </form>
            </div>
        @else
            <div class="pt-4 mt-4 border-t {{ $hBorder }}">
                <a href="{{ route('login') }}" @click="navOpen = false" class="block py-3 text-center bg-accent text-black font-bold text-sm uppercase tracking-widest hover:bg-accent/90 transition">JOIN / LOGIN</a>
            </div>
        @endauth
    </nav>
</div>

<!-- Overlay -->
<div x-show="navOpen" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black/60 z-[99]"
     @click="navOpen = false"
     style="display: none;">
</div>

<!-- Search Popup -->
<div x-show="searchOpen" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 {{ $soBg }} backdrop-blur-sm z-[100] flex items-start justify-center pt-24"
     @click.away="searchOpen = false"
     style="display: none;">
    
    <div class="w-full max-w-2xl px-4 relative" @click.stop x-data="{
        query: '',
        results: [],
        loading: false,
        debounceTimer: null,
        async search() {
            if (this.query.length < 2) {
                this.results = [];
                return;
            }
            this.loading = true;
            try {
                const response = await fetch(`/live-search?q=${encodeURIComponent(this.query)}`);
                const data = await response.json();
                this.results = data.products || [];
            } catch (error) {
                console.error('Search error:', error);
            } finally {
                this.loading = false;
            }
        },
        handleInput() {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => this.search(), 300);
        }
    }">
        <!-- Close Button -->
        <button @click="searchOpen = false" class="absolute -top-12 right-4 md:right-0 p-2 {{ $soMuted }} hover:{{ $soText }} transition" aria-label="Close search">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        
        <!-- Search Input -->
        <div class="relative">
            <input type="text" 
                   x-model="query"
                   @input="handleInput()"
                   placeholder="Search products..." 
                   autofocus
                   autocomplete="off"
                   class="w-full px-6 py-4 {{ $soInputBg }} border {{ $soBorder }} rounded-lg {{ $soText }} text-lg {{ $soPlaceholder }} focus:outline-none focus:border-accent transition">
            <div class="absolute right-4 top-1/2 -translate-y-1/2">
                <svg x-show="loading" class="w-6 h-6 text-accent animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg x-show="!loading" class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>
        
        <!-- Live Search Results -->
        <div x-show="results.length > 0" x-transition class="mt-4 bg-secondary border {{ $hBorder }} rounded-lg overflow-hidden max-h-96 overflow-y-auto">
            <template x-for="product in results" :key="product.id">
                <a :href="`/product/${product.slug}`" @click="searchOpen = false" class="flex items-center gap-4 p-4 {{ $hHoverBg }} transition border-b {{ $hBorder }} last:border-0">
                    <img :src="product.image" :alt="product.name" class="w-16 h-16 object-cover rounded">
                    <div class="flex-1 min-w-0">
                        <h4 class="{{ $hText }} font-medium truncate" x-text="product.name"></h4>
                        <p class="{{ $hMuted }} text-sm" x-text="product.category"></p>
                        <p class="text-accent font-bold mt-1" x-text="`৳${product.price}`"></p>
                    </div>
                </a>
            </template>
        </div>
        
        <!-- No Results -->
        <div x-show="query.length >= 2 && results.length === 0 && !loading" x-transition class="mt-4 text-center {{ $soMuted }} text-sm py-8">
            No products found
        </div>
        
        <p x-show="query.length < 2" class="{{ $soMuted }} opacity-50 text-xs mt-3 text-center uppercase tracking-widest">Type at least 2 characters to search</p>
    </div>
</div>

<!-- Main Header -->
<header class="{{ $hBg }} border-b {{ $hBorder }} fixed top-0 left-0 right-0 z-50">
    <div class="w-full px-3 md:px-6">
        <div class="flex items-center justify-between h-16 md:h-20">
            
            <!-- Left: Menu + Search Buttons -->
            <div class="flex items-center space-x-2 -ml-2">
                <button @click="navOpen = true" class="p-2 hover:text-accent transition {{ $hMuted2 }}" aria-label="Menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <button @click="searchOpen = true" class="p-2 hover:text-accent transition {{ $hMuted2 }}" aria-label="Search">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
            </div>

            <!-- Center: Logo (SVG) -->
            <div class="absolute left-1/2 -translate-x-1/2">
                <a href="{{ route('home') }}" class="flex items-center">
                    <!-- Logo (theme-driven) -->
                    <img src="{{ asset(theme('logo.path')) }}" alt="{{ theme('logo.alt') }}" class="h-10 md:h-12 w-auto">
                </a>
            </div>

            <!-- Right: Cart, User -->
            <div class="flex items-center space-x-3 -mr-2">
                <!-- Cart -->
                <a href="{{ route('cart.index') }}" class="relative p-2 hover:text-accent transition {{ $hMuted2 }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="absolute -top-1 -right-1 bg-accent text-black text-xs rounded-full h-4 w-4 flex items-center justify-center font-bold">
                        {{ $cartCount }}
                    </span>
                </a>

                <!-- Wishlist (only when logged in) -->
                @auth
                <a href="{{ route('user.wishlist') }}" class="hidden md:block relative p-2 hover:text-accent transition {{ $hMuted2 }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    <span class="absolute -top-1 -right-1 bg-accent text-black text-xs rounded-full h-4 w-4 flex items-center justify-center font-bold">
                        {{ $wishlistCount }}
                    </span>
                </a>
                @endauth

                <!-- User Menu -->
                @auth
                <div class="relative hidden md:block" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center p-2 hover:text-accent transition {{ $hMuted2 }}">
                        <div class="w-7 h-7 bg-accent text-black rounded-full flex items-center justify-center font-bold text-xs">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-secondary rounded shadow-lg py-2 z-50 border {{ $hBorder }}">
                        <a href="{{ route('user.orders') }}" class="flex items-center justify-between px-4 py-2 text-sm {{ $hMuted2 }} {{ $hHoverBg }} hover:text-accent">
                            <span>My Orders</span>
                            @if($orderCount > 0)
                            <span class="bg-accent text-black text-xs rounded-full h-4 min-w-4 px-1 flex items-center justify-center font-bold">{{ $orderCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('user.profile') }}" class="block px-4 py-2 text-sm {{ $hMuted2 }} {{ $hHoverBg }} hover:text-accent">Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm {{ $hHoverBg }} hover:text-danger {{ $hMuted2 }}">Logout</button>
                        </form>
                    </div>
                </div>
                @else
                <a href="{{ route('login') }}" class="hidden md:flex items-center px-3 py-1.5 border border-accent text-accent hover:bg-accent hover:text-black transition text-xs font-semibold tracking-wider uppercase">
                    JOIN
                </a>
                @endauth
            </div>
        </div>
    </div>
</header>

</div><!-- end x-data wrapper -->
