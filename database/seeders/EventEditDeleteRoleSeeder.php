<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class EventEditDeleteRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Crear el nuevo rol
        $role = Role::firstOrCreate(['name' => 'edicion-eliminacion-evento']);

        // Crear permisos asociados
        $permission = Permission::firstOrCreate(['name' => 'edit-events']);
        $permission2 = Permission::firstOrCreate(['name' => 'delete-events']);

        // Asignar permisos al rol
        $role->givePermissionTo($permission);
        $role->givePermissionTo($permission2);

        // Asignar el rol al administrador
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permission);
            $adminRole->givePermissionTo($permission2);
        }
    }
}