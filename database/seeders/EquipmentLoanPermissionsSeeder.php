<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class EquipmentLoanPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */    public function run()
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        try {
            // Crear el nuevo permiso para entrega y eliminación de reservas
            $permission = Permission::firstOrCreate(['name' => 'equipment.loans.manage']);
            
            // Asignar el permiso sólo al rol admin
            $adminRole = Role::where('name', 'admin')->first();
            
            if ($adminRole) {
                $adminRole->givePermissionTo($permission);
                $this->command->info('Permiso de entrega y eliminación de reservas asignado al rol admin.');
            } else {
                // Si no existe el rol admin, lo creamos
                $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
                $adminRole->givePermissionTo($permission);
                $this->command->info('Se creó el rol admin y se le asignó el permiso de préstamos de equipos.');
            }
            
            // Opcionalmente, también podemos crear un rol específico para la gestión de préstamos
            $equipmentManagerRole = Role::firstOrCreate(['name' => 'equipment_manager', 'guard_name' => 'web']);
            $equipmentManagerRole->givePermissionTo($permission);
            $this->command->info('Se creó/actualizó el rol equipment_manager y se le asignó el permiso de préstamos de equipos.');
            
        } catch (\Exception $e) {
            Log::error('Error al crear permisos de préstamos de equipos: ' . $e->getMessage());
            $this->command->error('Error al crear permisos: ' . $e->getMessage());
        }
    }
}
