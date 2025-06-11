<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('evaluacion_proveedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedors')->onDelete('cascade');
            $table->string('numero_contrato');
            $table->date('fecha_evaluacion');
            $table->string('lugar_evaluacion');
            $table->decimal('cumplimiento_entrega', 3, 1);
            $table->decimal('calidad_especificaciones', 3, 1);
            $table->decimal('documentacion_garantias', 3, 1);
            $table->decimal('servicio_postventa', 3, 1);
            $table->decimal('precio', 3, 1);
            $table->decimal('capacidad_instalada', 3, 1);
            $table->decimal('soporte_tecnico', 3, 1);
            $table->decimal('puntaje_total', 3, 1);
            $table->text('observaciones')->nullable();
            $table->string('evaluado_por');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluacion_proveedores');
    }
};
