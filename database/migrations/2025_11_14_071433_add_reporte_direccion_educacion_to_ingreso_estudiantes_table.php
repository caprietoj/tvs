<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ingreso_estudiantes', function (Blueprint $table) {
            $table->string('reporte_direccion_educacion')->nullable()->after('encuesta_observaciones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ingreso_estudiantes', function (Blueprint $table) {
            $table->dropColumn('reporte_direccion_educacion');
        });
    }
};
