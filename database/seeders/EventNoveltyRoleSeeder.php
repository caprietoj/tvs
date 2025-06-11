<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class EventNoveltyRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Crear el nuevo rol
        $role = Role::firstOrCreate(['name' => 'modificacion-novedad']);

        // Crear permisos asociados (opcional, podemos usar solo el rol)
        $permission = Permission::firstOrCreate(['name' => 'edit-event-novelties']);
        $permission2 = Permission::firstOrCreate(['name' => 'delete-event-novelties']);

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