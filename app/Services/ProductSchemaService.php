<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProductSchemaService
{
    /**
     * Get the attribute schema for the active site.
     * Reads from site.php 'product_schema' config.
     */
    public function getSchema(): array
    {
        return theme('product_schema', []);
    }

    /**
     * Sync attribute definitions from site config into the database.
     * Run on site activation or via artisan command.
     */
    public function syncSchemaToDatabase(?string $siteSlug = null): void
    {
        $this->ensureTablesExist();

        $siteSlug = $siteSlug ?? theme('slug', 'default');
        $schema = $siteSlug === theme('slug')
            ? $this->getSchema()
            : $this->getSchemaForSite($siteSlug);

        foreach ($schema as $index => $attr) {
            ProductAttribute::updateOrCreate(
                ['site_slug' => $siteSlug, 'code' => $attr['code']],
                [
                    'label' => $attr['label'],
                    'type' => $attr['type'] ?? 'text',
                    'options' => $attr['options'] ?? null,
                    'is_required' => $attr['is_required'] ?? false,
                    'is_filterable' => $attr['is_filterable'] ?? false,
                    'sort_order' => $index,
                ]
            );
        }
    }

    /**
     * Get attribute definitions for the active site from DB.
     */
    public function getAttributes(?string $siteSlug = null): \Illuminate\Database\Eloquent\Collection
    {
        $siteSlug = $siteSlug ?? theme('slug', 'default');

        return ProductAttribute::forSite($siteSlug)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get filterable attributes (for sidebar filters).
     */
    public function getFilterableAttributes(?string $siteSlug = null): \Illuminate\Database\Eloquent\Collection
    {
        $siteSlug = $siteSlug ?? theme('slug', 'default');

        return ProductAttribute::forSite($siteSlug)
            ->filterable()
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get attribute values for a product (keyed by attribute code).
     */
    public function getProductAttributes(Product $product): array
    {
        return $product->attributeValues()
            ->with('attribute')
            ->get()
            ->mapWithKeys(function ($av) {
                return [$av->attribute->code => [
                    'label' => $av->attribute->label,
                    'type' => $av->attribute->type,
                    'value' => $av->value,
                ]];
            })
            ->toArray();
    }

    /**
     * Save attribute values for a product.
     */
    public function saveProductAttributes(Product $product, array $attributeData): void
    {
        $siteSlug = theme('slug', 'default');
        $attributes = ProductAttribute::forSite($siteSlug)->get()->keyBy('code');

        foreach ($attributeData as $code => $value) {
            if (!isset($attributes[$code])) {
                continue;
            }

            ProductAttributeValue::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'product_attribute_id' => $attributes[$code]->id,
                ],
                ['value' => is_array($value) ? json_encode($value) : $value]
            );
        }
    }

    /**
     * Read schema from a specific site's config file.
     */
    protected function getSchemaForSite(string $siteSlug): array
    {
        $configPath = base_path("sites/{$siteSlug}/site.php");

        if (!file_exists($configPath)) {
            return [];
        }

        $config = require $configPath;

        return $config['product_schema'] ?? [];
    }

    /**
     * Ensure EAV tables exist in the active database connection.
     * Each white-label site has its own database which may predate
     * the product attributes migration, so sync is self-healing.
     */
    protected function ensureTablesExist(): void
    {
        if (!Schema::hasTable('product_attributes')) {
            Schema::create('product_attributes', function (Blueprint $table) {
                $table->id();
                $table->string('site_slug');
                $table->string('code');
                $table->string('label');
                $table->string('type')->default('text');
                $table->json('options')->nullable();
                $table->boolean('is_required')->default(false);
                $table->boolean('is_filterable')->default(false);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['site_slug', 'code']);
            });
        }

        if (!Schema::hasTable('product_attribute_values')) {
            Schema::create('product_attribute_values', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_attribute_id')->constrained()->cascadeOnDelete();
                $table->text('value')->nullable();
                $table->timestamps();
                $table->unique(['product_id', 'product_attribute_id']);
            });
        }
    }
}
