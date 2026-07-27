<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    protected $fillable = [
        'site_slug',
        'code',
        'label',
        'type',
        'options',
        'is_required',
        'is_filterable',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_filterable' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function values()
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    /**
     * Scope: attributes for a specific site
     */
    public function scopeForSite($query, string $siteSlug)
    {
        return $query->where('site_slug', $siteSlug);
    }

    /**
     * Scope: only filterable attributes (for sidebar)
     */
    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }
}
