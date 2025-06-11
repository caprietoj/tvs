<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\EquipmentLoan;
use App\Models\Equipment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ProcessEquipmentAutoReturns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'equipment:process-returns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesa la devolución automática de equipos al finalizar los períodos de clase';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando proceso de devolución automática de equipos...');
        
        try {
            // Procesamos directamente las devoluciones en vez de hacer una llamada HTTP
            // que podría fallar por configuraciones de URL
            $processed = $this->processReturnsDirectly();
            
            $count = count($processed);
            $this->info("Proceso completado. Se procesaron {$count} devoluciones automáticas.");
            
            if ($count > 0) {
                $this->info("IDs de préstamos procesados: " . implode(', ', $processed));
            }
            
            Log::info('Devolución automática de equipos procesada correctamente', [
                'count' => $count,
                'date' => now()->format('Y-m-d H:i:s')
            ]);
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error al procesar las devoluciones automáticas: ' . $e->getMessage());
            Log::error('Error en comando equipment:process-returns: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Procesa las devoluciones directamente sin hacer llamadas HTTP
     * Implementa la misma lógica que el controlador
     */
    protected function processReturnsDirectly()
    {
        $this->info('Procesando devoluciones directamente...');
        
        $now = Carbon::now();
        $processed = [];
        
        // Obtener préstamos que deberían finalizar ahora
        $this->info('Fecha actual: ' . $now->format('Y-m-d'));
        $this->info('Hora actual: ' . $now->format('H:i'));
        
        // Verificar explícitamente si la columna inventory_returned existe
        // Verificar primero si la columna existe en la tabla
        $hasInventoryReturnedColumn = in_array('inventory_returned', Schema::getColumnListing('equipment_loans'));
        $this->info('La columna inventory_returned ' . ($hasInventoryReturnedColumn ? 'existe' : 'NO existe') . ' en la tabla');
        
        $query = EquipmentLoan::with('equipment')
            ->where('status', 'delivered')  // Este es un string, aseguramos que se escape correctamente
            ->where('auto_return', 1);      // Usamos 1 en lugar de true para evitar problemas
        
        // Asegurarnos que las fechas se formateen correctamente
        $formattedDate = $now->format('Y-m-d');
        $formattedTime = $now->format('H:i');
        $this->info("Filtrando por: fecha <= '{$formattedDate}' y hora <= '{$formattedTime}'");
        
        $query->where('loan_date', '<=', $formattedDate)
              ->where('end_time', '<=', $formattedTime);
              
        // Solo añadir la condición si la columna existe
        if ($hasInventoryReturnedColumn) {
            $query->where('inventory_returned', 0);  // Usamos 0 en lugar de false para evitar problemas
        }
            
        // Ejecutar la consulta
        $loansToReturn = $query->get();
            
        if ($loansToReturn->count() === 0) {
            $this->info('No hay préstamos para devolver automáticamente');
            return $processed;
        }
        
        DB::beginTransaction();
        
        foreach ($loansToReturn as $loan) {
            try {
                // Preparar los datos para actualizar
                $updateData = [
                    'status' => 'returned',
                    'return_signature' => 'Devolución automática por sistema',
                    'return_observations' => 'Devolución procesada automáticamente al finalizar el período de clase',
                    'return_date' => now()
                ];
                
                // Verificar si la columna inventory_returned existe en la tabla
                if (in_array('inventory_returned', Schema::getColumnListing('equipment_loans'))) {
                    $updateData['inventory_returned'] = true;
                }
                
                // Actualizar el estado del préstamo
                $loan->update($updateData);
                
                // Devolver unidades al inventario si fueron descontadas
                // Solo actualizar inventario si existe la columna inventory_discounted
                if (in_array('inventory_discounted', Schema::getColumnListing('equipment_loans')) && 
                    $loan->inventory_discounted) {
                    $equipment = $loan->equipment;
                    
                    // Verificar que no exceda el total de unidades
                    $newAvailableUnits = $equipment->available_units + $loan->units_requested;
                    if ($newAvailableUnits <= $equipment->total_units) {
                        $equipment->available_units = $newAvailableUnits;
                        $equipment->save();
                        
                        $this->info("Unidades devueltas automáticamente al inventario para préstamo #{$loan->id}");
                    }
                }
                
                $processed[] = $loan->id;
                
            } catch (\Exception $e) {
                $this->error('Error al procesar devolución automática para préstamo #' . $loan->id . ': ' . $e->getMessage());
                Log::error('Error al procesar devolución automática para préstamo #' . $loan->id, [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        DB::commit();
        return $processed;
    }
}
