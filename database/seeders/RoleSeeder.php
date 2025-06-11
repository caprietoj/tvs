<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Create base permissions
        $permissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'impersonate',
            // Purchase module permissions
            'view-purchase-requests',
            'create-purchase-requests',
            'approve-purchase-requests',
            'view-purchase-orders',
            'create-purchase-orders',
            'approve-purchase-orders',
            // Document request permissions
            'view-all-document-requests',
            'view-own-document-requests',
            'create-document-requests',
            // Loan request permissions
            'view-loan-requests',
            'create-loan-requests',
            'approve-loan-requests',
            // Additional permissions for contabilidad role
            'view.dashboard',
            'ticket.view',
            'document-requests',
            'kpis.contabilidad.create',
            'kpis.contabilidad.index',
            'kpis.contabilidad.edit',
            'kpis.contabilidad.show',
            'view.budget',
            'view.maintenance',
            'budget.execution',
            'budget.register',
            'documents-contabilidad',
            'view.process.admin',
            'kpis.view',
            'kpis.contabilidad.access',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $compras = Role::firstOrCreate(['name' => 'compras']);
        $contabilidad = Role::firstOrCreate(['name' => 'contabilidad']);
        $rrhh = Role::firstOrCreate(['name' => 'rrhh']);
        $user = Role::firstOrCreate(['name' => 'User']);
        $financiera = Role::firstOrCreate(['name' => 'financiera']);

        // Roles para el módulo de Compras y Órdenes de Compra - Usar firstOrCreate para evitar errores
        $seccion = Role::firstOrCreate(['name' => 'seccion']);
        $direccionGeneral = Role::firstOrCreate(['name' => 'direccion_general']);
        $administracion = Role::firstOrCreate(['name' => 'administracion']);

        // Assign purchase module permissions to these roles
        $seccion->syncPermissions([
            'view-purchase-requests',
            'create-purchase-requests',
        ]);

        $direccionGeneral->syncPermissions([
            'view-purchase-requests',
            'approve-purchase-requests',
            'approve-purchase-orders',
        ]);

        $administracion->syncPermissions([
            'view-purchase-requests',
            'view-purchase-orders',
            'approve-purchase-orders',
        ]);

        // Assign permissions to roles
        // Para Admin, asignar TODOS los permisos del sistema
        $admin->syncPermissions(Permission::all());
        
        $compras->syncPermissions([
            'view-purchase-requests',
            'create-purchase-requests',
            'approve-purchase-requests',
            'view-purchase-orders',
            'create-purchase-orders',
            'approve-purchase-orders',
        ]);
        
        $contabilidad->syncPermissions([
            'view-purchase-requests',
            'view-purchase-orders',
            'view.dashboard',
            'ticket.view',
            'document-requests',
            'kpis.contabilidad.create',
            'kpis.contabilidad.index',
            'kpis.contabilidad.edit',
            'kpis.contabilidad.show',
            'view.budget',
            'view.maintenance',
            'budget.execution',
            'budget.register',
            'documents-contabilidad',
            'view-loan-requests',
            'create-loan-requests',
            'view.process.admin',
            'kpis.view',
            'kpis.contabilidad.access',
        ]);
        
        $rrhh->givePermissionTo([
            'view-all-document-requests',
            'create-document-requests',
            'view-loan-requests',
        ]);
        
        $user->givePermissionTo([
            'view-own-document-requests',
            'create-document-requests',
            'view-loan-requests',
            'create-loan-requests',
        ]);

        $financiera->givePermissionTo([
            'view-loan-requests',
            'approve-loan-requests',
        ]);

        $this->command->info('Roles and permissions seeded successfully');
    }
}
