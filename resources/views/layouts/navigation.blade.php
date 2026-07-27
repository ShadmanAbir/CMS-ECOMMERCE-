<nav class="bg-secondary text-white border-b border-white/10" x-data="{ mobileMenu: false }">
    <div class="container mx-auto px-4">
        <div class="hidden md:flex items-center justify-between h-12">
            <!-- Categories Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center space-x-2 px-4 py-2 hover:text-accent transition text-sm uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    <span>All Categories</span>
                </button>

                <div x-show="open" @click.away="open = false" class="absolute top-full left-0 mt-1 w-64 bg-secondary text-white rounded shadow-lg z-50 border border-white/10">
                    @foreach($navCategories as $category)
                        <a href="{{ route('category.show', $category->slug) }}" class="block px-4 py-2 text-sm text-white/80 hover:bg-white/5 hover:text-accent transition">
                            {{ $category->name }}
                        </a>
                        @if($category->children->count() > 0)
                            <div class="pl-8">
                                @foreach($category->children as $child)
                                    <a href="{{ route('category.show', $child->slug) }}" class="block px-4 py-2 text-xs text-white/60 hover:bg-white/5 hover:text-accent transition">
                                        {{ $child->name }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="flex items-center space-x-6">
                <a href="{{ route('home') }}" class="text-sm hover:text-accent transition uppercase tracking-wider">Home</a>
                <a href="{{ route('product.search', ['q' => '']) }}" class="text-sm hover:text-accent transition uppercase tracking-wider">All Products</a>
                <a href="{{ route('about') }}" class="text-sm hover:text-accent transition uppercase tracking-wider">About</a>
                <a href="{{ route('contact') }}" class="text-sm hover:text-accent transition uppercase tracking-wider">Contact</a>
            </div>
        </div>
    </div>
</nav>
