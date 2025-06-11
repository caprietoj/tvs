<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ausentismos', function (Blueprint $table) {
            $table->id();
            $table->string('persona');
            $table->date('fecha_de_creacion');
            $table->string('dependencia');
            $table->date('fecha_ausencia_desde');
            $table->date('fecha_hasta');
            $table->string('asistencia');
            $table->string('duracion_ausencia');
            $table->string('motivo_de_ausencia');
            $table->string('mes');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ausentismos');
    }
};
