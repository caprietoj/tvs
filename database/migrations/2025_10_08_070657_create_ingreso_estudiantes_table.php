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
        Schema::create('ingreso_estudiantes', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->time('hora');
            $table->string('estudiante');
            $table->string('curso', 50);
            $table->string('motivo', 500);
            $table->text('descripcion_evento');
            $table->text('accion_enfermeria');
            $table->string('seguimiento', 1000)->nullable();
            $table->string('derivacion_estudiante', 500)->nullable();
            $table->string('encuesta', 500)->nullable();
            $table->text('encuesta_observaciones')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // Usuario que registra
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingreso_estudiantes');
    }
};
