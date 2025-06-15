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
            $table->decimal('subtotal', 15, 2)->nullable()->after('total_amount');
            $table->boolean('includes_iva')->default(false)->after('subtotal');
            $table->decimal('iva_amount', 15, 2)->default(0)->after('includes_iva');
            $table->json('additional_items')->nullable()->after('warranty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'includes_iva', 'iva_amount', 'additional_items']);
        });
    }
};
