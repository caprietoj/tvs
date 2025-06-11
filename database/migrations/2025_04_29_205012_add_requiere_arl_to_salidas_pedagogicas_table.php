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
        Schema::table('salidas_pedagogicas', function (Blueprint $table) {
            if (!Schema::hasColumn('salidas_pedagogicas', 'requiere_arl')) {
                $table->boolean('requiere_arl')->default(false)->after('observaciones');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salidas_pedagogicas', function (Blueprint $table) {
            if (Schema::hasColumn('salidas_pedagogicas', 'requiere_arl')) {
                $table->dropColumn('requiere_arl');
            }
        });
    }
};