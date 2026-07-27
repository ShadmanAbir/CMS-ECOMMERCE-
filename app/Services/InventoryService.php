<?php

namespace App\Services;

use App\Events\StockLow;
use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\StockMovement;

class InventoryService
{
    /**
     * Low stock threshold, configurable per-site via theme config.
     */
    public function lowStockThreshold(): int
    {
        return (int) theme('inventory.low_stock_threshold', 5);
    }

    /**
     * Check if a cart item has sufficient stock.
     * Throws exception with descriptive message if not.
     */
    public function validateStock($cartItem): void
    {
        $product = $cartItem->product;

        if (!$product) {
            throw new \Exception('Product not found in cart.');
        }

        if ($cartItem->product_variant_id) {
            $this->validateVariantStock($cartItem);
        } elseif ($cartItem->product_stock_id) {
            $this->validateBatchStock($cartItem);
        } else {
            $this->validateProductStock($cartItem);
        }
    }

    /**
     * Validate all cart items have sufficient stock.
     */
    public function validateCartStock($cartItems): void
    {
        foreach ($cartItems as $cartItem) {
            $this->validateStock($cartItem);
        }
    }

    /**
     * Decrement stock for a cart item after order placement.
     * Fires StockLow event if threshold is breached.
     */
    public function decrementStock($cartItem): void
    {
        if ($cartItem->product_variant_id && $cartItem->variant) {
            $cartItem->variant->decrement('stock', $cartItem->quantity);

            if ($cartItem->variant->fresh()->stock <= $this->lowStockThreshold()) {
                event(new StockLow($cartItem->product));
            }
        } elseif ($cartItem->product_stock_id) {
            $stock = ProductStock::find($cartItem->product_stock_id);
            if ($stock) {
                $stock->decrement('quantity', $cartItem->quantity);
            }
        } else {
            $cartItem->product->decrement('stock', $cartItem->quantity);

            if ($cartItem->product->fresh()->stock <= $this->lowStockThreshold()) {
                event(new StockLow($cartItem->product));
            }
        }
    }

    /**
     * Restore stock for an order item (used on cancellation).
     */
    public function restoreStock($orderItem): void
    {
        if ($orderItem->product_variant_id) {
            $variant = ProductVariant::find($orderItem->product_variant_id);
            if ($variant) {
                $variant->increment('stock', $orderItem->quantity);
            }
        } elseif ($orderItem->product_stock_id) {
            $stock = ProductStock::find($orderItem->product_stock_id);
            if ($stock) {
                $stock->increment('quantity', $orderItem->quantity);
            }
        } else {
            $product = Product::find($orderItem->product_id);
            if ($product) {
                $product->increment('stock', $orderItem->quantity);
            }
        }
    }

    /**
     * Restore stock for all items in an order.
     */
    public function restoreOrderStock(Order $order): void
    {
        foreach ($order->items as $item) {
            $this->restoreStock($item);
        }
    }

    /**
     * Get total available stock for a product (all tiers).
     */
    public function getAvailableStock(Product $product): int
    {
        if ($product->activeVariants()->exists()) {
            return (int) $product->activeVariants()->sum('stock');
        }

        $batchStock = (int) $product->stocks()->sum('quantity');
        if ($batchStock > 0) {
            return $batchStock;
        }

        return (int) ($product->stock ?? 0);
    }

    /**
     * Check if a product is in stock.
     */
    public function isInStock(Product $product): bool
    {
        if ($product->activeVariants()->exists()) {
            return $product->activeVariants()->where('stock', '>', 0)->exists();
        }

        return $this->getAvailableStock($product) > 0;
    }

    // ─── Audit Trail ─────────────────────────────────────────────────

    /**
     * Record a stock movement (restock, sale, cancellation, adjustment).
     */
    public function logMovement(
        Product $product,
        string $type,
        int $quantity,
        int $stockBefore,
        int $stockAfter,
        ?int $userId = null,
        ?string $note = null,
    ): StockMovement {
        return StockMovement::create([
            'product_id' => $product->id,
            'user_id' => $userId,
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'note' => $note,
        ]);
    }

    /**
     * Movement history for a product, newest first.
     */
    public function movementsFor(Product $product, int $limit = 50)
    {
        return StockMovement::with('user')
            ->forProduct($product->id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    // ─── Private Validation Methods ─────────────────────────────────

    protected function validateVariantStock($cartItem): void
    {
        $variant = ProductVariant::find($cartItem->product_variant_id);

        if (!$variant || !$variant->is_active || $variant->stock < $cartItem->quantity) {
            throw new InsufficientStockException(
                $cartItem->product->name,
                $variant?->stock ?? 0,
                $variant->name ?? 'this variant'
            );
        }
    }

    protected function validateBatchStock($cartItem): void
    {
        $stock = ProductStock::find($cartItem->product_stock_id);

        if (!$stock || $stock->quantity < $cartItem->quantity) {
            throw new InsufficientStockException(
                $cartItem->product->name,
                $stock?->quantity ?? 0
            );
        }
    }

    protected function validateProductStock($cartItem): void
    {
        if ($cartItem->product->stock < $cartItem->quantity) {
            throw new InsufficientStockException(
                $cartItem->product->name,
                $cartItem->product->stock
            );
        }
    }
}
