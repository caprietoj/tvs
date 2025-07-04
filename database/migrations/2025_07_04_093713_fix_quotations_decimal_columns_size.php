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
            // Cambiar las columnas de impuestos que usan decimal(12,2) a decimal(15,2)
            // para manejar valores más grandes consistentemente
            $table->decimal('iva_19_amount', 15, 2)->change();
            $table->decimal('iva_5_amount', 15, 2)->change();
            $table->decimal('ipoconsumo_8_amount', 15, 2)->change();
            $table->decimal('ipoconsumo_4_amount', 15, 2)->change();
        });
        
        Schema::table('purchase_orders', function (Blueprint $table) {
            // También actualizar las columnas de purchase_orders para consistencia
            $table->decimal('total_amount', 15, 2)->change();
            $table->decimal('subtotal', 15, 2)->change();
            $table->decimal('iva_amount', 15, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            // Revertir las columnas de impuestos a decimal(12,2)
            $table->decimal('iva_19_amount', 12, 2)->change();
            $table->decimal('iva_5_amount', 12, 2)->change();
            $table->decimal('ipoconsumo_8_amount', 12, 2)->change();
            $table->decimal('ipoconsumo_4_amount', 12, 2)->change();
        });
        
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Revertir las columnas de purchase_orders a decimal(12,2)
            $table->decimal('total_amount', 12, 2)->change();
            $table->decimal('subtotal', 12, 2)->change();
            $table->decimal('iva_amount', 12, 2)->change();
        });
    }
};
