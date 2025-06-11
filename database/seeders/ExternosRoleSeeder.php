<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ExternosRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Limpiar cache de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        try {
            // Crear el rol de externos
            $externosRole = Role::firstOrCreate(['name' => 'externos']);
            
            // Crear los permisos necesarios si no existen
            $viewDashboardPermission = Permission::firstOrCreate(['name' => 'view.dashboard']);
            $viewTicketsPermission = Permission::firstOrCreate(['name' => 'ticket.view']);
            $viewSettingsPermission = Permission::firstOrCreate(['name' => 'view.settings']);
            $viewSalidasPermission = Permission::firstOrCreate(['name' => 'view.salidas']);
            $viewEventsPermission = Permission::firstOrCreate(['name' => 'view.events']);
            $viewCalendarPermission = Permission::firstOrCreate(['name' => 'view.calendar']);
            
            // Asignar los permisos al rol
            $externosRole->givePermissionTo([
                $viewDashboardPermission,
                $viewTicketsPermission,
                $viewSettingsPermission,
                $viewSalidasPermission,
                $viewEventsPermission,
                $viewCalendarPermission
            ]);
            
            // Agregar permiso específico para agregar novedades en eventos
            $addNoveltyPermission = Permission::firstOrCreate(['name' => 'add-event-novelties']);
            $externosRole->givePermissionTo($addNoveltyPermission);

            $this->command->info('Rol "externos" creado y permisos asignados correctamente.');
            
        } catch (\Exception $e) {
            $this->command->error('Error al crear el rol "externos": ' . $e->getMessage());
        }
    }
}
