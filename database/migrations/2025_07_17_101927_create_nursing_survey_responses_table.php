<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('nursing_survey_responses', function (Blueprint $table) {
            $table->id();
            $table->string('survey_period'); // Formato: YYYY-MM
            $table->timestamp('timestamp'); // Marca temporal
            $table->string('dependencia'); // Seleccione su dependencia
            $table->string('experiencia_enfermeria'); // 1. ¿Cómo califica su experiencia con el área de enfermería?
            $table->string('presentacion_personal'); // 2. ¿Considera que la presentación personal...?
            $table->text('comentarios_presentacion')->nullable(); // Comentarios presentación
            $table->string('disponibilidad_personal'); // 3. ¿Cómo califica la disponibilidad del personal?
            $table->text('comentarios_disponibilidad')->nullable(); // Comentarios disponibilidad
            $table->string('profesionalismo'); // 4. ¿Considera que el personal actúa con profesionalismo?
            $table->text('comentarios_profesionalismo')->nullable(); // Comentarios profesionalismo
            $table->string('respuesta_efectiva'); // 5. ¿Considera que la respuesta del área es efectiva?
            $table->text('comentarios_respuesta')->nullable(); // Comentarios respuesta
            $table->string('limpieza_orden'); // 6. ¿Cómo califica la limpieza y orden?
            $table->text('comentarios_limpieza')->nullable(); // Comentarios limpieza
            $table->string('reportes_oportunos'); // 7. ¿El área realiza reportes oportunos?
            $table->text('comentarios_reportes')->nullable(); // Comentarios reportes
            $table->string('claridad_reportes'); // 8. ¿Considera que los reportes son claros?
            $table->unsignedBigInteger('uploaded_by'); // Usuario que subió
            $table->timestamp('uploaded_at')->nullable(); // Fecha de subida
            $table->timestamps();
            
            $table->foreign('uploaded_by')->references('id')->on('users');
            $table->index(['survey_period', 'dependencia']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('nursing_survey_responses');
    }
};
