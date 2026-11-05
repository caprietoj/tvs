<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $atencionId = DB::table('thresholds')
            ->where('area', 'enfermeria')
            ->where('kpi_name', 'Atención al paciente')
            ->value('id');

        $seguimientoId = DB::table('thresholds')
            ->where('area', 'enfermeria')
            ->where('kpi_name', 'Seguimiento de la atención')
            ->value('id');

        if (!$atencionId || !$seguimientoId) {
            return;
        }

        $registros = DB::table('kpis')
            ->where('area', 'enfermeria')
            ->where('threshold_id', $seguimientoId)
            ->orderBy('measurement_date')
            ->get();

        foreach ($registros as $registro) {
            $existe = DB::table('kpis')
                ->where('area', 'enfermeria')
                ->where('name', 'Atención al paciente')
                ->where('measurement_date', $registro->measurement_date)
                ->exists();

            if ($existe) {
                continue;
            }

            $total = null;
            if (preg_match('/basado en (\d+) registros/i', (string) $registro->analysis, $matches)) {
                $total = (int) $matches[1];
            }

            DB::table('kpis')->insert([
                'threshold_id' => $atencionId,
                'area' => 'enfermeria',
                'name' => 'Atención al paciente',
                'methodology' => 'archivo',
                'frequency' => $registro->frequency,
                'measurement_date' => $registro->measurement_date,
                'percentage' => 100.00,
                'analysis' => $total
                    ? "Análisis de cobertura: {$total} de {$total} personas que acudieron fueron atendidas (100%)."
                    : 'Análisis de cobertura: cobertura de la demanda del servicio de enfermería (100%).',
                'type' => $registro->type,
                'url' => $registro->url,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('kpis')
            ->where('area', 'enfermeria')
            ->where('name', 'Atención al paciente')
            ->where('analysis', 'like', 'Análisis de cobertura:%')
            ->delete();
    }
};
