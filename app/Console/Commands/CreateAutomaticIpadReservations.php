<?php

namespace App\Console\Commands;

use App\Models\CycleDay;
use App\Models\Equipment;
use App\Models\EquipmentLoan;
use App\Models\SchoolCycle;
use App\Models\User;
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
    protected $description = 'Crea reservas automáticas de 10 iPads para cada curso 4a y 4b (20 total) en todos los días 5 del ciclo escolar de 08:00-09:30';

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
        
        // Buscar un usuario administrador para crear las reservas
        $adminUser = User::whereHas('roles', function($query) {
            $query->where('name', 'admin');
        })->first();
        
        if (!$adminUser) {
            $this->error('No se encontró un usuario administrador para crear las reservas.');
            return 1;
        }
        
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
        
        $created = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($day5Dates as $cycleDay) {
            $date = $cycleDay->date->format('Y-m-d');
            
            // Crear reserva para curso 4a
            $result4a = $this->createReservation(
                $ipadEquipment,
                $adminUser,
                $date,
                '4a',
                '08:00',
                '09:30'
            );
            
            if ($result4a === 'created') {
                $created++;
            } elseif ($result4a === 'skipped') {
                $skipped++;
            } else {
                $errors++;
            }
            
            // Crear reserva para curso 4b
            $result4b = $this->createReservation(
                $ipadEquipment,
                $adminUser,
                $date,
                '4b',
                '08:00',
                '09:30'
            );
            
            if ($result4b === 'created') {
                $created++;
            } elseif ($result4b === 'skipped') {
                $skipped++;
            } else {
                $errors++;
            }
        }
        
        $this->info("\n=== Resumen ===");
        $this->info("Reservas creadas: {$created}");
        $this->warn("Reservas omitidas (ya existían): {$skipped}");
        if ($errors > 0) {
            $this->error("Errores: {$errors}");
        }
        
        Log::info('Reservas automáticas de iPads creadas', [
            'school_cycle' => $schoolCycle->name,
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors
        ]);
        
        return 0;
    }
    
    /**
     * Crea una reserva de equipo si no existe
     */
    private function createReservation($equipment, $user, $date, $grade, $startTime, $endTime)
    {
        // Verificar si ya existe una reserva para este curso en esta fecha
        $existingLoan = EquipmentLoan::where('equipment_id', $equipment->id)
            ->where('loan_date', $date)
            ->where('grade', $grade)
            ->where('section', 'preescolar_primaria')
            ->first();
        
        if ($existingLoan) {
            $this->line("  - Omitida: Ya existe reserva para {$grade} el {$date}");
            return 'skipped';
        }
        
        try {
            // Crear la reserva
            $loan = EquipmentLoan::create([
                'user_id' => $user->id,
                'equipment_id' => $equipment->id,
                'section' => 'preescolar_primaria',
                'subsection' => 'primaria',
                'grade' => $grade,
                'teacher_name' => 'Reserva Automática - Cursos 4°',
                'loan_date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'units_requested' => 10,
                'status' => 'pending',
                'auto_return' => true,
                'period_id' => null
            ]);
            
            $this->line("  ✓ Creada: Reserva de 10 iPads para {$grade} el {$date}");
            return 'created';
        } catch (\Exception $e) {
            $this->error("  ✗ Error al crear reserva para {$grade} el {$date}: " . $e->getMessage());
            Log::error('Error al crear reserva automática de iPad', [
                'date' => $date,
                'grade' => $grade,
                'error' => $e->getMessage()
            ]);
            return 'error';
        }
    }
}
