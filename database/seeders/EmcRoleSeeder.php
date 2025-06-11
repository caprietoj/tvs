<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class EmcRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos adicionales para autorización si no existen
        $additionalPermissions = [
            'approve-purchase-requests',
            'approve-material-requests', 
            'approve-copy-requests',
            'view-purchase-requests',
            'view-purchase-orders',
            'manage.quotations',
            'pre-approve.quotations',
            'cotizaciones',
            'preaprobaciones',
            'aprobaciones',
            'ordenes_compra',
            'fotocopias_list',
        ];

        foreach ($additionalPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Obtener el rol profesor para copiar sus permisos
        $profesorRole = Role::where('name', 'profesor')->first();
        
        if (!$profesorRole) {
            $this->command->error('El rol profesor no existe. Ejecuta primero RolesAndPermissionsSeeder.');
            return;
        }

        // Crear el rol EMC
        $emcRole = Role::firstOrCreate(['name' => 'EMC']);

        // Obtener todos los permisos del rol profesor
        $profesorPermissions = $profesorRole->permissions->pluck('name')->toArray();

        // Agregar los permisos adicionales para autorización
        $emcPermissions = array_merge($profesorPermissions, $additionalPermissions);

        // Eliminar duplicados
        $emcPermissions = array_unique($emcPermissions);

        // Asignar todos los permisos al rol EMC
        $emcRole->syncPermissions($emcPermissions);

        $this->command->info('Rol EMC creado exitosamente con ' . count($emcPermissions) . ' permisos.');
        $this->command->info('Permisos del rol EMC:');
        foreach ($emcPermissions as $permission) {
            $this->command->line('  - ' . $permission);
        }
    }
}
