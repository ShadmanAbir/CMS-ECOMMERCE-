@extends('layouts.admin')

@section('title', 'Add Product')
@section('page-title', 'Add New Product')

@section('content')
@php
    $aCardBg     = theme('admin.card_bg');
    $aCardBorder = theme('admin.card_border');
    $aCardText   = theme('admin.card_text');
    $aMuted2     = theme('admin.muted_text_2');
    $aMuted3     = theme('admin.muted_text_3');
    $aInputBg    = theme('admin.input_bg');
    $aInputBorder = theme('admin.input_border');
    $aInputText  = theme('admin.input_text');
    $aBorderDiv  = theme('admin.border_divider');
    $aCheckBorder = theme('admin.checkbox_border');
    $aCheckBg    = theme('admin.checkbox_bg');
    $aFlagBorder = theme('admin.flag_border');
    $aFlagHover  = theme('admin.flag_hover');
    $aBtnPrimary = theme('admin.btn_primary_bg') . ' ' . theme('admin.btn_primary_text');
    $aBtnCancel  = theme('admin.btn_cancel_bg') . ' ' . theme('admin.btn_cancel_text') . ' ' . theme('admin.btn_cancel_hover');
@endphp
<div class="max-w-4xl mx-auto">
    <div class="{{ $aCardBg }} {{ $aCardBorder }} rounded-lg p-6">
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Product Name *</label>
                    <input type="text" name="name" required value="{{ old('name') }}"
                           class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Description *</label>
                    <textarea name="description" rows="4" required class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">{{ old('description') }}</textarea>
                </div>

                <div x-data="{
                    categories: {{ $categories->toJson() }},
                    selectedParent: {{ old('category_id') ? ($categories->firstWhere('id', old('category_id')) ? $categories->firstWhere('id', old('category_id'))->id : 'null') : 'null' }},
                    get subcategories() {
                        if (!this.selectedParent) return [];
                        const parent = this.categories.find(c => c.id == this.selectedParent);
                        return parent ? parent.children : [];
                    }
                }">
                    <div class="mb-4">
                        <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Main Category *</label>
                        <select x-model="selectedParent" name="category_id" id="main_category" required
                                class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                            <option value="">Select Main Category</option>
                            <template x-for="category in categories" :key="category.id">
                                <option :value="category.id" x-text="category.name"></option>
                            </template>
                        </select>
                        @error('category_id')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Subcategory</label>
                        <select name="subcategory_id" id="subcategory"
                                class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                            <option value="">Select Subcategory (Optional)</option>
                            <template x-for="subcat in subcategories" :key="subcat.id">
                                <option :value="subcat.id" x-text="subcat.name"></option>
                            </template>
                        </select>
                        <p class="text-sm {{ $aMuted2 }} mt-1">Select a main category first to see subcategories</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">SKU</label>
                    <input type="text" name="sku" value="{{ old('sku') }}"
                           class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                </div>

                <div>
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Volume (e.g., 100ml)</label>
                    <input type="text" name="volume" value="{{ old('volume') }}" placeholder="100ml"
                           class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                </div>

                <div>
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Price *</label>
                    <input type="number" name="price" required step="0.01" min="0" value="{{ old('price') }}"
                           class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                </div>

                <div>
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Discount (%)</label>
                    <input type="number" name="discount" step="0.01" min="0" max="100" value="{{ old('discount', 0) }}"
                           class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                </div>

                <div>
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Stock *</label>
                    <input type="number" name="stock" required min="0" value="{{ old('stock', 0) }}"
                           class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                </div>

                <div>
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Status *</label>
                    <select name="status" required class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="out_of_stock" {{ old('status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>

                <!-- Cosmetics Fields -->
                <div class="md:col-span-2 border-t {{ $aBorderDiv }} pt-6">
                    <h3 class="text-lg font-heading tracking-wide {{ $aCardText }} mb-4">PRODUCT <span class="text-accent">DETAILS</span></h3>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Key Ingredients (comma separated)</label>
                    <input type="text" name="ingredients_text" value="{{ old('ingredients_text') }}" placeholder="Salicylic Acid, Niacinamide, Tea Tree Oil"
                           class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                    <p class="text-sm {{ $aMuted2 }} mt-1">Enter ingredients separated by commas</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Benefits (one per line)</label>
                    <textarea name="benefits_text" rows="3" class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition" placeholder="Fights acne and breakouts&#10;Controls excess oil">{{ old('benefits_text') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">How to Use</label>
                    <textarea name="how_to_use" rows="3" class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition" placeholder="Apply to wet face, massage gently, rinse thoroughly. Use morning and night.">{{ old('how_to_use') }}</textarea>
                </div>

                {{-- Dynamic per-site attributes --}}
                @include('admin.products._dynamic_attributes')

                <!-- Flags & Images -->
                <div class="md:col-span-2 border-t {{ $aBorderDiv }} pt-6">
                    <h3 class="text-lg font-heading tracking-wide {{ $aCardText }} mb-4">FLAGS & <span class="text-accent">MEDIA</span></h3>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-3 uppercase tracking-wider">Product Flags</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="flex items-center space-x-3 p-3 border {{ $aFlagBorder }} rounded-lg {{ $aFlagHover }} cursor-pointer">
                            <input type="checkbox" name="is_trending" value="1" {{ old('is_trending') ? 'checked' : '' }}
                                   class="w-5 h-5 text-accent {{ $aCheckBorder }} rounded focus:ring-accent {{ $aCheckBg }}">
                            <span class="text-sm font-medium {{ $aCardText }}">Bestseller</span>
                        </label>
                        <label class="flex items-center space-x-3 p-3 border {{ $aFlagBorder }} rounded-lg {{ $aFlagHover }} cursor-pointer">
                            <input type="checkbox" name="is_new" value="1" {{ old('is_new') ? 'checked' : '' }}
                                   class="w-5 h-5 text-accent {{ $aCheckBorder }} rounded focus:ring-accent {{ $aCheckBg }}">
                            <span class="text-sm font-medium {{ $aCardText }}">New Launch</span>
                        </label>
                        <label class="flex items-center space-x-3 p-3 border {{ $aFlagBorder }} rounded-lg {{ $aFlagHover }} cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                                   class="w-5 h-5 text-accent {{ $aCheckBorder }} rounded focus:ring-accent {{ $aCheckBg }}">
                            <span class="text-sm font-medium {{ $aCardText }}">Lab Favorite</span>
                        </label>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Product Images *</label>
                    <input type="file" name="images[]" accept="image/*" multiple required
                           class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }}">
                    <p class="text-sm {{ $aMuted2 }} mt-1">You can select multiple images (Max 2MB each)</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Tags (comma separated)</label>
                    <input type="text" name="tags" value="{{ old('tags') }}" placeholder="face wash, anti-acne, oily skin"
                           class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                </div>

                <!-- SEO Fields -->
                <div class="md:col-span-2 border-t {{ $aBorderDiv }} pt-6">
                    <h3 class="text-lg font-heading tracking-wide {{ $aCardText }} mb-4">SEO <span class="text-accent">META</span></h3>
                </div>

                <div>
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Brand</label>
                    <input type="text" name="brand" value="{{ old('brand') }}" placeholder="BAD LAB"
                           class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                </div>

                <div>
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Meta Title</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title') }}" maxlength="200"
                           class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                    <p class="text-sm {{ $aMuted2 }} mt-1">Leave blank to auto-generate from product name</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Meta Description</label>
                    <textarea name="meta_description" rows="2" maxlength="500" class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition" placeholder="Short description for search engines...">{{ old('meta_description') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Remarks</label>
                    <textarea name="remarks" rows="2" class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">{{ old('remarks') }}</textarea>
                </div>
            </div>

            <div class="flex gap-4 mt-6">
                <button type="submit" class="flex-1 {{ $aBtnPrimary }} py-2 rounded-lg hover:bg-accent/90 transition font-bold uppercase tracking-wider">
                    Create Product
                </button>
                <a href="{{ route('admin.products.index') }}" class="flex-1 {{ $aBtnCancel }} py-2 rounded-lg transition text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
