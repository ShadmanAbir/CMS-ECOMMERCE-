{{-- Newsletter Subscription Section --}}
<section class="py-16 md:py-24 relative overflow-hidden">
    <div class="absolute inset-0 bg-secondary"></div>
    <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-2xl mx-auto text-center">
            <!-- Bell Icon -->
            <div class="mb-6">
                <svg class="w-16 h-16 mx-auto text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            
            <h2 class="font-heading text-4xl md:text-5xl lg:text-6xl text-white tracking-wide mb-4">JOIN THE LAB</h2>
            <p class="text-white/60 text-lg mb-8">Subscribe to get exclusive offers, early access to new products, and grooming tips straight from the lab.</p>
            
            <form action="{{ route('newsletter.subscribe') }}" method="POST" class="flex flex-col sm:flex-row gap-4 max-w-md mx-auto">
                @csrf
                <input type="email" 
                       name="email" 
                       placeholder="Enter your email" 
                       required
                       value="{{ old('email') }}"
                       class="flex-1 px-6 py-4 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/40 focus:outline-none focus:border-accent transition">
                <button type="submit" 
                        class="px-8 py-4 bg-accent text-black font-bold text-sm uppercase tracking-widest hover:bg-accent/90 transition-all duration-300 rounded-lg">
                    SUBSCRIBE
                </button>
            </form>
            
            @if(session('newsletter_success'))
                <p class="text-green-400 text-sm mt-3">{{ session('newsletter_success') }}</p>
            @endif
            @if($errors->has('email'))
                <p class="text-red-400 text-sm mt-3">{{ $errors->first('email') }}</p>
            @endif
            
            <p class="text-white/30 text-xs mt-4">No spam. Unsubscribe anytime.</p>
        </div>
    </div>
</section>
