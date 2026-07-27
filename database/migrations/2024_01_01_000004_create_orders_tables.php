<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Customer addresses table
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('address');
            $table->string('city')->nullable();
            $table->string('country')->default('Bangladesh');
            $table->string('postal_code')->nullable();
            $table->string('division');
            $table->string('district');
            $table->string('thana');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('user_id');
        });

        // Carts table
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('guest_session_id')->nullable()->constrained('guest_sessions')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_stock_id')->nullable()->constrained('product_stocks')->onDelete('set null');
            $table->integer('quantity')->default(1);
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->decimal('price', 12, 2);
            $table->timestamps();
            
            $table->index(['user_id', 'guest_session_id']);
            $table->index('product_id');
            $table->unique(['user_id', 'product_id', 'product_stock_id', 'size', 'color']);
        });

        // Wishlists table
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('guest_session_id')->nullable()->constrained('guest_sessions')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['user_id', 'guest_session_id']);
            $table->index('product_id');
            $table->unique(['user_id', 'product_id']);
            $table->unique(['guest_session_id', 'product_id']);
        });

        // Orders table with custom order numbers
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('vat_amount', 12, 2)->default(0);
            $table->decimal('shipping_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('transaction_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('user_id');
            $table->index('order_number');
            $table->index(['status', 'payment_status']);
        });

        // Order items table
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_stock_id')->nullable()->constrained('product_stocks')->onDelete('set null');
            $table->integer('quantity');
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamps();
            
            $table->index('order_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('customer_addresses');
    }
};
