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
        Schema::create('systems_survey_results', function (Blueprint $table) {
            $table->id();
            $table->timestamp('response_timestamp');
            $table->string('dependencia');
            $table->string('tiempos_respuesta');
            $table->string('efectividad_tecnica');
            $table->string('profesionalismo');
            $table->text('comentarios_personal')->nullable();
            $table->string('estado_equipos');
            $table->text('comentarios_equipos')->nullable();
            $table->string('apoyo_usabilidad');
            $table->string('plataformas_interaccion');
            $table->string('otra_plataforma')->nullable();
            $table->string('calidad_internet');
            $table->text('problemas_conectividad')->nullable();
            $table->string('intervencion_eventos');
            $table->text('comentarios_eventos')->nullable();
            $table->text('aspectos_destacados')->nullable();
            $table->text('oportunidades_mejora')->nullable();
            $table->integer('survey_year');
            $table->integer('survey_month');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('systems_survey_results');
    }
};
