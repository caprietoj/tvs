<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSistemasThresholdsTable extends Migration
{
    public function up()
    {
        Schema::create('sistemas_thresholds', function (Blueprint $table) {
            $table->id();
            $table->string('kpi_name'); // Nombre del KPI configurado para Sistemas
            $table->decimal('value', 5, 2)->default(80.00); // Valor del umbral
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sistemas_thresholds');
    }
}
