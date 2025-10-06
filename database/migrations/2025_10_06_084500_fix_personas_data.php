<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Los datos están mal: apellido tiene el grado, y grado está vacío
        // Necesitamos reorganizar
        
        DB::statement('UPDATE personas SET grado = apellido, apellido = "" WHERE grado IS NULL OR grado = ""');
        
        // Ahora identificar el tipo basándose en el grado
        $estatusEstudiantes = [
            'Prekinder PKA', 'Kinder KA', 'Transición A', 'Transición B',
            '1A', '1B', '2A', '2B', '3A', '3B', '4A', '5A', '5B',
            '6A', '6B', '7A', '7B', '8A', '8B', '9A', '9B', '10A', '10B', '11A', '11B',
            'Primero 1A', 'Primero 1B',
            'Segundo 2A', 'Segundo 2B',
            'Tercero 3A', 'Tercero 3B',
            'Cuarto 4A',
            'Quinto 5A', 'Quinto 5B',
            'Sexto 6A', 'Sexto 6B',
            'Séptimo 7A', 'Séptimo 7B',
            'Octavo 8A', 'Octavo 8B',
            'Noveno 9A', 'Noveno 9B',
            'Décimo 10A', 'Décimo 10B',
            'Undécimo 11A', 'Undécimo 11B'
        ];
        
        $estatusEmpleados = [
            'Docentes Bachillerato',
            'Administracion',
            'Docentes Preescolar y Primaria',
            'EMC',
            'PRACTICANTE'
        ];
        
        // Actualizar estudiantes
        foreach ($estatusEstudiantes as $grado) {
            DB::table('personas')
                ->where('grado', 'LIKE', "%{$grado}%")
                ->update(['tipo_persona' => 'estudiante']);
        }
        
        // Actualizar empleados
        foreach ($estatusEmpleados as $cargo) {
            DB::table('personas')
                ->where('grado', 'LIKE', "%{$cargo}%")
                ->update(['tipo_persona' => 'empleado']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No revertir, los datos originales estaban incorrectos
    }
};
