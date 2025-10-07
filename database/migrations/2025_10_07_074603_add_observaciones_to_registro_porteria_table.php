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
            $table->text('observaciones')->nullable()->after('hora_salida');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registro_porteria', function (Blueprint $table) {
            $table->dropColumn('observaciones');
        });
    }
};
