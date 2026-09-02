<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\EquipmentBlock;
use App\Models\SchoolCycle;

class ImacScheduleBlocksSeeder extends Seeder
{
    public function run(): void
    {
        $activeCycle = SchoolCycle::where('active', true)->first();

        if (!$activeCycle) {
            $this->command->error('No hay un ciclo escolar activo.');
            return;
        }

        // SEGUNDO PISO
        $this->crearBloqueosSala(
            'sala_informatica',
            'Segundo Piso',
            $activeCycle,
            [
                1 => [
                    ['start' => '07:30', 'end' => '08:20', 'reason' => 'PROFESORA LEIDY LATORRE'],
                    ['start' => '08:20', 'end' => '09:10', 'reason' => 'PROFESORA ADRIANA FERNANDEZ'],
                ],
                2 => [
                    ['start' => '07:30', 'end' => '08:20', 'reason' => 'PROFESORA LEIDY LATORRE'],
                    ['start' => '08:00', 'end' => '08:45', 'reason' => 'PROFESOR JOSE LUIS'],
                    ['start' => '10:45', 'end' => '11:30', 'reason' => 'PROFESOR JOSE LUIS'],
                ],
                3 => [
                    ['start' => '07:30', 'end' => '08:20', 'reason' => 'PROFESORA LEIDY LATORRE'],
                    ['start' => '08:20', 'end' => '09:10', 'reason' => 'PROFESORA ADRIANA FERNANDEZ'],
                    ['start' => '08:45', 'end' => '09:30', 'reason' => 'PROFESOR JOSE LUIS'],
                    ['start' => '10:30', 'end' => '11:20', 'reason' => 'PROFESORA ADRIANA FERNANDEZ'],
                    ['start' => '10:45', 'end' => '11:30', 'reason' => 'PROFESOR JOSE LUIS'],
                ],
                4 => [
                    ['start' => '08:00', 'end' => '08:45', 'reason' => 'PROFESOR JOSE LUIS'],
                    ['start' => '10:45', 'end' => '11:30', 'reason' => 'PROFESOR JOSE LUIS'],
                ],
                5 => [
                    ['start' => '07:30', 'end' => '08:20', 'reason' => 'PROFESORA ADRIANA FERNANDEZ'],
                    ['start' => '08:45', 'end' => '09:30', 'reason' => 'PROFESOR JOSE LUIS'],
                    ['start' => '10:00', 'end' => '10:45', 'reason' => 'PROFESOR JOSE LUIS'],
                    ['start' => '10:45', 'end' => '11:30', 'reason' => 'PROFESOR JOSE LUIS'],
                ],
                6 => [
                    ['start' => '08:00', 'end' => '08:45', 'reason' => 'PROFESOR JOSE LUIS'],
                    ['start' => '08:20', 'end' => '09:10', 'reason' => 'PROFESORA LEIDY LATORRE'],
                    ['start' => '08:45', 'end' => '09:30', 'reason' => 'PROFESOR JOSE LUIS'],
                ],
            ]
        );

        // PRIMER PISO
        $this->crearBloqueosSala(
            'sala_informatica_primer_piso',
            'Primer Piso',
            $activeCycle,
            [
                1 => [
                    ['start' => '07:30', 'end' => '08:20', 'reason' => 'PROFESOR MARCELL'],
                    ['start' => '08:20', 'end' => '09:10', 'reason' => 'PROFESOR MARCELL'],
                    ['start' => '11:20', 'end' => '12:10', 'reason' => 'PROFESOR MARCELL'],
                    ['start' => '12:10', 'end' => '13:00', 'reason' => 'PROFESOR MARCELL'],
                    ['start' => '13:50', 'end' => '14:40', 'reason' => 'PROFESOR MARCELL'],
                ],
                2 => [
                    ['start' => '08:20', 'end' => '09:10', 'reason' => 'PROFESOR MARCELL'],
                    ['start' => '09:10', 'end' => '10:00', 'reason' => 'PROFESOR MARCELL'],
                    ['start' => '10:30', 'end' => '11:20', 'reason' => 'PROFESOR MARCELL'],
                    ['start' => '12:10', 'end' => '13:00', 'reason' => 'PROFESOR MARCELL'],
                    ['start' => '13:50', 'end' => '14:40', 'reason' => 'PROFESOR MARCELL'],
                ],
                3 => [
                    ['start' => '07:30', 'end' => '08:20', 'reason' => 'PROFESOR MARCELL'],
                    ['start' => '08:20', 'end' => '09:10', 'reason' => 'PROFESOR MARCELL'],
                    ['start' => '09:10', 'end' => '10:00', 'reason' => 'PROFESOR MARCELL'],
                    ['start' => '10:30', 'end' => '11:20', 'reason' => 'PROFESOR MARCELL'],
                    ['start' => '11:20', 'end' => '12:10', 'reason' => 'PROFESOR MARCELL'],
                    ['start' => '12:10', 'end' => '13:00', 'reason' => 'PROFESOR MARCELL'],
                    ['start' => '13:50', 'end' => '14:40', 'reason' => 'PROFESOR MARCELL'],
                ],
                4 => [
                    ['start' => '07:30', 'end' => '08:20', 'reason' => 'PROFESOR MARCELL'],
                    ['start' => '09:10', 'end' => '10:00', 'reason' => 'PROFESOR MARCELL'],
                    ['start' => '10:30', 'end' => '11:20', 'reason' => 'PROFESOR MARCELL'],
                    ['start' => '11:20', 'end' => '12:10', 'reason' => 'PROFESOR MARCELL'],
                    ['start' => '12:10', 'end' => '13:00', 'reason' => 'PROFESOR MARCELL'],
                    ['start' => '13:50', 'end' => '14:40', 'reason' => 'PROFESOR MARCELL'],
                ],
                5 => [
                    ['start' => '07:30', 'end' => '08:20', 'reason' => 'PROFESOR MARCELL'],
                    ['start' => '10:30', 'end' => '11:20', 'reason' => 'PROFESOR MARCELL'],
                    ['start' => '12:10', 'end' => '13:00', 'reason' => 'PROFESOR MARCELL'],
                ],
                6 => [
                    ['start' => '08:20', 'end' => '09:10', 'reason' => 'PROFESOR MARCELL'],
                    ['start' => '10:30', 'end' => '11:20', 'reason' => 'PROFESOR MARCELL'],
                    ['start' => '12:10', 'end' => '13:00', 'reason' => 'PROFESOR MARCELL'],
                    ['start' => '13:50', 'end' => '14:40', 'reason' => 'PROFESOR MARCELL'],
                ],
            ]
        );
    }

