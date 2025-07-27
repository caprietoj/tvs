<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Actualizar órdenes de compra que tienen order_date como null
        // usando la fecha de creación como fecha de orden
        DB::statement("
            UPDATE purchase_orders 
            SET order_date = DATE(created_at) 
            WHERE order_date IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No es necesario revertir, ya que estamos corrigiendo datos inconsistentes
        // Si se requiere, se podría setear order_date a null nuevamente, pero no es recomendable
    }
};
