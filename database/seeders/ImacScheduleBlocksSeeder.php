<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\EquipmentBlock;
use App\Models\SchoolCycle;

class ImacScheduleBlocksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Crea bloqueos para los IMAC de la sala de informática según el horario de clases
     */
    public function run(): void
    {
        // Obtener el equipo IMAC
        $imac = Equipment::where('type', 'imac')
            ->where('section', 'sala_informatica')
            ->first();

        if (!$imac) {
            $this->command->error('No se encontró el equipo IMAC. Asegúrate de que exista en la base de datos.');
            return;
        }

        // Obtener el ciclo escolar activo
        $activeCycle = SchoolCycle::where('active', true)->first();

        if (!$activeCycle) {
            $this->command->error('No hay un ciclo escolar activo.');
            return;
        }

        $this->command->info("Creando bloqueos para IMAC (ID: {$imac->id}) en ciclo {$activeCycle->id}");

        // Eliminar bloqueos anteriores para este equipo
        EquipmentBlock::where('equipment_id', $imac->id)->delete();
        $this->command->info('Bloqueos anteriores eliminados.');

        // Definir los horarios ocupados según la imagen
        // ROJO y BLANCO = Bloqueados | AZUL = Disponible
        // Formato: [día_ciclo => [horarios]]
        $blockedSchedule = [
            // DÍA 1
            1 => [
                ['start' => '07:30', 'end' => '08:20', 'reason' => '7B - Clase programada'],
                ['start' => '08:20', 'end' => '09:10', 'reason' => '8B - Clase programada'],
                ['start' => '09:10', 'end' => '10:00', 'reason' => 'Horario bloqueado'],
                ['start' => '11:20', 'end' => '12:10', 'reason' => '8A - Clase programada'],
            ],
            // DÍA 2
            2 => [
                ['start' => '07:30', 'end' => '08:20', 'reason' => '9B - Clase programada'],
                ['start' => '10:30', 'end' => '11:20', 'reason' => 'Horario bloqueado'],
                ['start' => '12:10', 'end' => '13:00', 'reason' => '9A - Clase programada'],
                ['start' => '13:50', 'end' => '14:40', 'reason' => '7A - Clase programada'],
            ],
            // DÍA 3
            3 => [
                ['start' => '08:20', 'end' => '09:10', 'reason' => '8B - Clase programada'],
                ['start' => '09:10', 'end' => '10:00', 'reason' => '8A - Clase programada'],
                ['start' => '10:30', 'end' => '11:20', 'reason' => '6A - Clase programada'],
                ['start' => '12:10', 'end' => '13:00', 'reason' => '9A - Clase programada'],
            ],
            // DÍA 4
            4 => [
                ['start' => '08:20', 'end' => '09:10', 'reason' => '8A - Clase programada'],
                ['start' => '09:10', 'end' => '10:00', 'reason' => '7A - Clase programada'],
                ['start' => '10:30', 'end' => '11:20', 'reason' => 'Horario bloqueado'],
                ['start' => '11:20', 'end' => '12:10', 'reason' => 'Horario bloqueado'],
                ['start' => '13:50', 'end' => '14:40', 'reason' => '5A - Clase programada'],
            ],
            // DÍA 5
            5 => [
                ['start' => '09:10', 'end' => '10:00', 'reason' => '9B - Clase programada'],
                ['start' => '12:10', 'end' => '13:00', 'reason' => '5A - Clase programada'],
                ['start' => '13:50', 'end' => '14:40', 'reason' => 'Horario bloqueado'],
            ],
            // DÍA 6
            6 => [
                ['start' => '07:30', 'end' => '08:20', 'reason' => 'Horario bloqueado'],
                ['start' => '08:20', 'end' => '09:10', 'reason' => '7B - Clase programada'],
                ['start' => '09:10', 'end' => '10:00', 'reason' => 'Horario bloqueado'],
                ['start' => '11:20', 'end' => '12:10', 'reason' => '6A - Clase programada'],
                ['start' => '12:10', 'end' => '13:00', 'reason' => '8B - Clase programada'],
            ],
        ];

        $totalBlocks = 0;

        foreach ($blockedSchedule as $cycleDay => $schedules) {
            foreach ($schedules as $schedule) {
                EquipmentBlock::create([
                    'equipment_id' => $imac->id,
                    'school_cycle_id' => $activeCycle->id,
                    'cycle_day' => $cycleDay,
                    'start_time' => $schedule['start'],
                    'end_time' => $schedule['end'],
                    'blocked_units' => 22, // Bloquear todas las unidades
                    'reason' => $schedule['reason'],
                    'is_weekday_block' => false,
                ]);
                $totalBlocks++;
                $this->command->info("✓ Día {$cycleDay}: {$schedule['start']}-{$schedule['end']} - {$schedule['reason']}");
            }
        }

        $this->command->info("\n✅ Se crearon {$totalBlocks} bloqueos para los IMAC de la sala de informática.");
    }
}
