<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->enum('request_type', [
                'mantenimiento_preventivo',
                'mantenimiento_correctivo',
                'instalaciones',
                'modificacion',
                'plomeria',
                'electricidad',
                'adecuaciones',
                'Goteras',
                'Instalacion de persianas',
                'Pintura',
                'Carpinteria',
                'Cerrajeria',
                'Vidrios',
                'Jardineria',
                'Cambio de bombillos',
                'Demarcacion de Canchas',
                'Traslado de Mobiliario',
                'Limpieza de Tanques de Agua',
                'Otros'
            ]);
            $table->string('location');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high']);
            $table->enum('status', ['pending', 'in_progress', 'completed', 'rejected']);
            $table->dateTime('completion_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
