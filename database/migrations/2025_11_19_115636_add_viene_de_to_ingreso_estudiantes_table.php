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
            $table->string('viene_de')->nullable()->after('curso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ingreso_estudiantes', function (Blueprint $table) {
            $table->dropColumn('viene_de');
        });
    }
};
