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
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->string('documento', 50)->unique()->index();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->enum('tipo_persona', ['empleado', 'estudiante'])->default('estudiante');
            $table->string('email', 150)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('grado', 50)->nullable()->comment('Grado o cargo');
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            // Índices para búsquedas rápidas
            $table->index(['tipo_persona', 'activo']);
            $table->index('nombre');
            $table->index('apellido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
