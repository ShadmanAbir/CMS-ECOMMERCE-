{{-- The Ritual - Your Daily Armour --}}
<section class="relative h-[400px] md:h-[500px] overflow-hidden">
    <!-- Fixed silky texture background - very subtle -->
    <div class="absolute inset-0 z-0 bg-fixed bg-center bg-cover opacity-15" style="background-image: url('{{ asset('images/badlab/silky-texture.jpg') }}');"></div>
    <div class="absolute inset-0 z-0 bg-black/80"></div>
    
    <div class="relative h-full flex items-center px-8 md:px-16 lg:px-24 z-10">
        <div class="reveal-left max-w-xl">
            <p class="text-accent text-xs uppercase tracking-[0.5em] mb-3 font-semibold">The Ritual</p>
            <h2 class="font-heading text-white text-3xl md:text-5xl lg:text-6xl tracking-wide mb-4">YOUR DAILY<br>ARMOUR</h2>
            <p class="text-white/60 text-lg mb-6">Every product in the BAD LAB lineup is formulated with active ingredients chosen specifically for men's skin and hair. No fillers. No compromises.</p>
            <a href="{{ route('product.search') }}" class="inline-flex items-center bg-accent text-black px-8 py-3 hover:bg-accent/90 transition-all duration-300 font-bold text-sm uppercase tracking-widest" data-magnetic>
                EXPLORE RANGE
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                </svg>
            </a>
        </div>
    </div>
</section>
