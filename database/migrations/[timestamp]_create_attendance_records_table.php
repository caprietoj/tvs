<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->string('no_id');
            $table->string('nombre_apellidos');
            $table->date('fecha');
            $table->string('entrada')->nullable();  // Cambiado a string para manejar cualquier formato
            $table->string('salida')->nullable();   // Cambiado a string para manejar cualquier formato
            $table->string('departamento');
            $table->string('mes');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_records');
    }
};
