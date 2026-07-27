<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecentlyViewed extends Model
{
    use HasFactory;

    protected $table = 'recently_viewed';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'session_id',
        'product_id',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function track(Product $product, ?int $userId = null, ?string $sessionId = null)
    {
        // Remove existing entry for this product
        static::where('product_id', $product->id)
            ->where(function ($q) use ($userId, $sessionId) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->where('session_id', $sessionId);
                }
            })
            ->delete();

        // Add new entry
        static::create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'product_id' => $product->id,
            'viewed_at' => now(),
        ]);

        // Keep only last 20 items per user/session
        $query = static::where('product_id', '!=', $product->id);
        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }

        $idsToKeep = $query->orderBy('viewed_at', 'desc')->limit(19)->pluck('id');
        static::where(function ($q) use ($userId, $sessionId) {
            if ($userId) {
                $q->where('user_id', $userId);
            } else {
                $q->where('session_id', $sessionId);
            }
        })
        ->whereNotIn('id', $idsToKeep->merge([$product->id]))
        ->delete();
    }

    public static function getRecent(?int $userId = null, ?string $sessionId = null, int $limit = 10)
    {
        $query = static::with('product.images');

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }

        return $query->orderBy('viewed_at', 'desc')
            ->limit($limit)
            ->get()
            ->pluck('product')
            ->filter();
    }
}
