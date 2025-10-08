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
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->string('curso', 50);
            $table->string('nombre');
            $table->string('apellido_1');
            $table->string('apellido_2')->nullable();
            $table->string('codigo', 50)->unique();
            $table->string('documento', 50)->unique();
            $table->string('eps')->nullable();
            $table->enum('sexo', ['M', 'F', 'Masculino', 'Femenino']);
            $table->string('tipo_sangre', 10)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index(['curso']);
            $table->index(['documento']);
            $table->index(['codigo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
