<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class DirectorGeneralRoleSeeder extends Seeder
{
    /**
     * Crear rol específico para el Director General con acceso limitado
     * al módulo de Almacén y Compras (solo Aprobaciones)
     */
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear el rol director-general
        $directorGeneralRole = Role::firstOrCreate(['name' => 'director-general']);

        // Permisos básicos del sistema que debe mantener
        $basicPermissions = [
            'view.dashboard',
            'ticket.view',
            'document-requests',
            'view.maintenance',
            'view-loan-requests',
            'create-loan-requests',
            'view.space-reservations',
            'create.space-reservations',
            'view.reservas',
            'view.events',
            'view.salidas',
            'view.calendar',
            'view-own-performance-evaluations',
            'complete-own-performance-evaluations',
        ];

        // Permisos específicos para el módulo de Almacén y Compras
        // SOLO para Aprobaciones Finales (SIN acceso a Preaprobaciones)
        $comprasPermissions = [
            'almacen',      // Permiso necesario para ver el módulo principal
            'aprobaciones', // SOLO Acceso a Aprobaciones Finales
            // 'preaprobaciones' - REMOVIDO: No debe tener acceso a Preaprobaciones
        ];

        // Permisos específicos para previsitas (ya tiene acceso de solo lectura)
        $previstasPermissions = [
            'previsitas.view',
            'previsitas.show',
            'previsitas.download',
            'previsitas.dashboard'
        ];

        // Combinar todos los permisos
        $allPermissions = array_merge($basicPermissions, $comprasPermissions, $previstasPermissions);

        // Crear permisos si no existen
        foreach ($allPermissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        // Asignar permisos al rol
        $directorGeneralRole->syncPermissions($allPermissions);

        $this->command->info('✅ Rol director-general creado exitosamente con ' . count($allPermissions) . ' permisos.');
        $this->command->info('📋 Permisos del rol director-general:');
        foreach ($allPermissions as $permission) {
            $this->command->line('  - ' . $permission);
        }

        // Buscar y asignar el rol al usuario generaldirector@tvs.edu.co
        $user = User::where('email', 'generaldirector@tvs.edu.co')->first();
        
        if ($user) {
            // Remover todos los roles existentes para evitar permisos excesivos
            $user->syncRoles(['director-general']);
            $this->command->info('✅ Rol director-general asignado al usuario generaldirector@tvs.edu.co');
            $this->command->info('🔒 Se removieron otros roles existentes para mantener la restricción de acceso');
        } else {
            $this->command->warn('⚠️  Usuario generaldirector@tvs.edu.co no encontrado');
        }

        // Verificar la configuración final
        $this->verifyConfiguration();
    }

    /**
     * Verificar que la configuración del rol sea correcta
     */
    private function verifyConfiguration()
    {
        $role = Role::where('name', 'director-general')->first();
        $user = User::where('email', 'generaldirector@tvs.edu.co')->first();

        $this->command->info("\n🔍 Verificación de configuración:");
        
        if ($role) {
            $permissions = $role->permissions->pluck('name')->toArray();
            $this->command->info("📦 Total de permisos del rol: " . count($permissions));
            
            // Verificar permisos críticos del módulo de compras
            $criticalPermissions = [
                'almacen' => 'Acceso al módulo de Almacén y Compras',
                'preaprobaciones' => 'Acceso a Preaprobaciones',
                'aprobaciones' => 'Acceso a Aprobaciones Finales',
            ];

            foreach ($criticalPermissions as $perm => $desc) {
                $hasPermission = $role->hasPermissionTo($perm);
                $icon = $hasPermission ? '✅' : '❌';
                $this->command->line("  {$icon} {$desc}: " . ($hasPermission ? 'SÍ' : 'NO'));
            }

            // Verificar que NO tenga permisos que no debería tener
            $forbiddenPermissions = [
                'cotizaciones' => 'Acceso a Cotizaciones',
                'ordenes_compra' => 'Acceso a Órdenes de Compra',
                'fotocopias_list' => 'Acceso a Fotocopias',
                'solicitudes_compra' => 'Acceso a Solicitudes de Compra',
                'listado-proveedores' => 'Acceso a Proveedores',
                'inventario.view' => 'Acceso a Inventario',
            ];

            $this->command->info("\n🚫 Verificación de permisos restringidos:");
            foreach ($forbiddenPermissions as $perm => $desc) {
                $hasPermission = $role->hasPermissionTo($perm);
                $icon = $hasPermission ? '❌ PROBLEMA' : '✅ CORRECTO';
                $this->command->line("  {$icon} {$desc}: " . ($hasPermission ? 'TIENE (debe remover)' : 'NO TIENE'));
            }
        }

        if ($user) {
            $userRoles = $user->roles->pluck('name')->toArray();
            $this->command->info("\n👤 Roles del usuario generaldirector@tvs.edu.co: " . implode(', ', $userRoles));
            
            if (count($userRoles) === 1 && $userRoles[0] === 'director-general') {
                $this->command->info('✅ Usuario configurado correctamente con rol único');
            } else {
                $this->command->warn('⚠️  Usuario tiene múltiples roles o rol incorrecto');
            }
        }
    }
}