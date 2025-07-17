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
        Schema::create('warehouse_survey_responses', function (Blueprint $table) {
            $table->id();
            $table->string('survey_period'); // Periodo de la encuesta (año-mes)
            $table->timestamp('timestamp'); // Marca temporal
            $table->string('dependencia'); // Dependencia del encuestado
            $table->enum('califica_experiencia', ['Deficiente', 'Regular', 'Bueno', 'Excelente']); // Pregunta 1
            $table->enum('califica_tiempos', ['Deficiente', 'Regular', 'Bueno', 'Excelente']); // Pregunta 2
            $table->enum('requerimiento_oportuno', ['No', 'Sí']); // Pregunta 3
            $table->enum('materiales_disponibles', ['No', 'Sí']); // Pregunta 4
            $table->text('comentarios_disponibilidad')->nullable(); // Comentarios pregunta 4
            $table->enum('califica_servicio_persona', ['Deficiente', 'Regular', 'Bueno', 'Excelente']); // Pregunta 5
            $table->enum('califica_calidad_materiales', ['Deficiente', 'Regular', 'Bueno', 'Excelente']); // Pregunta 6
            $table->text('comentarios_calidad')->nullable(); // Comentarios pregunta 6
            $table->enum('opciones_cotizaciones', ['No', 'Sí']); // Pregunta 7
            $table->text('comentarios_cotizaciones')->nullable(); // Comentarios pregunta 7
            $table->enum('proveedores_cumplen', ['No', 'Sí']); // Pregunta 8
            $table->text('comentarios_proveedores')->nullable(); // Comentarios pregunta 8
            $table->text('aspectos_destacados')->nullable(); // Pregunta 9
            $table->text('oportunidades_mejora')->nullable(); // Pregunta 10
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade'); // Usuario que subió
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index(['survey_period', 'dependencia']);
            $table->index('timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_survey_responses');
    }
};
