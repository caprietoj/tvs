<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ServiciosGeneralesRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear o obtener el rol de Servicios Generales
        $role = Role::firstOrCreate(['name' => 'servicios-generales']);

        // Permisos para Servicios Generales
        $permissions = [
            // Eventos
            'view.events',
            'view.calendar',
            'confirm.events',
            'events.create',
            'events.edit',
            'events.delete',
            
            // Equipment (si necesitan)
            'equipment.view',
            'equipment.request',
            
            // Maintenance
            'maintenance.view',
            'maintenance.create',
            'maintenance.edit',
            'maintenance.update-status',
        ];

        // Crear permisos si no existen y asignarlos al rol
        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName]);
            if (!$role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }

        $this->command->info('Rol "servicios-generales" creado/actualizado con permisos');

        // Asignar el rol a los usuarios de Servicios Generales
        $servicesUsers = [
            'amayala@tvs.edu.co',
            'agomez@tvs.edu.co',
            'supervsergenerales@tvs.edu.co',
            'xsanchezy@tvs.edu.co',
            'mcobosm@tvs.edu.co',
            'mtrodriguez@tvs.edu.co'
        ];

        foreach ($servicesUsers as $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                // Asignar rol si no lo tiene
                if (!$user->hasRole('servicios-generales')) {
                    $user->assignRole('servicios-generales');
                    $this->command->info("Rol asignado a {$user->name} ({$email})");
                } else {
                    $this->command->info("Usuario {$user->name} ya tiene el rol servicios-generales");
                }
                
                // Actualizar departamento
                $user->update(['department' => 'Servicios Generales']);
            } else {
                $this->command->warn("Usuario {$email} no encontrado en la base de datos");
            }
        }

        $this->command->info('Usuarios de Servicios Generales configurados correctamente');
    }
}
