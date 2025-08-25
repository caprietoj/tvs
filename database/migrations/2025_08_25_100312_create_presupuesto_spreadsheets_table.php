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
        Schema::create('presupuesto_spreadsheets', function (Blueprint $table) {
            $table->id();
            $table->string('tabla_nombre'); // Nombre de la tabla (RESUMEN, INGRESOS ESCOLARES, etc.)
            $table->string('concepto'); // Concepto de la fila (Total Ingresos, Matriculas, etc.)
            $table->string('columna'); // Nombre de la columna (JULIO, AGOSTO, etc.)
            $table->decimal('valor', 15, 2)->default(0); // Valor de la celda
            $table->integer('fila_orden')->default(0); // Orden de la fila en la tabla
            $table->integer('columna_orden')->default(0); // Orden de la columna
            $table->boolean('es_total')->default(false); // Si es una fila de total
            $table->string('tipo_dato')->default('manual'); // manual, calculado, presupuesto
            $table->timestamps();
            
            // Índices para búsqueda rápida
            $table->unique(['tabla_nombre', 'concepto', 'columna'], 'unique_celda');
            $table->index(['tabla_nombre', 'es_total']);
            $table->index('tipo_dato');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuesto_spreadsheets');
    }
};
