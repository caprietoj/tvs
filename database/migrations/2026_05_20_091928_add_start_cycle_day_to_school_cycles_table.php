<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega el campo start_cycle_day a la tabla school_cycles.
     *
     * Este campo define en qué número del ciclo (1-6) comienza el primer
     * día lectivo del ciclo escolar. Por defecto es 1, pero puede ajustarse
     * cuando el ciclo nuevo continúa la secuencia del ciclo anterior.
     *
     * Ejemplo: si el último día lectivo del ciclo anterior fue Día 1,
     * el nuevo ciclo debe comenzar en Día 2 → start_cycle_day = 2.
     */
    public function up(): void
    {
        Schema::table('school_cycles', function (Blueprint $table) {
            $table->unsignedTinyInteger('start_cycle_day')
                  ->default(1)
                  ->after('cycle_length')
                  ->comment('Número del ciclo (1-6) en que comienza el primer día lectivo');
        });
    }

    public function down(): void
    {
        Schema::table('school_cycles', function (Blueprint $table) {
            $table->dropColumn('start_cycle_day');
        });
    }
};
