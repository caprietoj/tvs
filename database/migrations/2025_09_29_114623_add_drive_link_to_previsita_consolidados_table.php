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
        Schema::table('previsita_consolidados', function (Blueprint $table) {
            $table->string('drive_link')->nullable()->after('novedades_visita_archivo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('previsita_consolidados', function (Blueprint $table) {
            $table->dropColumn('drive_link');
        });
    }
};
