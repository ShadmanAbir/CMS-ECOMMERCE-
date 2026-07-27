@extends('layouts.admin')

@section('title', 'Edit Product')
@section('page-title', 'Edit Product')

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
        <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Product Name *</label>
                    <input type="text" name="name" required value="{{ old('name', $product->name) }}"
                           class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Description *</label>
                    <textarea name="description" rows="4" required class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">{{ old('description', $product->description) }}</textarea>
                </div>

                @php
                    $currentCategory = $product->category;
                    $isSubcategory = $currentCategory && $currentCategory->parent_id !== null;
                    $selectedParentId = $isSubcategory ? $currentCategory->parent_id : ($currentCategory ? $currentCategory->id : null);
                    $selectedSubcategoryId = $isSubcategory ? $currentCategory->id : null;
                @endphp

                <div x-data="{
                    categories: {{ $categories->toJson() }},
                    selectedParent: {{ $selectedParentId ? $selectedParentId : 'null' }},
                    selectedSubcategory: {{ $selectedSubcategoryId ? $selectedSubcategoryId : 'null' }},
                    get subcategories() {
                        if (!this.selectedParent) return [];
                        const parent = this.categories.find(c => c.id == this.selectedParent);
                        return parent ? parent.children : [];
                    }
                }">
                    <div class="mb-4">
                        <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Main Category *</label>
                        <select x-model="selectedParent" @change="selectedSubcategory = null" name="category_id" id="main_category" required
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
                        <select x-model="selectedSubcategory" name="subcategory_id" id="subcategory"
                                class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                            <option value="">Select Subcategory (Optional)</option>
                            <template x-for="subcat in subcategories" :key="subcat.id">
                                <option :value="subcat.id" x-text="subcat.name"></option>
                            </template>
                        </select>
                        @if($isSubcategory)
                            <p class="text-sm text-accent mt-1">Current: {{ $currentCategory->name }}</p>
                        @else
                            <p class="text-sm {{ $aMuted2 }} mt-1">Select a main category first to see subcategories</p>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">SKU</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}"
                           class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                </div>

                <div>
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Volume (e.g., 100ml)</label>
                    <input type="text" name="volume" value="{{ old('volume', $product->volume) }}" placeholder="100ml"
                           class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                </div>

                <div>
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Price *</label>
                    <input type="number" name="price" required step="0.01" min="0" value="{{ old('price', $product->price) }}"
                           class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                </div>

                <div>
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Discount (%)</label>
                    <input type="number" name="discount" step="0.01" min="0" max="100" value="{{ old('discount', $product->discount) }}"
                           class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                </div>

                <div>
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Stock *</label>
                    <input type="number" name="stock" required min="0" value="{{ old('stock', $product->stock) }}"
                           class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                </div>

                <div>
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Status *</label>
                    <select name="status" required class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                        <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="draft" {{ old('status', $product->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="out_of_stock" {{ old('status', $product->status) == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>

                <!-- Cosmetics Fields -->
                <div class="md:col-span-2 border-t {{ $aBorderDiv }} pt-6">
                    <h3 class="text-lg font-heading tracking-wide {{ $aCardText }} mb-4">PRODUCT <span class="text-accent">DETAILS</span></h3>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Key Ingredients (comma separated)</label>
                    <input type="text" name="ingredients_text" value="{{ old('ingredients_text', is_array($product->ingredients) ? implode(', ', $product->ingredients) : '') }}" placeholder="Salicylic Acid, Niacinamide, Tea Tree Oil"
                           class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Benefits (one per line)</label>
                    <textarea name="benefits_text" rows="3" class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition" placeholder="Fights acne and breakouts&#10;Controls excess oil">{{ old('benefits_text', is_array($product->benefits) ? implode("\n", $product->benefits) : '') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">How to Use</label>
                    <textarea name="how_to_use" rows="3" class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">{{ old('how_to_use', $product->how_to_use) }}</textarea>
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
                            <input type="checkbox" name="is_trending" value="1" {{ old('is_trending', $product->is_trending) ? 'checked' : '' }}
                                   class="w-5 h-5 text-accent {{ $aCheckBorder }} rounded focus:ring-accent {{ $aCheckBg }}">
                            <span class="text-sm font-medium {{ $aCardText }}">Bestseller</span>
                        </label>
                        <label class="flex items-center space-x-3 p-3 border {{ $aFlagBorder }} rounded-lg {{ $aFlagHover }} cursor-pointer">
                            <input type="checkbox" name="is_new" value="1" {{ old('is_new', $product->is_new) ? 'checked' : '' }}
                                   class="w-5 h-5 text-accent {{ $aCheckBorder }} rounded focus:ring-accent {{ $aCheckBg }}">
                            <span class="text-sm font-medium {{ $aCardText }}">New Launch</span>
                        </label>
                        <label class="flex items-center space-x-3 p-3 border {{ $aFlagBorder }} rounded-lg {{ $aFlagHover }} cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}
                                   class="w-5 h-5 text-accent {{ $aCheckBorder }} rounded focus:ring-accent {{ $aCheckBg }}">
                            <span class="text-sm font-medium {{ $aCardText }}">Lab Favorite</span>
                        </label>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Add More Images</label>
                    <input type="file" name="images[]" accept="image/*" multiple
                           class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }}">
                    <p class="text-sm {{ $aMuted2 }} mt-1">Current: {{ $product->images->count() }} image(s)</p>
                </div>

                @if($product->images->count() > 0)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Current Images</label>
                        <div class="grid grid-cols-4 gap-4">
                            @foreach($product->images as $image)
                                <div class="relative">
                                    <img src="{{ storage_asset($image->image_path) }}" class="w-full h-24 object-cover rounded">
                                    <form action="{{ route('admin.product-images.destroy', $image->id) }}" method="POST" class="absolute top-1 right-1" onsubmit="return confirm('Delete this image?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 text-sm">&times;</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Tags (comma separated)</label>
                    <input type="text" name="tags" value="{{ old('tags', is_array($product->tags) ? implode(', ', $product->tags) : '') }}" placeholder="face wash, anti-acne, oily skin"
                           class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                </div>

                <!-- SEO Fields -->
                <div class="md:col-span-2 border-t {{ $aBorderDiv }} pt-6">
                    <h3 class="text-lg font-heading tracking-wide {{ $aCardText }} mb-4">SEO <span class="text-accent">META</span></h3>
                </div>

                <div>
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Brand</label>
                    <input type="text" name="brand" value="{{ old('brand', $product->brand) }}" placeholder="BAD LAB"
                           class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                </div>

                <div>
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Meta Title</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $product->meta_title) }}" maxlength="200"
                           class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Meta Description</label>
                    <textarea name="meta_description" rows="2" maxlength="500" class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">{{ old('meta_description', $product->meta_description) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium {{ $aMuted3 }} mb-2 uppercase tracking-wider">Remarks</label>
                    <textarea name="remarks" rows="2" class="w-full px-4 py-2 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} focus:outline-none focus:border-accent transition">{{ old('remarks', $product->remarks) }}</textarea>
                </div>
            </div>

            <div class="flex gap-4 mt-6">
                <button type="submit" class="flex-1 {{ $aBtnPrimary }} py-2 rounded-lg hover:bg-accent/90 transition font-bold uppercase tracking-wider">
                    Update Product
                </button>
                <a href="{{ route('admin.products.index') }}" class="flex-1 {{ $aBtnCancel }} py-2 rounded-lg transition text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
