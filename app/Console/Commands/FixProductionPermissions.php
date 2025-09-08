<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class FixProductionPermissions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tvs:fix-production-permissions';

    /**
     * The console command description.
     */
    protected $description = 'Corrige los permisos faltantes en producción para equipment.blocks.manage y purchase-orders.no-quotation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Iniciando corrección de permisos en producción...');

        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos faltantes si no existen
        $permissions = [
            'equipment.blocks.manage' => 'Gestionar bloqueos de equipos',
            'purchase-orders.no-quotation' => 'Crear órdenes de compra sin cotización'
        ];

        foreach ($permissions as $permission => $description) {
            $permissionObj = Permission::firstOrCreate(['name' => $permission]);
            if ($permissionObj->wasRecentlyCreated) {
                $this->info("✅ Permiso creado: {$permission}");
            } else {
                $this->info("ℹ️  Permiso ya existe: {$permission}");
            }
        }

        // Asignar permisos a roles específicos
        $rolePermissions = [
            'admin' => array_keys($permissions),
            'compras' => array_keys($permissions),
            'technician' => ['equipment.blocks.manage']
        ];

        foreach ($rolePermissions as $roleName => $rolePermissions) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                foreach ($rolePermissions as $permissionName) {
                    if (!$role->hasPermissionTo($permissionName)) {
                        $role->givePermissionTo($permissionName);
                        $this->info("✅ Permiso '{$permissionName}' asignado al rol '{$roleName}'");
                    } else {
                        $this->info("ℹ️  El rol '{$roleName}' ya tiene el permiso '{$permissionName}'");
                    }
                }
            } else {
                $this->warn("⚠️  Rol '{$roleName}' no encontrado");
            }
        }

        // Verificar usuarios específicos (puedes añadir más si es necesario)
        $adminEmails = ['intranet@tvs.edu.co']; // Agrega más emails si es necesario
        
        foreach ($adminEmails as $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                if (!$user->hasRole('admin')) {
                    $user->assignRole('admin');
                    $this->info("✅ Rol 'admin' asignado al usuario: {$email}");
                } else {
                    $this->info("ℹ️  El usuario {$email} ya tiene el rol 'admin'");
                }
            } else {
                $this->warn("⚠️  Usuario no encontrado: {$email}");
            }
        }

        // Verificar que los permisos están correctamente asignados
        $this->info("\n📋 Verificación final:");
        
        $adminRole = Role::where('name', 'admin')->first();
        $comprasRole = Role::where('name', 'compras')->first();
        $technicianRole = Role::where('name', 'technician')->first();

        $this->table(['Rol', 'equipment.blocks.manage', 'purchase-orders.no-quotation'], [
            [
                'admin', 
                $adminRole && $adminRole->hasPermissionTo('equipment.blocks.manage') ? '✅' : '❌',
                $adminRole && $adminRole->hasPermissionTo('purchase-orders.no-quotation') ? '✅' : '❌'
            ],
            [
                'compras', 
                $comprasRole && $comprasRole->hasPermissionTo('equipment.blocks.manage') ? '✅' : '❌',
                $comprasRole && $comprasRole->hasPermissionTo('purchase-orders.no-quotation') ? '✅' : '❌'
            ],
            [
                'technician', 
                $technicianRole && $technicianRole->hasPermissionTo('equipment.blocks.manage') ? '✅' : '❌',
                'N/A'
            ]
        ]);

        // Limpiar cachés
        $this->info("\n🧹 Limpiando cachés...");
        $this->call('permission:cache-reset');
        $this->call('cache:clear');
        $this->call('config:clear');

        $this->info("\n🎉 ¡Corrección de permisos completada!");
        $this->info("📝 Las siguientes URLs deberían estar ahora disponibles:");
        $this->info("   - http://127.0.0.1:8000/equipment/blocks");
        $this->info("   - Sección 'Solicitudes Aprobadas Sin Cotización' en http://127.0.0.1:8000/purchase-orders");
        
        return 0;
    }
}
