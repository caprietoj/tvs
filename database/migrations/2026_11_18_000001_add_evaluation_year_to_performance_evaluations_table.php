<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('performance_evaluations', function (Blueprint $table) {
            $table->unsignedSmallInteger('evaluation_year')->nullable()->after('evaluation_type');
            $table->index('evaluation_year');
        });

        // Asignar el año de elaboración a las evaluaciones existentes
        // tomando como base el año de inicio del período evaluado.
        DB::table('performance_evaluations')
            ->select('id', 'evaluation_period_start', 'created_at')
            ->orderBy('id')
            ->chunkById(500, function ($evaluations) {
                foreach ($evaluations as $evaluation) {
                    $year = null;

                    if ($evaluation->evaluation_period_start) {
                        $year = (int) substr($evaluation->evaluation_period_start, 0, 4);
                    } elseif ($evaluation->created_at) {
                        $year = (int) substr($evaluation->created_at, 0, 4);
                    }

                    if ($year) {
                        DB::table('performance_evaluations')
                            ->where('id', $evaluation->id)
                            ->update(['evaluation_year' => $year]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_evaluations', function (Blueprint $table) {
            $table->dropIndex(['evaluation_year']);
            $table->dropColumn('evaluation_year');
        });
    }
};
