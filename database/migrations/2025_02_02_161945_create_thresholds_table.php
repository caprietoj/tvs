<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThresholdsTable extends Migration
{
    public function up()
    {
        Schema::create('thresholds', function (Blueprint $table) {
            $table->id();
            $table->string('area');           // Área, ej: 'enfermeria'
            $table->string('kpi_name');       // Nombre del KPI (configurado)
            $table->decimal('value', 5, 2)->default(80.00); // Valor del umbral (%)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('thresholds');
    }
}
