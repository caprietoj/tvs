<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('salidas_pedagogicas', function (Blueprint $table) {
            $table->id();
            $table->string('consecutivo')->unique();
            $table->date('fecha_solicitud');
            $table->boolean('calendario_general')->default(false);
            $table->string('grados');
            $table->string('lugar');
            $table->foreignId('responsable_id')->constrained('users');
            $table->dateTime('fecha_salida');
            $table->dateTime('fecha_regreso');
            $table->integer('cantidad_pasajeros');
            $table->text('observaciones')->nullable();
            
            // Visita de inspección
            $table->boolean('visita_inspeccion')->default(false);
            $table->text('detalles_inspeccion')->nullable();
            $table->string('contacto_lugar')->nullable();
            
            // Transporte (Metro Junior)
            $table->boolean('transporte_confirmado')->default(false);
            $table->string('hora_salida_bus')->nullable();
            $table->string('hora_regreso_bus')->nullable();
            
            // Alimentación (Aldimark)
            $table->boolean('requiere_alimentacion')->default(false);
            $table->integer('cantidad_snacks')->nullable();
            $table->integer('cantidad_almuerzos')->nullable();
            $table->time('hora_entrega_alimentos')->nullable();
            $table->text('menu_sugerido')->nullable();
            $table->text('observaciones_dieteticas')->nullable();
            $table->boolean('alimentacion_confirmada')->default(false);
            
            // Control de acceso
            $table->time('hora_apertura_puertas')->nullable();
            $table->boolean('accesos_confirmados')->default(false);
            
            // Enfermería
            $table->boolean('requiere_enfermeria')->default(false);
            $table->boolean('enfermeria_confirmada')->default(false);
            $table->text('observaciones_medicas')->nullable();
            
            // Comunicaciones
            $table->boolean('requiere_comunicaciones')->default(false);
            $table->text('observaciones_comunicaciones')->nullable();
            
            // Estado general
            $table->enum('estado', ['Programada', 'Realizada', 'Cancelada'])->default('Programada');
            $table->text('motivo_cancelacion')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('salidas_pedagogicas');
    }
};
