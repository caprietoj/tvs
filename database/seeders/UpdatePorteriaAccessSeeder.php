<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UpdatePorteriaAccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Crear los permisos para Solicitudes y Encuestas
        $viewSolicitudesPermission = Permission::firstOrCreate(['name' => 'view.solicitudes']);
        $viewEncuestasPermission = Permission::firstOrCreate(['name' => 'view.encuestas']);

        $this->command->info('✅ Permisos creados: view.solicitudes, view.encuestas');

        // Obtener todos los roles excepto portería
        $roles = Role::whereNotIn('name', ['porteria'])->get();

        // Asignar permisos de solicitudes y encuestas a todos los roles excepto portería
        foreach ($roles as $role) {
            $role->givePermissionTo(['view.solicitudes', 'view.encuestas']);
            $this->command->info("   ✓ Permisos asignados al rol: {$role->name}");
        }

        // Verificar que el rol portería NO tenga estos permisos
        $porteriaRole = Role::where('name', 'porteria')->first();
        if ($porteriaRole) {
            // Remover estos permisos si los tuviera
            $porteriaRole->revokePermissionTo(['view.solicitudes', 'view.encuestas']);
            
            $this->command->info('');
            $this->command->info('✅ Rol de Portería actualizado:');
            $this->command->info('   - NO tiene acceso a Solicitudes');
            $this->command->info('   - NO tiene acceso a Encuestas');
            $this->command->info('   - Solo tiene acceso a: Portería');
            $this->command->info('');
            $this->command->info('Permisos del rol portería:');
            foreach ($porteriaRole->permissions as $perm) {
                $this->command->info("   • {$perm->name}");
            }
        }

        $this->command->info('');
        $this->command->info('🎉 Actualización completada exitosamente');
    }
}
