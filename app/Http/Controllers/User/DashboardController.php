<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display user orders
     */
    public function orders(Request $request)
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('search'));

        $orders = $this->orderService->getUserOrders(auth()->id(), $status, $search);
        $statusCounts = $this->orderService->userOrderStatusCounts(auth()->id());

        return view('user.orders', compact('orders', 'statusCounts', 'status', 'search'));
    }

    /**
     * Display order details
     */
    public function orderDetails($orderNumber)
    {
        $order = \App\Models\Order::where('order_number', $orderNumber)
            ->with(['items.product.images', 'items.variant', 'payment', 'shippingAddress', 'statusHistory'])
            ->firstOrFail();

        $this->authorize('view', $order);

        return view('user.order-details', compact('order'));
    }

    /**
     * Cancel an order
     */
    public function cancelOrder($orderNumber)
    {
        $order = \App\Models\Order::where('order_number', $orderNumber)
            ->firstOrFail();

        $this->authorize('cancel', $order);

        try {
            $this->orderService->cancelOrder($order, auth()->id());

            return back()->with('success', 'Order cancelled successfully. Stock has been restored.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display user profile
     */
    public function profile()
    {
        $user = auth()->user();
        $addresses = $user->addresses()->orderBy('is_default', 'desc')->get();

        return view('user.profile', compact('user', 'addresses'));
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'nullable|email|max:150|unique:users,email,' . auth()->id(),
            'phone' => 'nullable|string|max:20|unique:users,phone,' . auth()->id(),
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|min:6|confirmed',
        ]);

        $user = auth()->user();
        
        // Update basic info
        $user->name = $validated['name'];
        $user->email = $validated['email'] ?? $user->email;
        $user->phone = $validated['phone'] ?? $user->phone;

        // Update password if provided
        if (!empty($validated['password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect']);
            }
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully');
    }

    /**
     * Display user wishlist
     */
    public function wishlist()
    {
        $wishlist = auth()->user()->wishlists()
            ->with('product.images')
            ->latest()
            ->paginate(12);

        return view('user.wishlist', compact('wishlist'));
    }

    /**
     * Toggle wishlist item (add/remove)
     */
    public function toggleWishlist(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to use wishlist.',
            ], 401);
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $existing = \App\Models\Wishlist::where([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
        ])->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'success' => true,
                'added' => false,
                'count' => auth()->user()->wishlists()->count(),
            ]);
        }

        \App\Models\Wishlist::create([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
        ]);

        return response()->json([
            'success' => true,
            'added' => true,
            'count' => auth()->user()->wishlists()->count(),
        ]);
    }
}
