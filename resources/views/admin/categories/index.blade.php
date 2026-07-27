@extends('layouts.admin')

@section('title', 'Categories')
@section('page-title', 'Category Management')

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
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-heading tracking-wide {{ $aCardText }}">ALL <span class="text-accent">CATEGORIES</span></h1>
    <a href="{{ route('admin.categories.create') }}" class="{{ $aBtnPrimary }} px-4 py-2 rounded-lg hover:bg-accent/90 transition font-bold uppercase tracking-wider text-sm">
        Add New Category
    </a>
</div>

<div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg overflow-hidden">
    <table class="min-w-full divide-y {{ $aTableDiv }}">
        <thead class="{{ $aTableHeadBg }}">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Image</th>
                <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Parent</th>
                <th class="px-6 py-3 text-left text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium {{ $aMuted }} uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y {{ $aRowDiv }}">
            @forelse($categories as $category)
                <tr class="{{ $aRowHover }} transition">
                    <td class="px-6 py-4">
                        @if($category->cat_image)
                            <img src="{{ storage_asset($category->cat_image) }}" alt="{{ $category->name }}" class="w-12 h-12 object-cover rounded">
                        @else
                            <div class="w-12 h-12 {{ $aImgBg }} border {{ $aImgBorder }} rounded flex items-center justify-center">
                                <svg class="w-6 h-6 {{ $aMuted2 }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium {{ $aCardText }}">{{ $category->name }}</div>
                        <div class="text-sm {{ $aMuted2 }}">{{ $category->slug }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm {{ $aMuted3 }}">
                        {{ $category->parent->name ?? 'None (Parent)' }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full {{ $category->status == 'active' ? 'bg-green-500/20 text-green-300' : 'bg-gray-200 text-gray-500' }}">
                            {{ ucfirst($category->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium space-x-3">
                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="text-accent hover:text-accent/80">Edit</a>
                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center {{ $aMuted2 }}">No categories found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
