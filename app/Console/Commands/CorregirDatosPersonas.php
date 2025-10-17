<?php

namespace App\Console\Commands;

use App\Models\Persona;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CorregirDatosPersonas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'personas:corregir-datos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige el tipo_persona según el grado de cada persona';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Iniciando corrección de tipo_persona...');
        
        DB::beginTransaction();
        
        try {
            // Obtener todas las personas
            $personas = Persona::all();
            $corregidos = 0;
            
            foreach ($personas as $persona) {
                // Si tiene grado asignado, verificar que el tipo_persona sea correcto
                if (!empty($persona->grado)) {
                    $tipoReal = $this->determinarTipoPersona($persona->grado);
                    
                    if ($persona->tipo_persona !== $tipoReal) {
                        $persona->tipo_persona = $tipoReal;
                        $persona->save();
                        $corregidos++;
                        $this->line("✓ Corregido: {$persona->nombre} - {$persona->grado} → {$tipoReal}");
                    }
                }
            }
            
            DB::commit();
            
            if ($corregidos > 0) {
                $this->info("✅ Corrección completada: {$corregidos} registros actualizados de {$personas->count()} totales.");
            } else {
                $this->info("✅ No se encontraron registros para corregir. Todos están correctos.");
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error al corregir datos: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
    
    /**
     * Determinar tipo de persona según el cargo
     */
    private function determinarTipoPersona($cargo)
    {
        // Lista de tipos de empleados
        $tiposEmpleado = [
            'Administracion',
            'Docentes Bachillerato',
            'Docentes Preescolar y Primaria',
            'EMC',
            'Depto de Apoyo',
            'Mantenimiento',
            'Servicios Generales',
            'PRACTICANTE',
            'Coordinacion',
            'Rectoria',
            'Secretaria',
            'Biblioteca',
            'Enfermeria',
            'Sistemas',
            'Contabilidad',
            'Pastoral'
        ];

        // Verificar si es empleado
        foreach ($tiposEmpleado as $tipo) {
            if (stripos($cargo, $tipo) !== false) {
                return 'empleado';
            }
        }

        // Si no es empleado, es estudiante
        return 'estudiante';
    }
}
