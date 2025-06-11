<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 255);
            $table->text('descripcion');
            $table->enum('estado', ['Abierto', 'En Proceso', 'Cerrado'])->default('Abierto');
            $table->enum('prioridad', ['Baja', 'Media', 'Alta'])->default('Media');
            $table->enum('tipo_requerimiento', ['Hardware', 'Software', 'Mantenimiento', 'Instalación', 'Conectividad']);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Nueva columna para asignar un técnico (puede ser null)
            $table->foreignId('tecnico_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};