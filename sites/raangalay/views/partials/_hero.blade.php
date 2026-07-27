{{-- Hero Banner Carousel - Raangalay --}}
@if($banners->count() > 0)
<div class="mb-16">
    <div class="relative w-full h-[500px] md:h-[650px] lg:h-[750px]" x-data="{ current: 0, banners: {{ $banners->count() }} }" x-init="setInterval(() => { current = (current + 1) % banners }, 5000)">
        @foreach($banners as $index => $banner)
        <div x-show="current === {{ $index }}" 
             x-transition:enter="transition ease-out duration-700" 
             x-transition:enter-start="opacity-0 scale-95" 
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-500"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="absolute inset-0">
            @php
                $imagePath = $banner->image;
                if (!file_exists(public_path($imagePath))) {
                    $imagePath = 'images/banner/banner_image.webp';
                }
            @endphp
            <img src="{{ asset($imagePath) }}" 
                 alt="{{ $banner->title ?? theme('name') }}" 
                 class="w-full h-full object-cover">
            
            <!-- Overlay with gradient -->
            <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/50 to-transparent">
                <div class="container mx-auto px-8 lg:px-16 h-full flex items-center">
                    <div class="max-w-3xl">
                        @if($banner->title)
                        <div class="mb-6">
                            <p class="text-white/80 text-xs md:text-sm uppercase tracking-[0.4em] mb-6 font-light">{{ theme('tagline') }}</p>
                            <h2 class="text-white text-5xl md:text-7xl lg:text-8xl font-bold leading-[0.9] mb-6">
                                {{ $banner->title }}
                            </h2>
                            <div class="w-20 h-0.5 bg-primary mt-6 mb-8"></div>
                        </div>
                        @endif
                        @if($banner->subtitle)
                        <p class="text-white/70 text-lg md:text-xl lg:text-2xl mb-8 font-light leading-relaxed max-w-2xl">{{ $banner->subtitle }}</p>
                        @else
                        <p class="text-white/70 text-lg md:text-xl lg:text-2xl mb-8 font-light leading-relaxed max-w-2xl">Discover our exclusive range of premium products rooted in tradition.</p>
                        @endif
                        @if($banner->link)
                        <div class="flex flex-wrap gap-4">
                            <a href="{{ $banner->link }}" 
                               class="inline-flex items-center bg-primary text-white px-10 py-4 rounded-full hover:bg-primary/90 transition-all duration-300 font-semibold text-lg group shadow-2xl hover:shadow-primary/50">
                                SHOP NOW
                                <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m0-4H3"></path>
                                </svg>
                            </a>
                            <a href="{{ route('product.search') }}" 
                               class="inline-flex items-center bg-white/10 backdrop-blur-sm text-white px-8 py-4 rounded-full hover:bg-white/20 transition-all duration-300 font-medium text-lg border border-white/30">
                                VIEW ALL
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        
        <!-- Banner Navigation -->
        <div class="absolute bottom-8 right-8 lg:right-16 flex space-x-3">
            @for($i = 0; $i < $banners->count(); $i++)
            <button @click="current = {{ $i }}" 
                    class="h-12 w-2 rounded-full transition-all duration-300"
                    :class="current === {{ $i }} ? 'bg-primary w-8' : 'bg-white/50 hover:bg-white'">
            </button>
            @endfor
        </div>
        
        <!-- Banner Counter -->
        <div class="absolute bottom-8 left-8 lg:left-16 text-white">
            <span class="text-5xl font-bold" x-text="String(current + 1).padStart(2, '0')">01</span>
            <span class="text-white/60 text-2xl">/{{ $banners->count() }}</span>
        </div>
    </div>
</div>
@else
<!-- Default Hero -->
<div class="mb-16 bg-gradient-to-r from-primary/10 to-primary/5 py-24">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-5xl md:text-7xl font-bold text-secondary mb-4">{{ theme('name') }}</h1>
        <p class="text-xl text-secondary/70 max-w-2xl mx-auto">{{ theme('tagline') }}</p>
        <a href="{{ route('product.search') }}" class="inline-flex items-center mt-8 bg-primary text-white px-10 py-4 rounded-full hover:bg-primary/90 transition font-semibold text-lg">
            SHOP NOW
        </a>
    </div>
</div>
@endif
