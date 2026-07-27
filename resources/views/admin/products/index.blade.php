@extends('layouts.admin')

@section('title', 'Products')
@section('page-title', 'Product Management')

@section('content')
@php
    $aCardBg     = theme('admin.card_bg');
    $aCardBorder = theme('admin.card_border');
    $aCardText   = theme('admin.card_text');
    $aMuted      = theme('admin.muted_text');
    $aMuted2     = theme('admin.muted_text_2');
    $aMuted3     = theme('admin.muted_text_3');
    $aTableHeadBg = theme('admin.table_head_bg');
    $aTableDiv   = theme('admin.table_divider');
    $aRowDiv     = theme('admin.table_row_divider');
    $aRowHover   = theme('admin.table_row_hover');
    $aImgBg      = theme('admin.img_placeholder_bg');
    $aImgBorder  = theme('admin.img_placeholder_border');
    $aBtnPrimary = theme('admin.btn_primary_bg') . ' ' . theme('admin.btn_primary_text');
@endphp
<div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
    <h1 class="text-2xl font-heading tracking-wide {{ $aCardText }}">ALL <span class="text-accent">PRODUCTS</span></h1>
    <div class="flex items-center gap-3">
        <!-- Search -->
        <form method="GET" action="{{ route('admin.products.index') }}" class="relative">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 {{ $aMuted2 }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input type="text" name="q" value="{{ $search }}" placeholder="Search name or SKU…"
                   class="pl-9 pr-4 py-2 w-64 rounded-lg {{ theme('admin.input_bg') }} border {{ theme('admin.input_border') }} {{ theme('admin.input_text') }} text-sm focus:outline-none focus:ring-2 focus:ring-accent/40 transition">
        </form>
        <a href="{{ route('admin.products.create') }}" class="{{ $aBtnPrimary }} px-4 py-2 rounded-lg hover:bg-accent/90 transition font-bold uppercase tracking-wider text-sm">
            Add New Product
        </a>
    </div>
</div>

<div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg overflow-hidden">
    <table class="min-w-full divide-y {{ $aTableDiv }}">
        <thead class="{{ $aTableHeadBg }}">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Image</th>
                <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Price</th>
                <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Stock</th>
                <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y {{ $aRowDiv }}">
            @forelse($products as $product)
                <tr class="{{ $aRowHover }} transition">
                    <td class="px-6 py-4">
                        @if($product->images->count() > 0)
                            <img src="{{ storage_asset($product->images->first()->image_path) }}" alt="{{ $product->name }}" class="w-12 h-12 object-cover rounded">
                        @else
                            <div class="w-12 h-12 {{ $aImgBg }} border {{ $aImgBorder }} rounded flex items-center justify-center">
                                <svg class="w-6 h-6 {{ $aMuted2 }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium {{ $aCardText }}">{{ $product->name }}</div>
                        <div class="text-sm {{ $aMuted2 }}">SKU: {{ $product->sku ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm {{ $aMuted3 }}">{{ $product->category->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-sm font-semibold {{ $aCardText }}">৳{{ number_format($product->price, 2) }}</td>
                    <td class="px-6 py-4 text-sm {{ $aMuted3 }}">{{ $product->stock }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full
                            @if($product->status == 'active') bg-green-500/20 text-green-300
                            @elseif($product->status == 'out_of_stock') bg-gray-100 text-gray-500 border border-gray-200
                            @else bg-gray-200 text-gray-500
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $product->status)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium space-x-3">
                        <a href="{{ route('admin.products.edit', $product->id) }}" class="text-accent hover:text-accent/80">Edit</a>
                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this product?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center {{ $aMuted2 }}">No products found{{ $search ? ' for "' . $search . '"' : '' }}.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $products->links() }}
</div>
@endsection
