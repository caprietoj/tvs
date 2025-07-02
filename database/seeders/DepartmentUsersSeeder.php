<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class DepartmentUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuarios de Mantenimiento
        $maintenanceUsers = [
            'cjmonterroza@tvs.edu.co' => 'CRISTHIAN JOSE MONTERROZA',
            'tcabezas@tvs.edu.co' => 'TEOFILO CABEZAS TORRES',
            'lamaya@tvs.edu.co' => 'LUIS ALBERTO AMAYA RAMIREZ'
        ];

        // Usuarios de Servicios Generales
        $servicesUsers = [
            'amayala@tvs.edu.co' => 'Ana Milena Ayala',
            'agomez@tvs.edu.co' => 'VIVIANA ANDREA GOMEZ OSORIO',
            'supervsergenerales@tvs.edu.co' => 'Nancy Vichue Oyola',
            'xsanchezy@tvs.edu.co' => 'Xiomara Isabel Sanchez Yanci',
            'mcobosm@tvs.edu.co' => 'MARTHA ISABEL COBOS MARTINEZ',
            'mtrodriguez@tvs.edu.co' => 'MARIA DEL TRANSITO RICO RODRIGUEZ'
        ];

        // Asignar usuarios de Mantenimiento (solo actualizar si existen)
        foreach ($maintenanceUsers as $email => $name) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update(['department' => 'Mantenimiento']);
                $this->command->info("Usuario {$name} asignado a Mantenimiento");
            } else {
                $this->command->warn("Usuario {$name} ({$email}) no encontrado - no se puede asignar a Mantenimiento");
            }
        }

        // Asignar usuarios de Servicios Generales (solo actualizar si existen)
        foreach ($servicesUsers as $email => $name) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update(['department' => 'Servicios Generales']);
                $this->command->info("Usuario {$name} asignado a Servicios Generales");
            } else {
                $this->command->warn("Usuario {$name} ({$email}) no encontrado - no se puede asignar a Servicios Generales");
            }
        }
    }
}
