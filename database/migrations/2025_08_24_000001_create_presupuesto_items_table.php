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
        Schema::create('presupuesto_items', function (Blueprint $table) {
            $table->id();
            $table->string('fuente')->nullable();
            $table->string('documento')->nullable();
            $table->date('fecha')->nullable();
            $table->string('cuenta')->nullable();
            $table->string('seccion')->nullable();  // NUEVA COLUMNA
            $table->string('rubro')->nullable();    // NUEVA COLUMNA
            $table->text('descripcion')->nullable();
            $table->decimal('valor', 15, 2)->default(0);
            $table->decimal('valor_moneda', 15, 2)->default(0);
            $table->string('cliente_proveedor')->nullable();
            $table->string('nombre_cliente_proveedor')->nullable();
            $table->string('tercero')->nullable();
            $table->string('nombre_tercero')->nullable();
            $table->string('auxiliar')->nullable();
            $table->string('centro_costo')->nullable();
            $table->boolean('es_total')->default(false); // Para identificar filas de totales
            $table->timestamps();
            
            // Índices para mejorar rendimiento
            $table->index(['seccion', 'rubro']);
            $table->index('centro_costo');
            $table->index('es_total');
            $table->index('fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuesto_items');
    }
};
