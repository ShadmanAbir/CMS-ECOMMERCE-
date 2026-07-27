@extends('layouts.admin')

@section('title', 'Order ' . $order->order_number)
@section('page-title', 'Order Details')

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
    $aBorderDiv   = theme('admin.border_divider');
    $aImgBg       = theme('admin.img_placeholder_bg');
    $aImgBorder   = theme('admin.img_placeholder_border');

    $badge = [
        'pending'    => 'bg-yellow-500/15 text-yellow-600 border border-yellow-500/30',
        'processing' => 'bg-blue-500/15 text-blue-600 border border-blue-500/30',
        'shipped'    => 'bg-purple-500/15 text-purple-600 border border-purple-500/30',
        'delivered'  => 'bg-green-500/15 text-green-600 border border-green-500/30',
        'cancelled'  => 'bg-red-500/15 text-red-600 border border-red-500/30',
    ];

    // Fulfilment stepper position (cancelled handled separately).
    $steps = ['pending', 'processing', 'shipped', 'delivered'];
    $currentIndex = array_search($order->status, $steps);
    $isCancelled = $order->status === 'cancelled';

    // Friendly labels + icons for each forward transition.
    $transitionMeta = [
        'processing' => ['label' => 'Start Processing', 'class' => 'bg-blue-600 hover:bg-blue-700 text-white'],
        'shipped'    => ['label' => 'Mark as Shipped', 'class' => 'bg-purple-600 hover:bg-purple-700 text-white'],
        'delivered'  => ['label' => 'Mark as Delivered', 'class' => 'bg-green-600 hover:bg-green-700 text-white'],
        'cancelled'  => ['label' => 'Cancel Order', 'class' => 'bg-red-600/90 hover:bg-red-700 text-white'],
    ];
@endphp

<!-- Back link -->
<a href="{{ route('admin.orders.index') }}" class="inline-flex items-center text-sm {{ $aMuted3 }} hover:text-accent transition mb-4">
    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
    Back to orders
</a>

<!-- Header -->
<div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
    <div>
        <h1 class="text-2xl font-heading tracking-wide {{ $aCardText }}">
            ORDER <span class="text-accent">{{ $order->order_number }}</span>
        </h1>
        <p class="text-sm {{ $aMuted }} mt-1">Placed on {{ $order->created_at->format('F d, Y \a\t h:i A') }}</p>
    </div>
    <span class="self-start inline-block px-3 py-1.5 text-sm font-semibold rounded-full {{ $badge[$order->status] ?? 'bg-gray-200 text-gray-500' }}">
        {{ ucfirst($order->status) }}
    </span>
</div>

<!-- Cancelled banner -->
@if($isCancelled)
    <div class="mb-6 flex items-center gap-3 bg-red-500/10 border border-red-500/30 text-red-600 px-4 py-3 rounded-lg">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
        <span class="text-sm font-medium">This order has been cancelled. Reserved stock has been returned to inventory.</span>
    </div>
@endif

<!-- Fulfilment timeline -->
@if(!$isCancelled)
<div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg p-6 mb-6">
    <div class="flex items-center">
        @foreach($steps as $i => $step)
            @php
                $done = $currentIndex !== false && $i < $currentIndex;
                $current = $i === $currentIndex;
                $isLast = $i === count($steps) - 1;
            @endphp
            <div class="flex items-center {{ $isLast ? '' : 'flex-1' }}">
                <div class="flex flex-col items-center">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center border-2 transition
                        @if($done) bg-accent border-accent text-white
                        @elseif($current) border-accent text-accent bg-accent/10 ring-4 ring-accent/15
                        @else border-gray-300 text-gray-300 bg-white
                        @endif">
                        @if($done)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        @else
                            <span class="text-xs font-bold">{{ $i + 1 }}</span>
                        @endif
                    </div>
                    <span class="mt-2 text-xs font-semibold uppercase tracking-wider whitespace-nowrap
                        {{ $current ? 'text-accent' : ($done ? $aCardText : $aMuted2) }}">
                        {{ ucfirst($step) }}
                    </span>
                </div>
                @if(!$isLast)
                    <div class="flex-1 h-0.5 mx-2 mb-6 rounded {{ $done ? 'bg-accent' : 'bg-gray-200' }}"></div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endif

