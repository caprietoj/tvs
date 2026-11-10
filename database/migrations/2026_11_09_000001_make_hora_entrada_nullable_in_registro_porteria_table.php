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
        Schema::table('registro_porteria', function (Blueprint $table) {
            // Permite registrar una salida sin que exista una entrada previa
            // (por ejemplo, empleados que solo marcan su salida del día).
            $table->time('hora_entrada')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registro_porteria', function (Blueprint $table) {
            $table->time('hora_entrada')->nullable(false)->change();
        });
    }
};
