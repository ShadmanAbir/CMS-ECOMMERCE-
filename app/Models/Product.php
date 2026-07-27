<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'vendor_id',
        'name',
        'slug',
        'description',
        'price',
        'discount',
        'stock',
        'sku',
        'tags',
        'ingredients',
        'how_to_use',
        'benefits',
        'volume',
        'sizes',
        'colors',
        'remarks',
        'weight',
        'status',
        'meta_title',
        'meta_description',
        'brand',
        'views_count',
        'is_trending',
        'is_new',
        'is_featured',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'weight' => 'decimal:2',
        'tags' => 'array',
        'ingredients' => 'array',
        'benefits' => 'array',
        'sizes' => 'array',
        'colors' => 'array',
        'is_trending' => 'boolean',
        'is_new' => 'boolean',
        'is_featured' => 'boolean',
        'views_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    // ─── Relationships ───────────────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function attributeValues()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function activeVariants()
    {
        return $this->hasMany(ProductVariant::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function defaultVariant()
    {
        return $this->hasOne(ProductVariant::class)->where('is_default', true);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function approvedReviews()
    {
        return $this->hasMany(ProductReview::class)->where('is_approved', true);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function upsells()
    {
        return $this->belongsToMany(Product::class, 'product_associations', 'parent_id', 'related_id')
            ->wherePivot('type', 'upsell')
            ->withPivot('sort_order');
    }

    public function crossSells()
    {
        return $this->belongsToMany(Product::class, 'product_associations', 'parent_id', 'related_id')
            ->wherePivot('type', 'cross_sell')
            ->withPivot('sort_order');
    }

    public function associations()
    {
        return $this->hasMany(ProductAssociation::class, 'parent_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch($query, $keyword)
    {
        if (empty($keyword)) {
            return $query;
        }

        return $query->where(function ($q) use ($keyword) {
            $q->where('name', 'like', '%' . $keyword . '%')
              ->orWhere('description', 'like', '%' . $keyword . '%')
              ->orWhere('sku', 'like', '%' . $keyword . '%')
              ->orWhere('brand', 'like', '%' . $keyword . '%')
              ->orWhereJsonContains('tags', $keyword);
        });
    }

    public function scopeTrending($query)
    {
        return $query->where('is_trending', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeNewArrivals($query)
    {
        return $query->where('is_new', true);
    }

    public function scopeOnSale($query)
    {
        return $query->where('discount', '>', 0);
    }

    public function scopePriceRange($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // ─── Accessors & Helpers ─────────────────────────────────────────

    public function isInStock(): bool
    {
        return app(\App\Services\InventoryService::class)->isInStock($this);
    }

    public function totalStock(): int
    {
        return app(\App\Services\InventoryService::class)->getAvailableStock($this);
    }

    public function isNew(): bool
    {
        return $this->is_new || $this->created_at > now()->subDays(30);
    }

    public function getDiscountedPrice(): float
    {
        return app(\App\Services\PricingService::class)->getProductPrice($this);
    }

    public function getAverageRatingAttribute()
    {
        return round($this->approvedReviews()->avg('rating') ?? 0, 1);
    }

    public function getReviewsCountAttribute()
    {
        return $this->approvedReviews()->count();
    }

    public function getPrimaryImageAttribute()
    {
        $primary = $this->images->firstWhere('is_primary', true);
        return $primary ?? $this->images->first();
    }

    public function getMinPriceAttribute()
    {
        $variantPrices = $this->activeVariants()->whereNotNull('price')->min('price');
        return $variantPrices ? min($this->price, $variantPrices) : $this->price;
    }

    public function getMaxPriceAttribute()
    {
        $variantPrices = $this->activeVariants()->whereNotNull('price')->max('price');
        return $variantPrices ? max($this->price, $variantPrices) : $this->price;
    }

    public function hasVariants(): bool
    {
        return $this->activeVariants()->exists();
    }

    public function getVariantOptions(): array
    {
        $options = [];
        $variants = $this->activeVariants()->get();
        foreach ($variants as $variant) {
            $attrs = json_decode($variant->getRawOriginal('attributes'), true) ?? [];
            foreach ($attrs as $key => $value) {
                if (!isset($options[$key])) {
                    $options[$key] = [];
                }
                if (!in_array($value, $options[$key])) {
                    $options[$key][] = $value;
                }
            }
        }
        return $options;
    }

    public function incrementViewsCount(): void
    {
        $this->increment('views_count');
    }
}
