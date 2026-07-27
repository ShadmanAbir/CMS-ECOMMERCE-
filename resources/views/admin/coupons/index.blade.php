@extends('layouts.admin')

@section('title', 'Coupons')
@section('page-title', 'Coupon Management')

@section('content')
@php
    $aCardBg      = theme('admin.card_bg');
    $aCardBorder  = theme('admin.card_border');
    $aCardText    = theme('admin.card_text');
    $aMuted       = theme('admin.muted_text');
    $aMuted2      = theme('admin.muted_text_2');
    $aMuted3      = theme('admin.muted_text_3');
    $aTableHeadBg = theme('admin.table_head_bg');
    $aTableDiv    = theme('admin.table_divider');
    $aRowDiv      = theme('admin.table_row_divider');
    $aRowHover    = theme('admin.table_row_hover');
    $aBtnPrimary  = theme('admin.btn_primary_bg') . ' ' . theme('admin.btn_primary_text');
@endphp
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-heading tracking-wide {{ $aCardText }}">ALL <span class="text-accent">COUPONS</span></h1>
        <p class="text-sm {{ $aMuted }} mt-1">Create and manage discount codes for your customers.</p>
    </div>
    <a href="{{ route('admin.coupons.create') }}" class="{{ $aBtnPrimary }} px-4 py-2 rounded-lg hover:bg-accent/90 transition font-bold uppercase tracking-wider text-sm">
        Add New Coupon
    </a>
</div>

<div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg overflow-hidden">
    <table class="min-w-full divide-y {{ $aTableDiv }}">
        <thead class="{{ $aTableHeadBg }}">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Code</th>
                <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Discount</th>
                <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Min Order</th>
                <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Usage</th>
                <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Expires</th>
                <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y {{ $aRowDiv }}">
            @forelse($coupons as $coupon)
                <tr class="{{ $aRowHover }} transition">
                    <td class="px-6 py-4">
                        <span class="font-mono text-sm font-bold {{ $aCardText }} bg-accent/10 border border-accent/20 rounded px-2 py-1">{{ $coupon->code }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm font-semibold {{ $aCardText }}">{{ $coupon->display_value }}</td>
                    <td class="px-6 py-4 text-sm {{ $aMuted3 }}">
                        {{ $coupon->min_order_amount ? '৳' . number_format($coupon->min_order_amount, 2) : '—' }}
                    </td>
                    <td class="px-6 py-4 text-sm {{ $aMuted3 }}">
                        {{ $coupon->usage_count }}{{ $coupon->usage_limit ? ' / ' . $coupon->usage_limit : '' }}
                    </td>
                    <td class="px-6 py-4 text-sm {{ $aMuted3 }}">
                        @if($coupon->expires_at)
                            {{ $coupon->expires_at->format('M d, Y') }}
                            @if($coupon->expires_at->isPast())
                                <span class="text-red-400 text-xs">(expired)</span>
                            @endif
                        @else
                            Never
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <form action="{{ route('admin.coupons.toggle-status', $coupon->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-2 py-1 text-xs rounded-full transition
                                @if($coupon->is_active) bg-green-500/20 text-green-600 border border-green-500/30 hover:bg-green-500/30
                                @else bg-gray-200 text-gray-500 border border-gray-300 hover:bg-gray-300
                                @endif">
                                {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium space-x-3">
                        <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="text-accent hover:text-accent/80">Edit</a>
                        <form action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this coupon?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center {{ $aMuted2 }}">
                        <svg class="w-10 h-10 mx-auto mb-3 {{ $aMuted2 }} opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        No coupons yet. Create your first discount code!
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $coupons->links() }}
</div>
@endsection
