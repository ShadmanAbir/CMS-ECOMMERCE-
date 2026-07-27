@extends('layouts.app')

@section('title', 'Payment Cancelled')

@section('content')
<div class="{{ theme('ui.page_bg') }} min-h-screen flex items-center justify-center px-4">
    <div class="max-w-lg w-full text-center">
        <!-- Cancel Icon -->
        <div class="w-20 h-20 mx-auto mb-6 border-2 border-red-400 rounded-full flex items-center justify-center">
            <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </div>

        <h1 class="text-4xl font-heading tracking-wider {{ theme('ui.heading_text') }} mb-3">PAYMENT <span class="text-red-400">CANCELLED</span></h1>
        <p class="{{ theme('ui.muted_text') }} mb-2">Your payment was not completed.</p>
        <p class="{{ theme('ui.muted_text_3') }} text-sm mb-8">Order #{{ $order->order_number }}</p>

        <div class="{{ theme('ui.section_bg') }} border {{ theme('ui.card_border') }} p-6 text-left mb-8">
            <p class="{{ theme('ui.muted_text') }} text-sm leading-relaxed">
                Your order has been created but payment is still pending. You can try again or contact support if you need assistance.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('checkout.index') }}" class="bg-accent {{ theme('ui.btn_text') }} px-8 py-3 hover:bg-accent/90 transition font-bold text-sm uppercase tracking-widest">
                Try Again
            </a>
            <a href="{{ route('home') }}" class="border {{ theme('ui.btn_outline_border') }} {{ theme('ui.btn_outline_text') }} px-8 py-3 hover:border-accent hover:text-accent transition font-bold text-sm uppercase tracking-widest">
                Back to Home
            </a>
        </div>
    </div>
</div>
@endsection
