<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PersonasPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear permiso para gestión de personas
        $permiso = Permission::firstOrCreate(
            ['name' => 'admin.personas'],
            ['guard_name' => 'web']
        );

        echo "✓ Permiso 'admin.personas' creado o ya existía\n";

        // Asignar permiso solo al rol Admin
        $adminRole = Role::where('name', 'Admin')->first();
        
        if ($adminRole) {
            if (!$adminRole->hasPermissionTo('admin.personas')) {
                $adminRole->givePermissionTo('admin.personas');
                echo "✓ Permiso 'admin.personas' asignado al rol Admin\n";
            } else {
                echo "✓ El rol Admin ya tenía el permiso 'admin.personas'\n";
            }
        } else {
            echo "✗ No se encontró el rol Admin\n";
        }

        echo "\n✅ Seeder ejecutado correctamente\n";
    }
}
