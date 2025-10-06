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
        Schema::create('registro_porteria', function (Blueprint $table) {
            $table->id();
            $table->string('documento', 50)->index(); // Número de documento/cédula
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->enum('tipo_persona', ['empleado', 'estudiante', 'externo']); // Tipo de persona
            $table->date('fecha'); // Fecha del registro
            $table->time('hora_entrada'); // Hora de entrada
            $table->time('hora_salida')->nullable(); // Hora de salida (puede ser null)
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Usuario que registró
            $table->timestamps();
            
            // Índice compuesto para búsquedas rápidas por documento y fecha
            $table->index(['documento', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registro_porteria');
    }
};
