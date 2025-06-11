<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('proveedors', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('nit')->unique();
            $table->string('direccion');
            $table->string('ciudad');
            $table->string('telefono');
            $table->string('email')->unique();
            $table->string('persona_contacto');
            $table->string('servicio_producto');
            $table->boolean('proveedor_critico')->default(false);
            $table->boolean('alto_riesgo')->default(false);
            $table->string('camara_comercio')->nullable();
            $table->string('rut')->nullable();
            $table->string('cedula_representante')->nullable();
            $table->string('certificacion_bancaria')->nullable();
            $table->string('seguridad_social')->nullable();
            $table->string('certificacion_alturas')->nullable();
            $table->string('matriz_peligros')->nullable();
            $table->string('matriz_epp')->nullable();
            $table->string('estadisticas')->nullable();
            $table->string('forma_pago')->nullable();
            $table->decimal('descuento', 5, 2)->nullable();
            $table->integer('cobertura')->nullable();
            $table->integer('referencias_comerciales')->nullable();
            $table->string('nivel_precios')->nullable();
            $table->text('valores_agregados')->nullable();
            $table->integer('puntaje_forma_pago')->nullable();
            $table->integer('puntaje_referencias')->nullable();
            $table->integer('puntaje_descuento')->nullable();
            $table->integer('puntaje_cobertura')->nullable();
            $table->integer('puntaje_valores_agregados')->nullable();
            $table->integer('puntaje_precios')->nullable();
            $table->integer('puntaje_criterios_tecnicos')->nullable();
            $table->integer('puntaje_total')->nullable();
            $table->enum('estado', ['Seleccionado', 'No Seleccionado'])->nullable();
            $table->timestamps();
            $table->string('camara_comercio_path')->nullable();
            $table->string('rut_path')->nullable();
            $table->string('cedula_representante_path')->nullable();
            $table->string('certificacion_bancaria_path')->nullable();
            $table->string('seguridad_social_path')->nullable();
            $table->string('certificacion_alturas_path')->nullable();
            $table->string('matriz_peligros_path')->nullable();
            $table->string('matriz_epp_path')->nullable();
            $table->string('estadisticas_path')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('proveedors');
    }
};
