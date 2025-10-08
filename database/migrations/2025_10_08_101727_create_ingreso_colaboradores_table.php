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
        Schema::create('ingreso_colaboradores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->nullable()->constrained('empleados')->onDelete('set null');
            $table->date('fecha');
            $table->time('hora');
            $table->string('nombre_completo'); // Nombre completo del colaborador
            $table->string('email')->nullable(); // Email del colaborador
            $table->string('documento_colaborador', 50); // Documento
            $table->string('eps_colaborador')->nullable();
            $table->string('sexo_colaborador', 1)->nullable();
            $table->string('tipo_sangre_colaborador', 10)->nullable();
            $table->string('motivo', 500);
            $table->text('descripcion_evento');
            $table->text('accion_enfermeria');
            $table->string('seguimiento', 1000)->nullable();
            $table->string('derivacion_colaborador', 500)->nullable();
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
        Schema::dropIfExists('ingreso_colaboradores');
    }
};
