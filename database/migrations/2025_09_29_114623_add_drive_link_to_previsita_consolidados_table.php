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
        if (Schema::hasTable('previsita_consolidados')) {
            Schema::table('previsita_consolidados', function (Blueprint $table) {
                if (!Schema::hasColumn('previsita_consolidados', 'drive_link')) {
                    $table->string('drive_link')->nullable()->after('novedades_visita_archivo');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('previsita_consolidados')) {
            Schema::table('previsita_consolidados', function (Blueprint $table) {
                if (Schema::hasColumn('previsita_consolidados', 'drive_link')) {
                    $table->dropColumn('drive_link');
                }
            });
        }
    }
};
