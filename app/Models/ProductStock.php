<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'batch_code',
        'quantity',
        'price',
        'purchased_at',
        'expire_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'purchased_at' => 'date',
        'expire_at' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }
}
