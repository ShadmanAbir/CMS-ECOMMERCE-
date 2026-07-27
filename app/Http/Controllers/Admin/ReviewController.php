<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * List all reviews with optional status filter.
     */
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'pending');

        $query = ProductReview::with(['product', 'user'])->latest();

        if ($filter === 'pending') {
            $query->where('is_approved', false);
        } elseif ($filter === 'approved') {
            $query->where('is_approved', true);
        }
        // 'all' shows everything

        $reviews = $query->paginate(20)->withQueryString();

        $counts = [
            'pending' => ProductReview::where('is_approved', false)->count(),
            'approved' => ProductReview::where('is_approved', true)->count(),
            'all' => ProductReview::count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'filter', 'counts'));
    }

    /**
     * Approve a review.
     */
    public function approve(ProductReview $review)
    {
        $review->update(['is_approved' => true]);

        return redirect()->back()->with('success', 'Review approved.');
    }

    /**
     * Reject (unapprove) a review.
     */
    public function reject(ProductReview $review)
    {
        $review->update(['is_approved' => false]);

        return redirect()->back()->with('success', 'Review rejected.');
    }

    /**
     * Delete a review permanently.
     */
    public function destroy(ProductReview $review)
    {
        $review->delete();

        return redirect()->back()->with('success', 'Review deleted.');
    }
}
