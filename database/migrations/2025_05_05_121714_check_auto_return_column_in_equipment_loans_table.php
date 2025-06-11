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
        // Comprueba si la columna ya existe para evitar errores
        if (!Schema::hasColumn('equipment_loans', 'auto_return')) {
            Schema::table('equipment_loans', function (Blueprint $table) {
                $table->boolean('auto_return')->default(true)->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Solo eliminar la columna si existe
        if (Schema::hasColumn('equipment_loans', 'auto_return')) {
            Schema::table('equipment_loans', function (Blueprint $table) {
                $table->dropColumn('auto_return');
            });
        }
    }
};