<!-- Status actions -->
@if(count($allowedTransitions) > 0)
<div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg p-6 mb-6">
    <h3 class="text-sm font-heading uppercase tracking-wider {{ $aMuted }} mb-4">Update Status</h3>
    <form method="POST" action="{{ route('admin.orders.update-status', $order->id) }}">
        @csrf
        @method('PATCH')
        <textarea name="note" rows="2" maxlength="255"
                  placeholder="Optional note — saved to the order timeline (e.g. tracking number, reason)..."
                  class="w-full rounded-lg border {{ $aBorderDiv }} bg-transparent {{ $aCardText }} text-sm p-3 mb-4 focus:outline-none focus:border-accent">{{ old('note') }}</textarea>
        <div class="flex flex-wrap gap-3">
            @foreach($allowedTransitions as $next)
                @php $meta = $transitionMeta[$next] ?? ['label' => ucfirst($next), 'class' => 'bg-accent hover:bg-accent/90 text-white']; @endphp
                <button type="submit" name="status" value="{{ $next }}"
                    @if($next === 'cancelled') onclick="return confirm('Cancel this order? Stock will be restored.');" @endif
                    class="px-5 py-2.5 rounded-lg text-sm font-bold uppercase tracking-wider transition shadow-sm {{ $meta['class'] }}">
                    {{ $meta['label'] }}
                </button>
            @endforeach
        </div>
    </form>
    @error('status')
        <p class="mt-3 text-sm text-red-500">{{ $message }}</p>
    @enderror
    @error('note')
        <p class="mt-3 text-sm text-red-500">{{ $message }}</p>
    @enderror
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left column: items + summary -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Items -->
        <div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b {{ $aBorderDiv }}">
                <h3 class="text-lg font-heading tracking-wide {{ $aCardText }}">ORDER <span class="text-accent">ITEMS</span></h3>
            </div>
            <table class="min-w-full divide-y {{ $aTableDiv }}">
                <thead class="{{ $aTableHeadBg }}">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Variant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Qty</th>
                        <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-right text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y {{ $aRowDiv }}">
                    @foreach($order->items as $item)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($item->product && $item->product->images->count() > 0)
                                        <img src="{{ storage_asset($item->product->images->first()->image_path) }}" alt="{{ $item->product->name }}" class="w-12 h-12 object-cover rounded">
                                    @else
                                        <div class="w-12 h-12 {{ $aImgBg }} border {{ $aImgBorder }} rounded flex items-center justify-center">
                                            <svg class="w-6 h-6 {{ $aMuted2 }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium {{ $aCardText }}">{{ $item->product->name ?? 'Product unavailable' }}</div>
                                        <div class="text-xs {{ $aMuted2 }}">{{ $item->product->sku ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm {{ $aMuted3 }}">
                                {{ $item->variant->name ?? '—' }}
                                @if($item->size || $item->color)
                                    <div class="text-xs {{ $aMuted2 }}">
                                        @if($item->size) Size: {{ $item->size }} @endif
                                        @if($item->color) · {{ $item->color }} @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm {{ $aMuted3 }}">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 text-sm {{ $aMuted3 }}">৳{{ number_format($item->price, 2) }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-right {{ $aCardText }}">৳{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Totals -->
            <div class="px-6 py-4 border-t {{ $aBorderDiv }} space-y-2">
                <div class="flex justify-between text-sm {{ $aMuted3 }}"><span>Subtotal</span><span>৳{{ number_format($order->subtotal, 2) }}</span></div>
                <div class="flex justify-between text-sm {{ $aMuted3 }}"><span>Discount</span><span>− ৳{{ number_format($order->discount_amount, 2) }}</span></div>
                <div class="flex justify-between text-sm {{ $aMuted3 }}"><span>VAT</span><span>৳{{ number_format($order->vat_amount, 2) }}</span></div>
                <div class="flex justify-between text-sm {{ $aMuted3 }}"><span>Shipping</span><span>৳{{ number_format($order->shipping_amount, 2) }}</span></div>
                <div class="flex justify-between text-base font-bold {{ $aCardText }} pt-2 border-t {{ $aBorderDiv }}">
                    <span>Total</span><span class="text-accent">৳{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right column: customer / shipping / payment -->
    <div class="space-y-6">
        <!-- Customer -->
        <div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg p-6">
            <h3 class="text-sm font-heading uppercase tracking-wider {{ $aMuted }} mb-4">Customer</h3>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm bg-accent/10 border border-accent/20 text-accent">
                    {{ substr($order->user->name ?? 'G', 0, 1) }}
                </div>
                <div>
                    <p class="text-sm font-medium {{ $aCardText }}">{{ $order->user->name ?? 'Guest' }}</p>
                    <p class="text-xs {{ $aMuted2 }}">{{ $order->user->email ?? '—' }}</p>
                    <p class="text-xs {{ $aMuted2 }}">{{ $order->user->phone ?? '' }}</p>
                </div>
            </div>
        </div>

        <!-- Shipping address -->
        <div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg p-6">
            <h3 class="text-sm font-heading uppercase tracking-wider {{ $aMuted }} mb-4">Shipping Address</h3>
            @if($order->shippingAddress)
                <p class="text-sm font-medium {{ $aCardText }}">{{ $order->shippingAddress->name ?? $order->user->name ?? '' }}</p>
                <p class="text-sm {{ $aMuted3 }} mt-1">{{ $order->shippingAddress->address ?? '' }}</p>
                <p class="text-sm {{ $aMuted3 }}">{{ $order->shippingAddress->city ?? '' }} {{ $order->shippingAddress->postal_code ?? '' }}</p>
                <p class="text-sm {{ $aMuted3 }}">{{ $order->shippingAddress->phone ?? '' }}</p>
            @else
                <p class="text-sm {{ $aMuted2 }}">No shipping address on file.</p>
            @endif
        </div>

        <!-- Payment -->
        <div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg p-6">
            <h3 class="text-sm font-heading uppercase tracking-wider {{ $aMuted }} mb-4">Payment</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="{{ $aMuted3 }}">Method</span><span class="font-medium {{ $aCardText }}">{{ strtoupper($order->payment_method ?? '—') }}</span></div>
                <div class="flex justify-between"><span class="{{ $aMuted3 }}">Status</span><span class="font-medium {{ $aCardText }}">{{ ucfirst($order->payment_status) }}</span></div>
                @if($order->transaction_id)
                    <div class="flex justify-between"><span class="{{ $aMuted3 }}">Transaction</span><span class="font-mono text-xs {{ $aCardText }}">{{ $order->transaction_id }}</span></div>
                @endif
            </div>
        </div>

        @if($order->notes)
        <div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg p-6">
            <h3 class="text-sm font-heading uppercase tracking-wider {{ $aMuted }} mb-4">Customer Notes</h3>
            <p class="text-sm {{ $aMuted3 }} italic">“{{ $order->notes }}”</p>
        </div>
        @endif

        <!-- Status history -->
        @if($order->statusHistory->count() > 0)
        <div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg p-6">
            <h3 class="text-sm font-heading uppercase tracking-wider {{ $aMuted }} mb-4">Status History</h3>
            <ol class="relative border-l {{ $aBorderDiv }} ml-1.5 space-y-5">
                @foreach($order->statusHistory as $entry)
                    <li class="ml-4">
                        <span class="absolute -left-[5px] mt-1 w-2.5 h-2.5 rounded-full {{ $entry->status === 'cancelled' ? 'bg-red-500' : 'bg-accent' }}"></span>
                        <p class="text-sm font-semibold {{ $aCardText }}">{{ ucfirst($entry->status) }}</p>
                        @if($entry->note)<p class="text-xs {{ $aMuted2 }}">{{ $entry->note }}</p>@endif
                        <p class="text-xs {{ $aMuted3 }} mt-0.5">{{ $entry->created_at->format('M d, Y \a\t h:i A') }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
        @endif
    </div>
</div>
@endsection
