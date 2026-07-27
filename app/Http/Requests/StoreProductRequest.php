<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:200',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'status' => 'required|in:draft,active,inactive,out_of_stock',
            'is_trending' => 'nullable|boolean',
            'is_new' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'remarks' => 'nullable|string|max:240',
            'tags' => 'nullable|string',
            'sizes' => 'nullable|string',
            'colors' => 'nullable|string',
            'volume' => 'nullable|string|max:100',
            'how_to_use' => 'nullable|string',
            'ingredients_text' => 'nullable|string',
            'benefits_text' => 'nullable|string',
            'meta_title' => 'nullable|string|max:200',
            'meta_description' => 'nullable|string|max:500',
            'brand' => 'nullable|string|max:100',
        ];
    }

    /**
     * Get the effective category ID (subcategory takes priority).
     */
    public function effectiveCategoryId(): ?int
    {
        return $this->input('subcategory_id') ?? $this->input('category_id');
    }

    /**
     * Prepare data for product creation/update.
     */
    public function productData(): array
    {
        return [
            'name' => $this->input('name'),
            'slug' => \Illuminate\Support\Str::slug($this->input('name')),
            'description' => $this->input('description'),
            'price' => $this->input('price'),
            'discount' => $this->input('discount', 0),
            'stock' => $this->input('stock'),
            'sku' => $this->input('sku'),
            'category_id' => $this->effectiveCategoryId(),
            'status' => $this->input('status'),
            'is_trending' => $this->boolean('is_trending'),
            'is_new' => $this->boolean('is_new'),
            'is_featured' => $this->boolean('is_featured'),
            'remarks' => $this->input('remarks'),
            'volume' => $this->input('volume'),
            'how_to_use' => $this->input('how_to_use'),
            'brand' => $this->input('brand'),
            'meta_title' => $this->input('meta_title'),
            'meta_description' => $this->input('meta_description'),
            'tags' => $this->csvToArray('tags'),
            'sizes' => $this->csvToArray('sizes'),
            'colors' => $this->csvToArray('colors'),
            'ingredients' => $this->csvToArray('ingredients_text'),
            'benefits' => $this->linesToArray('benefits_text'),
        ];
    }

    protected function csvToArray(string $field): ?array
    {
        $value = $this->input($field);
        return $value ? array_map('trim', explode(',', $value)) : null;
    }

    protected function linesToArray(string $field): ?array
    {
        $value = $this->input($field);
        return $value ? array_filter(array_map('trim', explode("\n", $value))) : null;
    }
}
