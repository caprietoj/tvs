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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->json('applied_taxes')->nullable()->after('iva_amount');
            $table->decimal('subtotal_amount', 15, 2)->nullable()->after('applied_taxes');
            $table->decimal('tax_amount_19', 15, 2)->nullable()->after('subtotal_amount');
            $table->decimal('tax_amount_8', 15, 2)->nullable()->after('tax_amount_19');
            $table->decimal('tax_amount_5', 15, 2)->nullable()->after('tax_amount_8');
            $table->decimal('tax_amount_4', 15, 2)->nullable()->after('tax_amount_5');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn([
                'applied_taxes', 
                'subtotal_amount', 
                'tax_amount_19', 
                'tax_amount_8', 
                'tax_amount_5', 
                'tax_amount_4'
            ]);
        });
    }
};
