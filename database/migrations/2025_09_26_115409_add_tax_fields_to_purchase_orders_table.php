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
            // Verificar si las columnas no existen antes de agregarlas
            if (!Schema::hasColumn('purchase_orders', 'iva_rate')) {
                $table->decimal('iva_rate', 5, 2)->nullable()->default(0)->comment('Tasa de IVA aplicada (0, 5, 19)');
            }
            if (!Schema::hasColumn('purchase_orders', 'ipoconsumo_rate')) {
                $table->decimal('ipoconsumo_rate', 5, 2)->nullable()->default(0)->comment('Tasa de Impuesto al Consumo (0, 4, 8)');
            }
            if (!Schema::hasColumn('purchase_orders', 'force_global_taxes')) {
                $table->boolean('force_global_taxes')->default(false)->comment('Forzar aplicación de impuestos globales en lugar de por ítem');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['iva_rate', 'ipoconsumo_rate', 'force_global_taxes']);
        });
    }
};
