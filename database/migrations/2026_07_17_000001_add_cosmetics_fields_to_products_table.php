<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('ingredients')->nullable()->after('tags');
            $table->text('how_to_use')->nullable()->after('ingredients');
            $table->json('benefits')->nullable()->after('how_to_use');
            $table->string('volume')->nullable()->after('benefits')->comment('e.g., 100ml, 200ml');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['ingredients', 'how_to_use', 'benefits', 'volume']);
        });
    }
};
