<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Product;
use App\Models\ProductReview;

class ReviewController extends Controller
{
    /**
     * Store a new product review
     */
    public function store(StoreReviewRequest $request, Product $product)
    {
        $validated = $request->validated();

        // Check if user already reviewed this product
        $existing = ProductReview::where('product_id', $product->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            // Update existing review
            $existing->update([
                'rating' => $validated['rating'],
                'title' => $validated['title'] ?? $existing->title,
                'body' => $validated['body'] ?? $existing->body,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review updated successfully! It will appear after approval.',
            ]);
        }

        // Check if user purchased this product (for verified badge)
        $hasPurchased = \App\Models\OrderItem::where('product_id', $product->id)
            ->whereHas('order', function ($q) {
                $q->where('user_id', auth()->id())
                  ->where('status', '!=', 'cancelled');
            })
            ->exists();

        ProductReview::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'rating' => $validated['rating'],
            'title' => $validated['title'] ?? null,
            'body' => $validated['body'] ?? null,
            'is_verified_purchase' => $hasPurchased,
            'is_approved' => false, // Requires admin approval
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted! It will appear after approval.',
        ]);
    }

    /**
     * Get reviews for a product (AJAX)
     */
    public function index(Product $product)
    {
        $reviews = $product->approvedReviews()
            ->with('user')
            ->latest()
            ->paginate(10);

        return response()->json([
            'reviews' => $reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'title' => $review->title,
                    'body' => $review->body,
                    'user_name' => $review->user->name ?? 'Anonymous',
                    'is_verified' => $review->is_verified_purchase,
                    'created_at' => $review->created_at->diffForHumans(),
                ];
            }),
            'average_rating' => $product->average_rating,
            'reviews_count' => $product->reviews_count,
        ]);
    }
}
