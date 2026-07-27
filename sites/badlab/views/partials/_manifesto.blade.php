{{-- Manifesto / Brand Story Section - from badlabco.com --}}
<section class="py-16 md:py-24 relative overflow-hidden">
    <div class="absolute inset-0 bg-fixed bg-center bg-cover" style="background-image: url('{{ asset('images/badlab/grunge.jpg') }}'); background-color: #111;"></div>
    <div class="absolute inset-0 bg-black/60"></div>
    
    <div class="container mx-auto px-4 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 items-center">
            <!-- Brand Story Text - Left Column -->
            <div class="manifesto-text reveal">
                <p class="text-lg md:text-xl text-white/50 italic mb-4">Spiritually, we were founded since the beginning of time.</p>
                
                <p class="text-base md:text-lg text-white/70 leading-relaxed mb-4">To seek new frontiers and boldly go where no other personal care brand has gone before.</p>
                
                <p class="text-base md:text-lg text-white/70 leading-relaxed mb-4">With carefully selected and formulated ingredients especially for men, BAD LAB is <span class="highlight">Man</span>-Gineered for the man who's all walk and little talk.</p>
                
                <p class="text-base md:text-lg text-white/70 leading-relaxed mb-4">The day is out there and not in the bathroom. So, wash like a man and Seize the Day.</p>
                
                <p class="font-heading text-2xl md:text-3xl text-white tracking-wider mt-6">WE ARE BAD LAB.</p>
            </div>
            
            <!-- Caveman Illustration - Right Column -->
            <div class="reveal-scale flex justify-center md:justify-end">
                <img src="{{ asset('images/badlab/caveman-illustration.png') }}" alt="BAD LAB Caveman" class="w-80 md:w-[450px] h-auto float-image opacity-100 brightness-125">
            </div>
        </div>
    </div>
</section>
