<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecursosHumanosThresholdsTable extends Migration
{
    public function up()
    {
        Schema::create('recursos_humanos_thresholds', function (Blueprint $table) {
            $table->id();
            $table->string('kpi_name');          // Nombre del KPI configurado para RRHH
            $table->decimal('value', 5, 2)->default(80.00); // Valor del umbral
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('recursos_humanos_thresholds');
    }
}
