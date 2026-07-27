<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            // Drop old unique index and recreate with variant_id
            $table->dropUnique(['user_id', 'product_id', 'product_stock_id', 'size', 'color']);
            $table->unique(['user_id', 'product_id', 'product_variant_id', 'product_stock_id', 'size', 'color'], 'carts_unique_item');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropUnique('carts_unique_item');
            $table->unique(['user_id', 'product_id', 'product_stock_id', 'size', 'color']);
        });
    }
};
