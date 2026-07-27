{{-- Newsletter Subscription - Raangalay --}}
<section class="mb-8 container mx-auto px-4">
    <div class="bg-white rounded-2xl shadow-sm p-10 md:p-16 text-center">
        <!-- Icon -->
        <div class="mb-6">
            <div class="w-20 h-20 mx-auto bg-primary/10 rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>
        
        <h2 class="text-3xl md:text-4xl font-bold text-secondary mb-3">Stay Connected</h2>
        <p class="text-gray-600 text-lg mb-8 max-w-xl mx-auto">Subscribe for exclusive offers, new arrivals, and beauty tips inspired by Bengali tradition.</p>
        
        <form action="{{ route('newsletter.subscribe') }}" method="POST" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
            @csrf
            <input type="email" 
                   name="email" 
                   placeholder="Enter your email" 
                   required
                   value="{{ old('email') }}"
                   class="flex-1 px-6 py-4 border border-gray-300 rounded-full text-secondary placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition">
            <button type="submit" 
                    class="px-8 py-4 bg-primary text-white font-semibold text-sm uppercase tracking-widest hover:bg-primary/90 transition-all duration-300 rounded-full shadow-lg hover:shadow-primary/30">
                Subscribe
            </button>
        </form>
        
        @if(session('newsletter_success'))
            <p class="text-green-600 text-sm mt-3">{{ session('newsletter_success') }}</p>
        @endif
        @if($errors->has('email'))
            <p class="text-danger text-sm mt-3">{{ $errors->first('email') }}</p>
        @endif
        
        <p class="text-gray-400 text-xs mt-4">No spam. Unsubscribe anytime.</p>
    </div>
</section>
