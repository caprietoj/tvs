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
        Schema::table('quotations', function (Blueprint $table) {
            $table->json('original_item_prices')->nullable()->after('additional_items');
            $table->json('original_item_totals')->nullable()->after('original_item_prices');
            $table->json('original_item_taxes')->nullable()->after('original_item_totals');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['original_item_prices', 'original_item_totals', 'original_item_taxes']);
        });
    }
};
