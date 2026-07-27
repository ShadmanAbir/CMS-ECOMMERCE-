<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAssociation extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'related_id',
        'type',
        'sort_order',
        'bundle_price',
    ];

    protected $casts = [
        'bundle_price' => 'decimal:2',
    ];

    public function parent()
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    public function related()
    {
        return $this->belongsTo(Product::class, 'related_id');
    }

    public function scopeUpsells($query)
    {
        return $query->where('type', 'upsell');
    }

    public function scopeCrossSells($query)
    {
        return $query->where('type', 'cross_sell');
    }

    public function scopeBundles($query)
    {
        return $query->where('type', 'bundle');
    }
}
