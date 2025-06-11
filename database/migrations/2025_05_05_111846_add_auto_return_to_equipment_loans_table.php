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
        Schema::table('equipment_loans', function (Blueprint $table) {
            // Solo añade auto_return si no existe
            if (!Schema::hasColumn('equipment_loans', 'auto_return')) {
                $table->boolean('auto_return')->default(true)->after('status');
            }
            
            // Añade period_id si no existe
            if (!Schema::hasColumn('equipment_loans', 'period_id')) {
                $table->string('period_id')->nullable()->after('auto_return');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_loans', function (Blueprint $table) {
            // Solo elimina las columnas si existen
            if (Schema::hasColumn('equipment_loans', 'period_id')) {
                $table->dropColumn('period_id');
            }
            
            if (Schema::hasColumn('equipment_loans', 'auto_return')) {
                $table->dropColumn('auto_return');
            }
        });
    }
};
