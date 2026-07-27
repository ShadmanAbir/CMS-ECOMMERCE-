{{-- Hero Banner Section - BAD LAB --}}
@if($banners->count() > 0)
<div class="w-full h-screen overflow-hidden">
    <div class="relative w-full h-full" x-data="{ current: 0, banners: {{ $banners->count() }} }" x-init="setInterval(() => { current = (current + 1) % banners }, 5000)">
        @foreach($banners as $index => $banner)
        <div x-show="current === {{ $index }}" 
             x-transition:enter="transition ease-out duration-700" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-500"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0 w-full h-full">
            @php
                $imagePath = $banner->image;
                if (!file_exists(public_path($imagePath))) {
                    $imagePath = 'images/banner/banner_image.webp';
                }
            @endphp
            <img src="{{ asset($imagePath) }}" alt="{{ $banner->title ?? theme('name') }}" class="w-full h-full object-cover">
        </div>
        @endforeach
        
        <!-- Banner Navigation Dots -->
        @if($banners->count() > 1)
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex space-x-2 z-10">
            @for($i = 0; $i < $banners->count(); $i++)
            <button @click="current = {{ $i }}" 
                    class="h-1.5 w-1.5 rounded-full transition-all duration-300"
                    :class="current === {{ $i }} ? 'bg-white w-6' : 'bg-white/50 hover:bg-white/80'">
            </button>
            @endfor
        </div>
        @endif
    </div>
</div>
@else
<!-- Default Hero Banner -->
<div class="w-full overflow-hidden">
    <img src="{{ asset(theme('images_path') . '/banner-caveman.png') }}" alt="{{ theme('name') }}" class="w-full h-auto object-cover">
</div>
@endif
