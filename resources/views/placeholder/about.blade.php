@extends('layouts.app')

@section('title', 'About Us')

@section('content')
<!-- Page Loader -->
<div class="page-loader">
    <div class="loader-text">{{ theme('name') }}</div>
</div>

<div class="{{ theme('ui.page_bg') }}">
    <!-- Hero -->
    <div class="relative py-32 px-4 text-center border-b {{ theme('ui.divider') }} overflow-hidden">
        <div class="absolute inset-0 noise-overlay z-[2]"></div>
        <div class="particles-container z-[1]"></div>
        <div class="relative z-10">
            <h1 class="hero-title text-5xl md:text-6xl font-heading tracking-wider {{ theme('ui.heading_text') }} mb-4">ABOUT <span class="text-gradient-animated">{{ theme('name') }}</span></h1>
            <p class="hero-subtitle {{ theme('ui.muted_text') }} uppercase tracking-widest text-sm max-w-2xl mx-auto">{{ theme('tagline') }}</p>
        </div>
    </div>

    <!-- Brand Story -->
    <div class="max-w-5xl mx-auto px-4 py-20">
        <div class="grid md:grid-cols-2 gap-16 items-center mb-24">
            <div class="reveal-left">
                <h2 class="text-3xl font-heading tracking-wide {{ theme('ui.heading_text') }} mb-6">OUR <span class="text-accent">ORIGIN</span></h2>
                <p class="{{ theme('ui.muted_text') }} leading-relaxed mb-4">
                    Founded with a clear purpose: to create products that actually work for those who don't settle.
                </p>
                <p class="{{ theme('ui.muted_text') }} leading-relaxed mb-4">
                    We're not your average brand. We're where quality meets authenticity, where every product is crafted for the demands of modern life.
                </p>
                <p class="{{ theme('ui.muted_text') }} leading-relaxed">
                    Welcome to {{ theme('name') }}.
                </p>
            </div>
            <div class="reveal-right">
                <div class="relative overflow-hidden rounded-lg group">
                    <div class="w-full h-80 {{ theme('ui.img_placeholder_bg') }} flex items-center justify-center">
                        <svg class="w-16 h-16 {{ theme('ui.muted_text_3') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                    <div class="absolute bottom-4 left-4">
                        <p class="text-accent text-xs uppercase tracking-widest font-semibold">Our Story</p>
                        <p class="{{ theme('ui.muted_text') }} text-sm">Where it all begins</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Philosophy -->
        <div class="mb-24">
            <div class="reveal text-center mb-12">
                <h2 class="text-3xl font-heading tracking-wide {{ theme('ui.heading_text') }} mb-4">OUR <span class="text-accent">PHILOSOPHY</span></h2>
                <div class="w-20 h-0.5 bg-accent mx-auto"></div>
            </div>
            <div class="grid md:grid-cols-3 gap-6 stagger-children">
                <div class="relative overflow-hidden {{ theme('ui.section_bg') }} border {{ theme('ui.card_border') }} rounded-lg p-8 text-center group hover:border-accent/30 transition-all duration-500">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-accent/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="text-accent text-5xl font-heading mb-4">01</div>
                    <h3 class="{{ theme('ui.heading_text') }} font-bold mb-3 uppercase tracking-wide">Quality First</h3>
                    <p class="{{ theme('ui.muted_text') }} text-sm leading-relaxed">Every product is crafted with carefully selected ingredients that deliver real results.</p>
                </div>
                <div class="relative overflow-hidden {{ theme('ui.section_bg') }} border {{ theme('ui.card_border') }} rounded-lg p-8 text-center group hover:border-accent/30 transition-all duration-500">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-accent/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="text-accent text-5xl font-heading mb-4">02</div>
                    <h3 class="{{ theme('ui.heading_text') }} font-bold mb-3 uppercase tracking-wide">No Nonsense</h3>
                    <p class="{{ theme('ui.muted_text') }} text-sm leading-relaxed">No fillers, no fluff, no empty claims. Just effective formulations that do what they say.</p>
                </div>
                <div class="relative overflow-hidden {{ theme('ui.section_bg') }} border {{ theme('ui.card_border') }} rounded-lg p-8 text-center group hover:border-accent/30 transition-all duration-500">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-accent/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="text-accent text-5xl font-heading mb-4">03</div>
                    <h3 class="{{ theme('ui.heading_text') }} font-bold mb-3 uppercase tracking-wide">Made for You</h3>
                    <p class="{{ theme('ui.muted_text') }} text-sm leading-relaxed">Designed specifically for your needs. Thoughtful, effective, and authentic personal care.</p>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="reveal text-center">
            <a href="{{ route('home') }}" class="inline-block bg-accent {{ theme('ui.btn_text') }} px-10 py-4 font-heading tracking-wider text-lg hover:bg-accent/90 transition-all duration-300 group" data-magnetic>
                SHOP NOW
                <svg class="w-5 h-5 inline-block ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                </svg>
            </a>
        </div>
    </div>
</div>
@endsection
