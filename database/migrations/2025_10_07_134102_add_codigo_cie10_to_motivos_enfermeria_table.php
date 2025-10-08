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
        Schema::table('motivos_enfermeria', function (Blueprint $table) {
            $table->string('codigo_cie10', 10)->nullable()->after('nombre')->comment('Código CIE-10 del diagnóstico');
            $table->string('categoria', 100)->nullable()->after('codigo_cie10')->comment('Categoría del motivo (ej: RESFRIADO COMÚN, LESIONES, etc.)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('motivos_enfermeria', function (Blueprint $table) {
            $table->dropColumn(['codigo_cie10', 'categoria']);
        });
    }
};
