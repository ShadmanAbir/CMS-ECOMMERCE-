@extends('layouts.admin')

@section('title', 'Add Category')
@section('page-title', 'Add New Category')

@section('content')
@php
    $aCardBg     = theme('admin.card_bg');
    $aCardBorder = theme('admin.card_border');
    $aMuted3     = theme('admin.muted_text_3');
    $aMuted2     = theme('admin.muted_text_2');
    $aInputBg    = theme('admin.input_bg');
    $aInputBorder = theme('admin.input_border');
    $aInputText  = theme('admin.input_text');
    $aBtnPrimary = theme('admin.btn_primary_bg') . ' ' . theme('admin.btn_primary_text');
    $aBtnCancel  = theme('admin.btn_cancel_bg') . ' ' . theme('admin.btn_cancel_text') . ' ' . theme('admin.btn_cancel_hover');
@endphp
<div class="max-w-2xl mx-auto">
    <div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg p-6">
        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Category Name *</label>
                <input type="text" name="name" required value="{{ old('name') }}"
                       class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                @error('name')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Description</label>
                <textarea name="description" rows="3" class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">{{ old('description') }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Category Image</label>
                <input type="file" name="cat_image" accept="image/*"
                       class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }}">
                <p class="text-sm {{ $aMuted2 }} mt-1">Accepted: JPEG, PNG, JPG, WEBP (Max 2MB)</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Parent Category</label>
                <select name="parent_id" class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                    <option value="">None (This is a parent category)</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                            {{ $parent->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Status *</label>
                <select name="status" required class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="flex-1 {{ $aBtnPrimary }} py-2 rounded-lg hover:bg-accent/90 transition font-bold uppercase tracking-wider">
                    Create Category
                </button>
                <a href="{{ route('admin.categories.index') }}" class="flex-1 {{ $aBtnCancel }} py-2 rounded-lg transition text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
