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
        Schema::create('previsita_archivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('previsita_consolidado_id')->constrained('previsita_consolidados')->onDelete('cascade');
            $table->string('nombre_original'); // Nombre original del archivo
            $table->string('nombre_archivo'); // Nombre del archivo almacenado
            $table->string('ruta_archivo'); // Ruta completa del archivo
            $table->string('tipo_archivo'); // pdf, image
            $table->string('mime_type'); // application/pdf, image/jpeg, etc.
            $table->bigInteger('tamaño_archivo'); // Tamaño en bytes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('previsita_archivos');
    }
};
