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
        // Lista de palabras que indican que el apellido es realmente un cargo/grado
        $cargosComunes = [
            'Administracion',
            'Docente',
            'Coordinacion',
            'Asistente',
            'Servicios',
            'Biblioteca',
            'Enfermeria',
            'Mantenimiento',
            'Sistemas',
            'Contabilidad',
            'Rectoría',
            'Rectoria',
            'Secretaria',
            'Pastoral',
        ];

        foreach ($cargosComunes as $cargo) {
            // Actualizar tabla personas
            DB::table('personas')
                ->where('apellido', $cargo)
                ->update([
                    'grado' => $cargo,
                    'apellido' => '',
                    'tipo_persona' => 'empleado', // Corregir tipo
                    'updated_at' => now(),
                ]);

            // Actualizar también registros de portería
            DB::table('registro_porteria')
                ->where('apellido', $cargo)
                ->update([
                    'apellido' => '',
                    'tipo_persona' => 'empleado', // Corregir tipo
                    'updated_at' => now(),
                ]);
        }

        // Registrar en log
        \Log::info('Migración de limpieza de datos de personas ejecutada', [
            'cargos_corregidos' => $cargosComunes,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No se puede revertir automáticamente ya que no guardamos los datos originales
        \Log::warning('No se puede revertir la migración de limpieza de datos automáticamente');
    }
};
