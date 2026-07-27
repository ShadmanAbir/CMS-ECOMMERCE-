<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockAlert;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StockAlertService
{
    /**
     * Record a low-stock alert for a product.
     *
     * If an unresolved alert already exists for the product it is refreshed
     * with the latest stock level instead of creating a duplicate, so the
     * admin sees one row per product until it is restocked/resolved.
     */
    public function record(Product $product, int $threshold): StockAlert
    {
        $existing = StockAlert::unresolved()
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->update([
                'stock_at_alert' => $product->totalStock(),
                'threshold' => $threshold,
            ]);

            return $existing;
        }

        return StockAlert::create([
            'product_id' => $product->id,
            'stock_at_alert' => $product->totalStock(),
            'threshold' => $threshold,
            'is_resolved' => false,
        ]);
    }

    /**
     * Paginated list of alerts filtered by status.
     *
     * @param  string  $filter  unresolved|resolved|all
     */
    public function list(string $filter = 'unresolved', int $perPage = 20): LengthAwarePaginator
    {
        $query = StockAlert::with('product')->latest();

        if ($filter === 'unresolved') {
            $query->unresolved();
        } elseif ($filter === 'resolved') {
            $query->resolved();
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Status counts for the filter tabs.
     *
     * @return array{unresolved:int, resolved:int, all:int}
     */
    public function counts(): array
    {
        return [
            'unresolved' => StockAlert::unresolved()->count(),
            'resolved' => StockAlert::resolved()->count(),
            'all' => StockAlert::count(),
        ];
    }

    /**
     * Number of open alerts (used for the sidebar badge).
     */
    public function unresolvedCount(): int
    {
        return StockAlert::unresolved()->count();
    }

    /**
     * Mark an alert as resolved.
     */
    public function resolve(StockAlert $alert): StockAlert
    {
        $alert->update([
            'is_resolved' => true,
            'resolved_at' => now(),
        ]);

        return $alert;
    }

    /**
     * Resolve every open alert in one pass. Returns the number resolved.
     */
    public function resolveAll(): int
    {
        return StockAlert::unresolved()->update([
            'is_resolved' => true,
            'resolved_at' => now(),
        ]);
    }

    /**
     * Auto-resolve a product's open alert once its stock has climbed back
     * above the threshold that raised it. Called after a restock so admins
     * don't have to clear alerts by hand. Returns the resolved alert, or null
     * if there was no open alert or the product is still low.
     */
    public function resolveIfRestocked(Product $product): ?StockAlert
    {
        $alert = StockAlert::unresolved()
            ->where('product_id', $product->id)
            ->first();

        if (!$alert) {
            return null;
        }

        if ($product->totalStock() > $alert->threshold) {
            return $this->resolve($alert);
        }

        return null;
    }

    /**
     * Every open alert with its product, newest first — the payload for the
     * low-stock digest email.
     */
    public function openAlerts(): Collection
    {
        return StockAlert::with('product')->unresolved()->latest()->get();
    }

    /**
     * Who should receive the low-stock digest. Every admin user, falling back
     * to the site's contact address (then the app's from-address) when no
     * admin accounts exist.
     *
     * @return array<int, string>
     */
    public function digestRecipients(): array
    {
        $admins = User::where('role', 'admin')
            ->whereNotNull('email')
            ->pluck('email')
            ->all();

        if (!empty($admins)) {
            return $admins;
        }

        $fallback = theme('contact.email', config('mail.from.address'));

        return $fallback ? [$fallback] : [];
    }
}
