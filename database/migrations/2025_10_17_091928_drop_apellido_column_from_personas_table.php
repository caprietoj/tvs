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
        // Verificar si la columna existe antes de eliminarla
        if (Schema::hasColumn('personas', 'apellido')) {
            Schema::table('personas', function (Blueprint $table) {
                $table->dropColumn('apellido');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Solo restaurar si la columna no existe
        if (!Schema::hasColumn('personas', 'apellido')) {
            Schema::table('personas', function (Blueprint $table) {
                $table->string('apellido', 100)->nullable()->after('nombre');
            });
        }
    }
};
