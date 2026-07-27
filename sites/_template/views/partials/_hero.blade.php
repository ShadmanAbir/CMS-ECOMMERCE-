<!-- Hero Section -->
<section class="{{ theme('ui.page_bg') }} py-20">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-5xl font-heading {{ theme('ui.heading_text') }} mb-6">
            Welcome to {{ theme('name') }}
        </h1>
        <p class="text-xl {{ theme('ui.muted_text') }} mb-8">
            {{ theme('tagline') }}
        </p>
        <a href="{{ route('category.show', 'all') }}" 
           class="inline-block px-8 py-3 bg-primary {{ theme('ui.btn_text') }} rounded-lg font-semibold hover:opacity-90 transition">
            Shop Now
        </a>
    </div>
</section>
