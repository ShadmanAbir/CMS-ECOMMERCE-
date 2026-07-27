@extends('layouts.admin')

@section('title', 'Edit Banner')
@section('page-title', 'Edit Banner')

@section('content')
@php
    $aCardBg     = theme('admin.card_bg');
    $aCardBorder = theme('admin.card_border');
    $aCardText   = theme('admin.card_text');
    $aMuted3     = theme('admin.muted_text_3');
    $aInputBg    = theme('admin.input_bg');
    $aInputBorder = theme('admin.input_border');
    $aInputText  = theme('admin.input_text');
    $aMuted2     = theme('admin.muted_text_2');
    $aCheckBorder = theme('admin.checkbox_border');
    $aCheckBg    = theme('admin.checkbox_bg');
    $aBtnPrimary = theme('admin.btn_primary_bg') . ' ' . theme('admin.btn_primary_text');
    $aBtnCancel  = theme('admin.btn_cancel_bg') . ' ' . theme('admin.btn_cancel_text') . ' ' . theme('admin.btn_cancel_hover');
@endphp
<div class="max-w-2xl mx-auto">
    <div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg p-6">
        <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Title</label>
                <input type="text" name="title" value="{{ old('title', $banner->title) }}"
                       class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Link URL</label>
                <input type="url" name="link" value="{{ old('link', $banner->link) }}" placeholder="https://example.com"
                       class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Banner Image</label>
                @if($banner->image)
                    <div class="mb-2">
                        <img src="{{ storage_asset($banner->image) }}" alt="{{ $banner->title }}" class="w-full h-48 object-cover rounded">
                        <p class="text-sm {{ $aMuted2 }}">Current image</p>
                    </div>
                @endif
                <input type="file" name="image" accept="image/*"
                       class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }}">
                <p class="text-sm {{ $aMuted2 }} mt-1">Upload new image to replace current (Max 2MB)</p>
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="status" value="1" {{ old('status', $banner->status) ? 'checked' : '' }}
                           class="h-4 w-4 text-accent focus:ring-accent {{ $aCheckBorder }} rounded {{ $aCheckBg }}">
                    <span class="ml-2 text-sm {{ $aMuted3 }}">Active</span>
                </label>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="flex-1 {{ $aBtnPrimary }} py-2 rounded-lg hover:bg-accent/90 transition font-bold uppercase tracking-wider">
                    Update Banner
                </button>
                <a href="{{ route('admin.banners.index') }}" class="flex-1 {{ $aBtnCancel }} py-2 rounded-lg transition text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
