@extends('layouts.app')
@section('title', 'Vendors')
@section('content')
<div class="{{ theme('ui.page_bg') }}">
    <div class="py-24 px-4 text-center border-b {{ theme('ui.divider') }}">
        <h1 class="text-5xl md:text-6xl font-heading tracking-wider {{ theme('ui.heading_text') }} mb-4">OUR <span class="text-accent">PARTNERS</span></h1>
        <p class="{{ theme('ui.muted_text') }} uppercase tracking-widest text-sm mb-8">The brands and suppliers we work with</p>
        <div class="{{ theme('ui.section_bg') }} border {{ theme('ui.card_border') }} rounded-lg p-12 max-w-lg mx-auto">
            <svg class="w-16 h-16 text-accent/30 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <p class="{{ theme('ui.muted_text_2') }} text-lg font-heading tracking-wide">COMING SOON</p>
            <p class="{{ theme('ui.muted_text_3') }} text-sm mt-2">Partner information will be available soon.</p>
        </div>
    </div>
</div>
@endsection
