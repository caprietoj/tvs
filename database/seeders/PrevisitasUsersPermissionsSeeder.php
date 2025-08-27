<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class PrevisitasUsersPermissionsSeeder extends Seeder
{
    /**
     * Asignar permisos de previsitas a usuarios específicos
     */
    public function run(): void
    {
        // Usuarios con permisos de lector (solo ver)
        $readOnlyUsers = [
            'coordpai@tvs.edu.co',
            'asistentegeneral@tvs.edu.co',
            'coordpep@tvs.edu.co',
            'preschool@tvs.edu.co',
            'dp@tvs.edu.co',
            'generaldirector@tvs.edu.co',
            'escuelamedia@tvs.edu.co'
        ];

        // Usuarios con permisos de editor (crear, editar, eliminar)
        $editorUsers = [
            'asistentebachillerato@tvs.edu.co',
            'asistentepyp@tvs.edu.co',
            'wrueda@tvs.edu.co',
            'asistenteadministrativa@tvs.edu.co'
        ];

        // Permisos de solo lectura
        $readOnlyPermissions = [
            'previsitas.view',
            'previsitas.show',
            'previsitas.download',
            'previsitas.dashboard'
        ];

        // Permisos completos (incluye lectura + escritura)
        $editorPermissions = [
            'previsitas.view',
            'previsitas.show', 
            'previsitas.create',
            'previsitas.edit',
            'previsitas.delete',
            'previsitas.download',
            'previsitas.dashboard'
        ];

        // Crear permisos si no existen
        foreach (array_merge($readOnlyPermissions, ['previsitas.create', 'previsitas.edit', 'previsitas.delete']) as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Asignar permisos a usuarios de solo lectura
        foreach ($readOnlyUsers as $email) {
            $user = User::where('email', $email)->first();
            
            if ($user) {
                // Revocar todos los permisos de previsitas primero
                $user->revokePermissionTo(Permission::where('name', 'like', 'previsitas.%')->get());
                
                // Asignar permisos de solo lectura
                foreach ($readOnlyPermissions as $permission) {
                    $user->givePermissionTo($permission);
                }
                
                $this->command->info("Permisos de lector asignados a: {$email}");
            } else {
                $this->command->warn("Usuario no encontrado: {$email}");
            }
        }

        // Asignar permisos completos a usuarios editores
        foreach ($editorUsers as $email) {
            $user = User::where('email', $email)->first();
            
            if ($user) {
                // Revocar todos los permisos de previsitas primero
                $user->revokePermissionTo(Permission::where('name', 'like', 'previsitas.%')->get());
                
                // Asignar permisos completos
                foreach ($editorPermissions as $permission) {
                    $user->givePermissionTo($permission);
                }
                
                $this->command->info("Permisos de editor asignados a: {$email}");
            } else {
                $this->command->warn("Usuario no encontrado: {$email}");
            }
        }

        $this->command->info('Permisos de previsitas asignados exitosamente.');
    }
}