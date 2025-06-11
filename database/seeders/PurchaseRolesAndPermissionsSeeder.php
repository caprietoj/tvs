<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PurchaseRolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Crear permisos para el módulo de compras
        $permissions = [
            'view.purchases',
            'create.purchase-requests',
            'edit.purchase-requests',
            'delete.purchase-requests',
            'manage.quotations',
            'pre-approve.quotations',
            'create.purchase-orders',
            'edit.purchase-orders',
            'approve.orders',
            'reject.orders',
            'register.payments',
            'approve.requests' // Permiso para aprobar definitivamente solicitudes pre-aprobadas
        ];

        foreach ($permissions as $permission) {
            // Verificar si el permiso ya existe antes de crearlo
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Crear roles para el módulo de compras
        $roles = [
            'compras' => [
                'view.purchases',
                'create.purchase-requests',
                'edit.purchase-requests',
                'manage.quotations',
                'create.purchase-orders',
                'edit.purchase-orders',
            ],
            'seccion' => [
                'view.purchases',
                'create.purchase-requests',
                'pre-approve.quotations',
            ],
            'direccion_general' => [
                'view.purchases',
                'approve.orders',
                'reject.orders',
                'approve.requests', // Añadido el permiso para aprobar solicitudes
            ],
            'administracion' => [
                'view.purchases',
                'approve.orders',
                'reject.orders',
            ],
            'contabilidad' => [
                'view.purchases',
                'register.payments',
            ]
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            // Crear el rol si no existe
            $role = Role::firstOrCreate(['name' => $roleName]);
            
            // Asignar permisos al rol
            $role->syncPermissions($rolePermissions);
        }

        // Asignar todos los permisos al rol admin si existe
        if ($adminRole = Role::where('name', 'admin')->first()) {
            $adminRole->givePermissionTo($permissions);
        }
    }
}
