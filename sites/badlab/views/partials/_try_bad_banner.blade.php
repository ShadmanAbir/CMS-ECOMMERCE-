{{-- Try BAD Banner - Full width CTA after Most Popular --}}
<section class="w-full bg-white py-16 md:py-24 overflow-hidden relative">
    <!-- Fixed stone background - very subtle -->
    <div class="absolute inset-0 bg-fixed bg-center bg-cover opacity-10" style="background-image: url('{{ asset('images/badlab/stone-texture.jpg') }}');"></div>
    <div class="absolute inset-0 bg-white/80"></div>
    
    <div class="container mx-auto px-4 text-center relative z-10">
        <div class="relative inline-block">
            <!-- Caveman illustration positioned between TRY and BAD -->
            <div class="relative">
                <h2 class="font-heading text-6xl md:text-8xl lg:text-9xl text-black tracking-tight leading-none">
                    TRY <span class="inline-block relative mx-4 md:mx-8">
                        <img src="{{ asset('images/badlab/caveman-illustration.png') }}" alt="BAD LAB" class="inline-block w-24 md:w-40 lg:w-56 h-auto -mb-4 md:-mb-8">
                    </span> BAD
                </h2>
            </div>
            <p class="font-heading text-3xl md:text-5xl lg:text-6xl text-black tracking-tight mt-2 md:mt-4">SEE WHY IT'S GOOD</p>
        </div>
    </div>
</section>