    protected function crearBloqueosSala(string $section, string $nombreSala, SchoolCycle $activeCycle, array $blockedSchedule): void
    {
        $equipment = Equipment::where('type', 'imac')
            ->where('section', $section)
            ->first();

        if (!$equipment) {
            $this->command->warn("No se encontró equipo IMAC para la sección '{$section}' ({$nombreSala}). Se omite.");
            return;
        }

        $this->command->info("\n=== Sala de Informática {$nombreSala} (Sección: {$section}) ===");
        $this->command->info("Equipo IMAC ID: {$equipment->id} | Unidades totales: {$equipment->total_units}");

        $deleted = EquipmentBlock::where('equipment_id', $equipment->id)->delete();
        $this->command->info("Bloqueos anteriores eliminados: {$deleted}");

        $totalBlocks = 0;

        foreach ($blockedSchedule as $cycleDay => $schedules) {
            foreach ($schedules as $schedule) {
                EquipmentBlock::create([
                    'equipment_id' => $equipment->id,
                    'school_cycle_id' => $activeCycle->id,
                    'cycle_day' => $cycleDay,
                    'start_time' => $schedule['start'],
                    'end_time' => $schedule['end'],
                    'blocked_units' => $equipment->total_units,
                    'reason' => $schedule['reason'],
                    'is_weekday_block' => false,
                ]);
                $totalBlocks++;
                $this->command->info("  ✓ Día {$cycleDay}: {$schedule['start']}-{$schedule['end']} | {$schedule['reason']}");
            }
        }

        $this->command->info("✅ Se crearon {$totalBlocks} bloqueos para {$nombreSala}.");
    }
}
