@extends('layouts.app')
@section('title', 'Coupons & Offers')
@section('content')
<div class="{{ theme('ui.page_bg') }}">
    <div class="py-24 px-4 text-center border-b {{ theme('ui.divider') }}">
        <h1 class="text-5xl md:text-6xl font-heading tracking-wider {{ theme('ui.heading_text') }} mb-4">DEALS & <span class="text-accent">OFFERS</span></h1>
        <p class="{{ theme('ui.muted_text') }} uppercase tracking-widest text-sm mb-8">Exclusive deals and special offers</p>
        <div class="{{ theme('ui.section_bg') }} border {{ theme('ui.card_border') }} rounded-lg p-12 max-w-lg mx-auto">
            <svg class="w-16 h-16 text-accent/30 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
            </svg>
            <p class="{{ theme('ui.muted_text_2') }} text-lg font-heading tracking-wide">COMING SOON</p>
            <p class="{{ theme('ui.muted_text_3') }} text-sm mt-2">Exciting deals and offers are on the way.</p>
        </div>
    </div>
</div>
@endsection
