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
        Schema::table('salidas_pedagogicas', function (Blueprint $table) {
            // Campos para registrar qué usuario confirmó cada servicio
            $table->unsignedBigInteger('transporte_confirmado_por')->nullable()->after('transporte_confirmado');
            $table->timestamp('transporte_confirmado_at')->nullable()->after('transporte_confirmado_por');
            
            $table->unsignedBigInteger('alimentacion_confirmada_por')->nullable()->after('alimentacion_confirmada');
            $table->timestamp('alimentacion_confirmada_at')->nullable()->after('alimentacion_confirmada_por');
            
            $table->unsignedBigInteger('accesos_confirmados_por')->nullable()->after('accesos_confirmados');
            $table->timestamp('accesos_confirmados_at')->nullable()->after('accesos_confirmados_por');
            
            $table->unsignedBigInteger('enfermeria_confirmada_por')->nullable()->after('enfermeria_confirmada');
            $table->timestamp('enfermeria_confirmada_at')->nullable()->after('enfermeria_confirmada_por');
            
            // Relaciones con la tabla users
            $table->foreign('transporte_confirmado_por')->references('id')->on('users')->onDelete('set null');
            $table->foreign('alimentacion_confirmada_por')->references('id')->on('users')->onDelete('set null');
            $table->foreign('accesos_confirmados_por')->references('id')->on('users')->onDelete('set null');
            $table->foreign('enfermeria_confirmada_por')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salidas_pedagogicas', function (Blueprint $table) {
            // Eliminar las foreign keys primero
            $table->dropForeign(['transporte_confirmado_por']);
            $table->dropForeign(['alimentacion_confirmada_por']);
            $table->dropForeign(['accesos_confirmados_por']);
            $table->dropForeign(['enfermeria_confirmada_por']);
            
            // Eliminar las columnas
            $table->dropColumn([
                'transporte_confirmado_por',
                'transporte_confirmado_at',
                'alimentacion_confirmada_por',
                'alimentacion_confirmada_at',
                'accesos_confirmados_por',
                'accesos_confirmados_at',
                'enfermeria_confirmada_por',
                'enfermeria_confirmada_at'
            ]);
        });
    }
};
