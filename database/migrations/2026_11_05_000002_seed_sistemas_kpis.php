<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $helpDeskId = DB::table('sistemas_thresholds')
            ->where('kpi_name', 'HELP DESK')
            ->value('id');

        if (!$helpDeskId) {
            $helpDeskId = DB::table('sistemas_thresholds')->insertGetId([
                'kpi_name' => 'HELP DESK',
                'value' => 80,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $eventsId = DB::table('sistemas_thresholds')
            ->where('kpi_name', 'EVENTOS INSTITUCIONALES')
            ->value('id');

        if (!$eventsId) {
            $eventsId = DB::table('sistemas_thresholds')->insertGetId([
                'kpi_name' => 'EVENTOS INSTITUCIONALES',
                'value' => 90,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $equipmentId = DB::table('sistemas_thresholds')
            ->where('kpi_name', 'PRÉSTAMO DE EQUIPOS')
            ->value('id');

        if (!$equipmentId) {
            $equipmentId = DB::table('sistemas_thresholds')->insertGetId([
                'kpi_name' => 'PRÉSTAMO DE EQUIPOS',
                'value' => 90,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $helpDeskMethodology = 'Solicitudes de soporte recibidas por WhatsApp y atendidas (respuesta del equipo de Sistemas o cierre confirmado por el usuario) dentro de las 24 horas siguientes. Fuente: chat de soporte de Sistemas. Fórmula: (solicitudes atendidas / solicitudes recibidas) × 100. Agosto: 25/37; septiembre: 32/77; octubre: 24/55.';

        $eventsMethodology = 'Eventos institucionales que requieren apoyo de Sistemas y cuyo requerimiento fue confirmado. Fuente: módulo de Eventos (systems_required / systems_confirmed). Fórmula: (eventos con soporte confirmado / eventos que requieren Sistemas) × 100. Agosto: 25/27; septiembre: 19/20; octubre: 19/21.';

        $equipmentMethodology = 'Solicitudes de préstamo de equipos registradas y atendidas en el módulo de Préstamo de Equipos. Fórmula: (préstamos atendidos / solicitudes de préstamo recibidas) × 100. Agosto: 80 préstamos (925 unidades); septiembre: 249 (3186); octubre: 123 (1704).';

        $records = [
            [$helpDeskId, 'HELP DESK', $helpDeskMethodology, '2026-08-31', 67.57],
            [$helpDeskId, 'HELP DESK', $helpDeskMethodology, '2026-09-30', 41.56],
            [$helpDeskId, 'HELP DESK', $helpDeskMethodology, '2026-10-31', 43.64],
            [$eventsId, 'EVENTOS INSTITUCIONALES', $eventsMethodology, '2026-08-31', 92.59],
            [$eventsId, 'EVENTOS INSTITUCIONALES', $eventsMethodology, '2026-09-30', 95.00],
            [$eventsId, 'EVENTOS INSTITUCIONALES', $eventsMethodology, '2026-10-31', 90.48],
            [$equipmentId, 'PRÉSTAMO DE EQUIPOS', $equipmentMethodology, '2026-08-31', 100.00],
            [$equipmentId, 'PRÉSTAMO DE EQUIPOS', $equipmentMethodology, '2026-09-30', 100.00],
            [$equipmentId, 'PRÉSTAMO DE EQUIPOS', $equipmentMethodology, '2026-10-31', 100.00],
        ];

        foreach ($records as [$thresholdId, $name, $methodology, $date, $percentage]) {
            $exists = DB::table('sistemas_kpis')
                ->where('threshold_id', $thresholdId)
                ->where('measurement_date', $date)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('sistemas_kpis')->insert([
                'threshold_id' => $thresholdId,
                'name' => $name,
                'type' => 'measurement',
                'methodology' => $methodology,
                'frequency' => 'Mensual',
                'measurement_date' => $date,
                'percentage' => $percentage,
                'area' => 'sistemas',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('sistemas_kpis')
            ->whereIn('measurement_date', ['2026-08-31', '2026-09-30', '2026-10-31'])
            ->whereIn('name', ['HELP DESK', 'EVENTOS INSTITUCIONALES', 'PRÉSTAMO DE EQUIPOS'])
            ->delete();

        DB::table('sistemas_thresholds')
            ->whereIn('kpi_name', ['EVENTOS INSTITUCIONALES', 'PRÉSTAMO DE EQUIPOS'])
            ->delete();
    }
};
