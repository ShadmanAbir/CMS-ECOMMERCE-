<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // This migration was for the old 'customer_orders' table.
        // The current schema uses 'orders' which already has these columns.
        if (!Schema::hasTable('customer_orders')) {
            return;
        }

        Schema::table('customer_orders', function (Blueprint $table) {
            // Add missing columns that the code expects
            if (!Schema::hasColumn('customer_orders', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('customer_orders', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('subtotal');
            }
            if (!Schema::hasColumn('customer_orders', 'vat_amount')) {
                $table->decimal('vat_amount', 10, 2)->default(0)->after('discount_amount');
            }
            if (!Schema::hasColumn('customer_orders', 'shipping_cost')) {
                $table->decimal('shipping_cost', 10, 2)->default(0)->after('vat_amount');
            }
            if (!Schema::hasColumn('customer_orders', 'shipping_address_id')) {
                $table->unsignedBigInteger('shipping_address_id')->nullable()->after('shipping_cost');
            }
            if (!Schema::hasColumn('customer_orders', 'notes')) {
                $table->text('notes')->nullable()->after('shipping_address_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('customer_orders')) {
            return;
        }

        Schema::table('customer_orders', function (Blueprint $table) {
            $table->dropColumnIfExists('subtotal');
            $table->dropColumnIfExists('discount_amount');
            $table->dropColumnIfExists('vat_amount');
            $table->dropColumnIfExists('shipping_cost');
            $table->dropColumnIfExists('shipping_address_id');
            $table->dropColumnIfExists('notes');
        });
    }
};
