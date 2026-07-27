<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Attribute definitions (per-site schema)
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('site_slug'); // which site this attribute belongs to
            $table->string('code');      // e.g. 'fabric', 'weave_type', 'ingredients'
            $table->string('label');     // display label e.g. 'Fabric Type'
            $table->string('type')->default('text'); // text, textarea, select, multiselect, boolean, number
            $table->json('options')->nullable();     // for select/multiselect types
            $table->boolean('is_required')->default(false);
            $table->boolean('is_filterable')->default(false); // show in sidebar filters
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['site_slug', 'code']);
            $table->index('site_slug');
        });

        // Attribute values (EAV)
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_attribute_id')->constrained()->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'product_attribute_id']);
            $table->index('product_attribute_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('product_attributes');
    }
};
