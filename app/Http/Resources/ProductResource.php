<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->when($request->routeIs('products.show'), $this->description),
            'price' => $this->price,
            'discounted_price' => $this->getDiscountedPrice(),
            'discount' => $this->discount,
            'image' => $this->primaryImage(),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'category' => $this->when(
                $this->relationLoaded('category') && $this->category,
                fn () => ['id' => $this->category->id, 'name' => $this->category->name, 'slug' => $this->category->slug]
            ),
            'rating' => $this->average_rating,
            'reviews_count' => $this->reviews_count,
            'in_stock' => $this->isInStock(),
            'is_trending' => $this->is_trending,
            'is_new' => $this->is_new,
            'is_featured' => $this->is_featured,
        ];
    }

    protected function primaryImage(): string
    {
        $image = $this->whenLoaded('images', fn () => $this->images->firstWhere('is_primary', true) ?? $this->images->first());

        return $image ? storage_asset($image->image_path) : asset('images/placeholder/product_placeholder.jpg');
    }
}
