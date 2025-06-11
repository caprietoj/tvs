<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class InventoryPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear permisos para el módulo de inventario
        $permissions = [
            'inventario.view' => 'Ver inventario',
            'inventario.create' => 'Crear elementos en inventario',
            'inventario.edit' => 'Editar elementos en inventario',
            'inventario.delete' => 'Eliminar elementos en inventario',
            'inventario.import' => 'Importar datos de inventario',
        ];

        foreach ($permissions as $permission => $description) {
            Permission::firstOrCreate(['name' => $permission], ['name' => $permission]);
        }

        // Asignar permisos a roles existentes
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(array_keys($permissions));

        // Rol para manejo de compras - debe tener acceso al inventario
        $comprasRole = Role::firstOrCreate(['name' => 'compras']);
        $comprasRole->givePermissionTo([
            'inventario.view',
            'inventario.create',
            'inventario.edit',
            'inventario.import',
        ]);

        // Rol para los usuarios que solo pueden ver el inventario
        $viewerRole = Role::firstOrCreate(['name' => 'viewer']);
        $viewerRole->givePermissionTo([
            'inventario.view',
        ]);
    }
}
