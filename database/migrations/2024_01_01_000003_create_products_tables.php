<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Products table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->json('tags')->nullable();
            $table->string('remarks')->nullable()->comment('Trending, New Arrival, Features, etc.');
            $table->json('sizes')->nullable();
            $table->json('colors')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->decimal('weight', 8, 2)->nullable();
            $table->enum('status', ['draft', 'active', 'inactive', 'out_of_stock'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['category_id', 'status']);
            $table->index('slug');
            $table->index('sku');
            $table->index('remarks');
            // $table->fullText(['name', 'description']); // SQLite doesn't support fulltext
        });

        // Product images table
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('image_path');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            
            $table->index('product_id');
        });

        // Product stocks table (batch-based inventory)
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('batch_code')->nullable();
            $table->integer('quantity');
            $table->decimal('price', 12, 2)->default(0);
            $table->date('purchased_at');
            $table->date('expire_at')->nullable();
            $table->timestamps();
            
            $table->index('product_id');
            $table->index('batch_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stocks');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
    }
};
