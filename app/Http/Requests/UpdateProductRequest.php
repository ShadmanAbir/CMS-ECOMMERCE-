<?php

namespace App\Http\Requests;

class UpdateProductRequest extends StoreProductRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        // Images are optional on update (adds to existing)
        $rules['images'] = 'nullable|array';
        $rules['images.*'] = 'image|mimes:jpeg,png,jpg,webp|max:2048';

        return $rules;
    }
}
