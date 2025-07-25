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
        Schema::table('performance_evaluations', function (Blueprint $table) {
            $table->json('objectives_section_supervisor')->nullable()->after('objectives_supervisor_score');
            $table->json('organizational_competencies_supervisor')->nullable()->after('competencies_supervisor_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_evaluations', function (Blueprint $table) {
            $table->dropColumn('objectives_section_supervisor');
            $table->dropColumn('organizational_competencies_supervisor');
        });
    }
};
