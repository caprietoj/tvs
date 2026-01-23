<?php

namespace App\Console\Commands;

use App\Models\CycleDay;
use App\Models\Equipment;
use App\Models\EquipmentBlock;
use App\Models\SchoolCycle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CreateAutomaticIpadReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'equipment:create-automatic-ipad-reservations {--school-cycle-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea bloqueos automáticos de 20 iPads para cursos 4a y 4b en todos los días 5 del ciclo escolar de 08:00-09:30';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Obtener el ciclo escolar activo o el especificado
        $schoolCycleId = $this->option('school-cycle-id');
        
        if ($schoolCycleId) {
            $schoolCycle = SchoolCycle::find($schoolCycleId);
        } else {
            $schoolCycle = SchoolCycle::where('active', true)->first();
        }
        
        if (!$schoolCycle) {
            $this->error('No se encontró un ciclo escolar activo o el ID especificado no existe.');
            return 1;
        }
        
        $this->info("Procesando ciclo escolar: {$schoolCycle->name}");
        
        // Buscar el equipo de iPads para preescolar y primaria
        $ipadEquipment = Equipment::where('type', 'ipad')
            ->where('section', 'preescolar_primaria')
            ->first();
        
        if (!$ipadEquipment) {
            $this->error('No se encontró equipo de iPads para preescolar y primaria en el sistema.');
            return 1;
        }
        
        $this->info("Equipo encontrado: {$ipadEquipment->total_units} iPads disponibles");
        
        // Obtener todos los días 5 del ciclo escolar
        $day5Dates = CycleDay::where('school_cycle_id', $schoolCycle->id)
            ->where('cycle_day', 5)
            ->orderBy('date')
            ->get();
        
        if ($day5Dates->isEmpty()) {
            $this->warn('No se encontraron días 5 en el ciclo escolar.');
            return 0;
        }
        
        $this->info("Se encontraron {$day5Dates->count()} días de ciclo 5");
        
        // Crear un solo bloqueo para todos los días 5 del ciclo escolar
        $result = $this->createEquipmentBlock(
            $ipadEquipment,
            $schoolCycle,
            '08:00',
            '09:30',
            20  // 20 iPads bloqueados (10 para 4a + 10 para 4b)
        );
        
        if ($result === 'created') {
            $this->info("\n✓ Bloqueo creado exitosamente");
            $this->info("  - 20 iPads bloqueados en todos los días 5 del ciclo");
            $this->info("  - Horario: 08:00 - 09:30");
            $this->info("  - Razón: Cursos 4a y 4b (10 iPads por curso)");
        } elseif ($result === 'skipped') {
            $this->warn("\n- Bloqueo omitido: Ya existe un bloqueo para los días 5");
        } else {
            $this->error("\n✗ Error al crear el bloqueo");
        }
        
        Log::info('Bloqueo automático de iPads para días 5', [
            'school_cycle' => $schoolCycle->name,
            'result' => $result
        ]);
        
        return 0;
    }
    
    /**
     * Crea un bloqueo de equipo para todos los días 5 del ciclo escolar
     */
    private function createEquipmentBlock($equipment, $schoolCycle, $startTime, $endTime, $blockedUnits)
    {
        // Verificar si ya existe un bloqueo para los días 5
        $existingBlock = EquipmentBlock::where('equipment_id', $equipment->id)
            ->where('school_cycle_id', $schoolCycle->id)
            ->where('cycle_day', 5)
            ->where('start_time', $startTime)
            ->where('end_time', $endTime)
            ->first();
        
        if ($existingBlock) {
            return 'skipped';
        }
        
        try {
            // Crear el bloqueo para el día 5 del ciclo
            $block = EquipmentBlock::create([
                'equipment_id' => $equipment->id,
                'school_cycle_id' => $schoolCycle->id,
                'cycle_day' => 5,  // Día 5 del ciclo escolar
                'start_time' => $startTime,
                'end_time' => $endTime,
                'blocked_units' => $blockedUnits,
                'reason' => 'Cursos 4a y 4b (10 iPads por curso)',
                'is_weekday_block' => false,  // Es un bloqueo por día de ciclo, no por día de semana
                'monday' => false,
                'tuesday' => false,
                'wednesday' => false,
                'thursday' => false,
                'friday' => false,
                'saturday' => false,
                'sunday' => false
            ]);
            
            return 'created';
        } catch (\Exception $e) {
            Log::error('Error al crear bloqueo automático de iPads', [
                'school_cycle' => $schoolCycle->name,
                'error' => $e->getMessage()
            ]);
            return 'error';
        }
    }
}