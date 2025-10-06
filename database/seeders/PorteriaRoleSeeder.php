<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PorteriaRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Permisos SOLO para el rol porteria (registro de entrada/salida)
        $permisosPorteria = [
            'view.porteria',           // Permiso para ver el módulo principal
            'porteria.registro',       // Permiso para el registro de entrada/salida
            'porteria.registro.view',  // Ver registros
            'porteria.registro.create',// Crear registros
        ];

        // Permisos SOLO para admin y rrhh (gestión completa)
        $permisosAdminRRHH = [
            'porteria.registro.edit',  // Editar registros
            'porteria.registro.delete',// Eliminar registros
            'admin.personas',          // Permiso para gestión de personas (incluye importar)
        ];

        // Todos los permisos combinados
        $todosLosPermisos = array_merge($permisosPorteria, $permisosAdminRRHH);

        // Crear todos los permisos
        foreach ($todosLosPermisos as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear el rol de Portería y asignar SOLO sus permisos
        $porteriaRole = Role::firstOrCreate(['name' => 'porteria']);
        $porteriaRole->syncPermissions($permisosPorteria);

        // Asegurarse de que los roles Admin y RRHH tengan TODOS los permisos
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($todosLosPermisos);
        }

        $rrhhRole = Role::where('name', 'rrhh')->first();
        if ($rrhhRole) {
            $rrhhRole->givePermissionTo($todosLosPermisos);
        }

        $this->command->info('✅ Permisos de Portería configurados exitosamente');
        $this->command->info('');
        $this->command->info('📋 Rol: porteria');
        $this->command->info('   - Permisos: ' . count($permisosPorteria) . ' (solo registro)');
        $this->command->info('   - ✓ Ver módulo de portería');
        $this->command->info('   - ✓ Registrar entrada/salida');
        $this->command->info('   - ✓ Ver registros del día');
        $this->command->info('   - ✗ NO puede gestionar personas');
        $this->command->info('   - ✗ NO puede editar/eliminar registros');
        $this->command->info('');
        $this->command->info('📋 Roles: admin, rrhh');
        $this->command->info('   - Permisos: ' . count($todosLosPermisos) . ' (gestión completa)');
        $this->command->info('   - ✓ Todas las funciones de portería');
        $this->command->info('   - ✓ Gestionar personas (crear, editar, eliminar, importar)');
    }
}
