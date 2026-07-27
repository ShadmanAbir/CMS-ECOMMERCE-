@extends('layouts.admin')

@section('title', 'Edit Coupon')
@section('page-title', 'Edit Coupon')

@section('content')
@php
    $aCardBg     = theme('admin.card_bg');
    $aCardBorder = theme('admin.card_border');
    $aCardText   = theme('admin.card_text');
    $aMuted      = theme('admin.muted_text');
    $aBtnPrimary = theme('admin.btn_primary_bg') . ' ' . theme('admin.btn_primary_text');
    $aBtnCancel  = theme('admin.btn_cancel_bg') . ' ' . theme('admin.btn_cancel_text') . ' ' . theme('admin.btn_cancel_hover');
@endphp
<div class="max-w-3xl mx-auto">
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-heading tracking-wide {{ $aCardText }}">
            EDIT <span class="text-accent">{{ $coupon->code }}</span>
        </h1>
        <span class="text-sm {{ $aMuted }}">Used {{ $coupon->usage_count }} time{{ $coupon->usage_count == 1 ? '' : 's' }}</span>
    </div>

    <div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg p-6">
        <form action="{{ route('admin.coupons.update', $coupon->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.coupons._form')

            <div class="flex gap-4 mt-6">
                <button type="submit" class="flex-1 {{ $aBtnPrimary }} py-2 rounded-lg hover:bg-accent/90 transition font-bold uppercase tracking-wider">
                    Update Coupon
                </button>
                <a href="{{ route('admin.coupons.index') }}" class="flex-1 {{ $aBtnCancel }} py-2 rounded-lg transition text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
