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
        // Crear tabla de secciones
        Schema::create('secciones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
            
            $table->index('codigo');
            $table->index('activa');
        });

        // Crear tabla de rubros
        Schema::create('rubros', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index('codigo');
            $table->index('activo');
        });

        // Crear tabla de centros de costo
        Schema::create('centros_costo', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique();
            $table->string('nombre', 100);
            $table->foreignId('seccion_id')->constrained('secciones')->onDelete('cascade');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index('codigo');
            $table->index('seccion_id');
            $table->index('activo');
        });

        // Crear tabla de cuentas
        Schema::create('cuentas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 100);
            $table->foreignId('rubro_id')->constrained('rubros')->onDelete('cascade');
            $table->boolean('activa')->default(true);
            $table->timestamps();
            
            $table->index('codigo');
            $table->index('rubro_id');
            $table->index('activa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuentas');
        Schema::dropIfExists('centros_costo');
        Schema::dropIfExists('rubros');
        Schema::dropIfExists('secciones');
    }
};
