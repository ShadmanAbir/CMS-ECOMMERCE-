@extends('layouts.admin')

@section('title', 'Reviews')
@section('page-title', 'Product Reviews')

@section('content')
@php
    $aCardBg    = theme('admin.card_bg');
    $aCardBorder = theme('admin.card_border');
    $aCardText  = theme('admin.card_text');
    $aMuted     = theme('admin.muted_text');
    $aMuted2    = theme('admin.muted_text_2');
    $aBorderDiv = theme('admin.border_divider');
    $aInputBg   = theme('admin.input_bg');
@endphp

{{-- Filter Tabs --}}
<div class="flex items-center gap-2 mb-6">
    @php
        $tabs = [
            'pending' => "Pending ({$counts['pending']})",
            'approved' => "Approved ({$counts['approved']})",
            'all' => "All ({$counts['all']})",
        ];
    @endphp
    @foreach($tabs as $key => $label)
        <a href="{{ route('admin.reviews.index', ['filter' => $key]) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition
                  {{ $filter === $key ? 'bg-accent text-white' : "{$aCardBg} {$aCardBorder} {$aMuted} hover:text-white" }}">
            {{ $label }}
        </a>
    @endforeach
</div>

{{-- Reviews Table --}}
<div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg overflow-hidden">
    @if($reviews->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b {{ $aBorderDiv }}">
                        <th class="px-6 py-4 text-xs uppercase tracking-wider {{ $aMuted }}">Product</th>
                        <th class="px-6 py-4 text-xs uppercase tracking-wider {{ $aMuted }}">Customer</th>
                        <th class="px-6 py-4 text-xs uppercase tracking-wider {{ $aMuted }}">Rating</th>
                        <th class="px-6 py-4 text-xs uppercase tracking-wider {{ $aMuted }}">Review</th>
                        <th class="px-6 py-4 text-xs uppercase tracking-wider {{ $aMuted }}">Status</th>
                        <th class="px-6 py-4 text-xs uppercase tracking-wider {{ $aMuted }}">Date</th>
                        <th class="px-6 py-4 text-xs uppercase tracking-wider {{ $aMuted }}">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y {{ $aBorderDiv }}">
                    @foreach($reviews as $review)
                        <tr class="hover:bg-white/5 transition">
                            <td class="px-6 py-4">
                                <p class="{{ $aCardText }} text-sm font-medium">{{ Str::limit($review->product->name ?? 'Deleted Product', 30) }}</p>
                                <p class="{{ $aMuted }} text-xs">{{ $review->product->sku ?? '' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="{{ $aCardText }} text-sm">{{ $review->user->name ?? 'Anonymous' }}</p>
                                @if($review->is_verified_purchase)
                                    <span class="inline-flex items-center gap-1 text-xs text-green-400">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                        Verified
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-yellow-400 text-sm">{{ $review->star_rating }}</span>
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                @if($review->title)
                                    <p class="{{ $aCardText }} text-sm font-medium">{{ $review->title }}</p>
                                @endif
                                <p class="{{ $aMuted }} text-xs mt-0.5">{{ Str::limit($review->body, 80) }}</p>
                            </td>
                            <td class="px-6 py-4">
                                @if($review->is_approved)
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-500/20 text-green-300">Approved</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-500/20 text-yellow-300">Pending</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 {{ $aMuted }} text-sm whitespace-nowrap">
                                {{ $review->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    @if(!$review->is_approved)
                                        <form action="{{ route('admin.reviews.approve', $review) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 text-xs rounded-lg bg-green-500/20 text-green-300 hover:bg-green-500/30 transition" title="Approve">
                                                ✓ Approve
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.reviews.reject', $review) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 text-xs rounded-lg bg-yellow-500/20 text-yellow-300 hover:bg-yellow-500/30 transition" title="Reject">
                                                ✗ Reject
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Delete this review permanently?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 text-xs rounded-lg bg-red-500/20 text-red-300 hover:bg-red-500/30 transition" title="Delete">
                                            🗑
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t {{ $aBorderDiv }}">
            {{ $reviews->links() }}
        </div>
    @else
        <div class="p-12 text-center">
            <p class="{{ $aMuted2 }}">No {{ $filter !== 'all' ? $filter : '' }} reviews found.</p>
        </div>
    @endif
</div>
@endsection
