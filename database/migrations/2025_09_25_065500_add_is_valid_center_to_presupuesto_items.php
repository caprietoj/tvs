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
        Schema::table('presupuesto_items', function (Blueprint $table) {
            $table->boolean('is_valid_center')->default(true)->after('centro_costo')
                  ->comment('Indica si el centro de costo es válido según archivos oficiales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presupuesto_items', function (Blueprint $table) {
            $table->dropColumn('is_valid_center');
        });
    }
};