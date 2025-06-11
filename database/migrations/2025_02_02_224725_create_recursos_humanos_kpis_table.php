<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecursosHumanosKpisTable extends Migration
{
    public function up()
    {
        Schema::create('recursos_humanos_kpis', function (Blueprint $table) {
            $table->id();
            // Clave foránea a la tabla de thresholds de RRHH
            $table->foreignId('threshold_id')->constrained('recursos_humanos_thresholds')->onDelete('cascade');
            $table->string('name');                // Nombre del KPI (derivado del threshold)
            $table->string('methodology');         // Metodología de medición (input text)
            $table->string('frequency');           // Frecuencia: Diario, Quincenal, Mensual
            $table->date('measurement_date');      // Fecha de medición
            $table->decimal('percentage', 5, 2);    // Porcentaje alcanzado
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('recursos_humanos_kpis');
    }
}
