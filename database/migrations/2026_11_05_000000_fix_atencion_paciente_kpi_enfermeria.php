<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $seguimientoId = DB::table('thresholds')
            ->where('area', 'enfermeria')
            ->where('kpi_name', 'Seguimiento de la atención')
            ->value('id');

        if (!$seguimientoId) {
            $seguimientoId = DB::table('thresholds')->insertGetId([
                'area' => 'enfermeria',
                'kpi_name' => 'Seguimiento de la atención',
                'value' => 80,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('thresholds')
            ->where('area', 'enfermeria')
            ->where('kpi_name', 'Atención al paciente')
            ->update(['value' => 100, 'updated_at' => now()]);

        DB::table('kpis')
            ->where('area', 'enfermeria')
            ->where('name', 'Atención al paciente')
            ->update([
                'name' => 'Seguimiento de la atención',
                'threshold_id' => $seguimientoId,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        $atencionId = DB::table('thresholds')
            ->where('area', 'enfermeria')
            ->where('kpi_name', 'Atención al paciente')
            ->value('id');

        $seguimientoId = DB::table('thresholds')
            ->where('area', 'enfermeria')
            ->where('kpi_name', 'Seguimiento de la atención')
            ->value('id');

        if ($atencionId && $seguimientoId) {
            DB::table('kpis')
                ->where('area', 'enfermeria')
                ->where('threshold_id', $seguimientoId)
                ->update([
                    'name' => 'Atención al paciente',
                    'threshold_id' => $atencionId,
                    'updated_at' => now(),
                ]);
        }

        DB::table('thresholds')
            ->where('area', 'enfermeria')
            ->where('kpi_name', 'Seguimiento de la atención')
            ->delete();
    }
};
