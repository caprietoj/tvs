<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Verificar si la columna 'status' existe antes de intentar actualizarla
        if (Schema::hasColumn('purchase_requests', 'status')) {
            // Actualizar todas las solicitudes con estado 'in_process' a 'approved'
            // Esto es para corregir el problema donde las solicitudes aprobadas 
            // se cambiaban incorrectamente a 'in_process' al generar la orden de compra
            DB::table('purchase_requests')
                ->where('status', 'in_process')
                ->update(['status' => 'approved']);
                
            // Log para registro
            $updatedCount = DB::table('purchase_requests')
                ->where('status', 'approved')
                ->whereNotNull('approval_date')
                ->count();
                
            \Log::info("Migración completada: Se actualizaron solicitudes de 'in_process' a 'approved'. Total de solicitudes aprobadas: {$updatedCount}");
        } else {
            \Log::info("Migración omitida: La columna 'status' no existe en la tabla 'purchase_requests'.");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Esta migración no es reversible de forma segura
        // ya que no sabemos cuáles solicitudes tenían originalmente 'in_process'
        \Log::warning("Reversión de migración no implementada - no es segura");
    }
};
