<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Product variants table - for products with multiple options (size, color, etc.)
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('sku')->unique()->nullable();
            $table->string('name'); // e.g., "100ml / Matte / Charcoal"
            $table->json('attributes'); // {"size": "100ml", "finish": "Matte", "color": "Charcoal"}
            $table->decimal('price', 12, 2)->nullable(); // null = use base product price
            $table->integer('stock')->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('product_id');
            $table->index(['product_id', 'is_active']);
        });

        // Product variant images - link images to specific variants
        Schema::create('product_variant_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->onDelete('cascade');
            $table->foreignId('product_image_id')->constrained('product_images')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['product_variant_id', 'product_image_id'], 'pvi_variant_image_unique');
        });

        // Product reviews table
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('rating')->unsigned()->comment('1-5 stars');
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->boolean('is_verified_purchase')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->integer('helpful_count')->default(0);
            $table->timestamps();

            $table->index('product_id');
            $table->index('user_id');
            $table->index(['product_id', 'is_approved']);
        });

        // Product review images
        Schema::create('product_review_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_review_id')->constrained('product_reviews')->onDelete('cascade');
            $table->string('image_path');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Product associations (upsells, cross-sells, bundles)
        Schema::create('product_associations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('related_id')->constrained('products')->onDelete('cascade');
            $table->enum('type', ['upsell', 'cross_sell', 'bundle', 'frequently_bought']);
            $table->integer('sort_order')->default(0);
            $table->decimal('bundle_price', 12, 2)->nullable(); // for bundle type
            $table->timestamps();

            $table->unique(['parent_id', 'related_id', 'type'], 'passoc_parent_related_type_unique');
            $table->index('parent_id');
            $table->index('related_id');
        });

        // Add SEO + new fields to products table
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('status');
            }
            if (!Schema::hasColumn('products', 'meta_description')) {
                $table->string('meta_description', 500)->nullable()->after('meta_title');
            }
            if (!Schema::hasColumn('products', 'brand')) {
                $table->string('brand')->nullable()->after('meta_description');
            }
            if (!Schema::hasColumn('products', 'views_count')) {
                $table->unsignedInteger('views_count')->default(0)->after('brand');
            }
        });

        // Add product_variant_id to carts table
        Schema::table('carts', function (Blueprint $table) {
            if (!Schema::hasColumn('carts', 'product_variant_id')) {
                $table->foreignId('product_variant_id')->nullable()->after('product_id')
                    ->constrained('product_variants')->onDelete('set null');
            }
        });

        // Add product_variant_id to order_items table
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'product_variant_id')) {
                $table->foreignId('product_variant_id')->nullable()->after('product_id')
                    ->constrained('product_variants')->onDelete('set null');
            }
        });

        // Recently viewed products (session-based tracking)
        Schema::create('recently_viewed', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('session_id')->nullable();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->timestamp('viewed_at');

            $table->index(['user_id', 'product_id']);
            $table->index(['session_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recently_viewed');
        Schema::dropIfExists('product_associations');
        Schema::dropIfExists('product_review_images');
        Schema::dropIfExists('product_reviews');
        Schema::dropIfExists('product_variant_images');
        Schema::dropIfExists('product_variants');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'brand', 'views_count']);
        });
    }
};
