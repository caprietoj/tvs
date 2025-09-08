<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EquipmentBlock;
use App\Models\Equipment;
use App\Models\SchoolCycle;

class EquipmentBlockSeeder extends Seeder
{
    public function run()
    {
        // Obtener el ciclo escolar activo
        $activeCycle = SchoolCycle::where('active', true)->first();
        
        if (!$activeCycle) {
            $this->command->warn('No hay ciclo escolar activo. Creando uno para la demo...');
            $activeCycle = SchoolCycle::create([
                'name' => 'Ciclo Escolar 2024-2025',
                'description' => 'Ciclo escolar demo para bloqueos',
                'start_date' => now()->startOfYear(),
                'cycle_length' => 10,
                'active' => true
            ]);
        }

        // Obtener algunos equipos para crear bloqueos de ejemplo
        $equipments = Equipment::take(3)->get();
        
        if ($equipments->isEmpty()) {
            $this->command->warn('No hay equipos disponibles. Creando algunos para la demo...');
            
            $equipments = collect([
                Equipment::create([
                    'type' => 'Tablet',
                    'section' => 'Primaria',
                    'total_units' => 30,
                    'available_units' => 30
                ]),
                Equipment::create([
                    'type' => 'Proyector',
                    'section' => 'Bachillerato',
                    'total_units' => 5,
                    'available_units' => 5
                ]),
                Equipment::create([
                    'type' => 'Laptop',
                    'section' => 'Secundaria',
                    'total_units' => 20,
                    'available_units' => 20
                ])
            ]);
        }

        $this->command->info('Creando bloqueos de ejemplo...');

        // Ejemplo 1: Bloqueo semanal - Tablets reservadas para mantenimiento los lunes
        $tablet = $equipments->where('type', 'Tablet')->first();
        if ($tablet) {
            EquipmentBlock::create([
                'equipment_id' => $tablet->id,
                'school_cycle_id' => $activeCycle->id,
                'cycle_day' => 0, // Bloqueo semanal
                'start_time' => '08:00',
                'end_time' => '10:00',
                'blocked_units' => 5,
                'reason' => 'Mantenimiento semanal de tablets',
                'is_weekday_block' => true,
                'monday' => true,
                'tuesday' => false,
                'wednesday' => false,
                'thursday' => false,
                'friday' => false,
                'saturday' => false,
                'sunday' => false,
            ]);
            
            $this->command->info("✓ Bloqueo semanal creado: {$tablet->type} - 5 unidades bloqueadas los lunes de 08:00 a 10:00");
        }

        // Ejemplo 2: Bloqueo por días de ciclo - Proyectores reservados para eventos especiales
        $proyector = $equipments->where('type', 'Proyector')->first();
        if ($proyector) {
            // Bloquear días de ciclo 5 y 10 (como ejemplos de días de eventos)
            foreach ([5, 10] as $cycleDay) {
                EquipmentBlock::create([
                    'equipment_id' => $proyector->id,
                    'school_cycle_id' => $activeCycle->id,
                    'cycle_day' => $cycleDay,
                    'start_time' => '14:00',
                    'end_time' => '17:00',
                    'blocked_units' => 2,
                    'reason' => 'Evento institucional - Día de ciclo ' . $cycleDay,
                    'is_weekday_block' => false,
                ]);
            }
            
            $this->command->info("✓ Bloqueo por días de ciclo creado: {$proyector->type} - 2 unidades bloqueadas en días 5 y 10 de 14:00 a 17:00");
        }

        // Ejemplo 3: Bloqueo semanal más extenso - Laptops para clases especiales
        $laptop = $equipments->where('type', 'Laptop')->first();
        if ($laptop) {
            EquipmentBlock::create([
                'equipment_id' => $laptop->id,
                'school_cycle_id' => $activeCycle->id,
                'cycle_day' => 0, // Bloqueo semanal
                'start_time' => '13:00',
                'end_time' => '15:00',
                'blocked_units' => 10,
                'reason' => 'Clases de programación - Secundaria',
                'is_weekday_block' => true,
                'monday' => false,
                'tuesday' => true,
                'wednesday' => true,
                'thursday' => true,
                'friday' => false,
                'saturday' => false,
                'sunday' => false,
            ]);
            
            $this->command->info("✓ Bloqueo semanal creado: {$laptop->type} - 10 unidades bloqueadas martes a jueves de 13:00 a 15:00");
        }

        $this->command->info('¡Bloqueos de equipos de ejemplo creados exitosamente!');
        $this->command->info('Ahora puedes:');
        $this->command->info('1. Visitar /equipment/blocks para ver los bloqueos');
        $this->command->info('2. Crear nuevos bloqueos desde la interfaz web');
        $this->command->info('3. Verificar que el sistema considere estos bloqueos al calcular disponibilidad');
    }
}
