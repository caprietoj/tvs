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
        Schema::table('quotation_item_selections', function (Blueprint $table) {
            // Cambiar el tipo de dato de total_price para soportar valores más grandes
            // decimal(15, 2) permite hasta 13 dígitos enteros + 2 decimales = 9,999,999,999,999.99
            $table->decimal('total_price', 15, 2)->change();
            
            // También cambiar unit_price por consistencia
            $table->decimal('unit_price', 15, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_item_selections', function (Blueprint $table) {
            // Revertir a los tipos originales
            $table->decimal('total_price', 10, 2)->change();
            $table->decimal('unit_price', 10, 2)->change();
        });
    }
};
