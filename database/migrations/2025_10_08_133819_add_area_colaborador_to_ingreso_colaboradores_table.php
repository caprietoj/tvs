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
        Schema::table('ingreso_colaboradores', function (Blueprint $table) {
            $table->string('area_colaborador', 50)->nullable()->after('documento_colaborador');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ingreso_colaboradores', function (Blueprint $table) {
            $table->dropColumn('area_colaborador');
        });
    }
};
