<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PurchaseRequestPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear el permiso para aprobar solicitudes de compra
        Permission::firstOrCreate(['name' => 'approve-purchase-requests']);

        // Asignar el permiso al rol de administrador
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo('approve-purchase-requests');
        }

        // También podemos crear un nuevo rol específico para los aprobadores de compras
        $approverRole = Role::firstOrCreate(['name' => 'compras-aprobador']);
        $approverRole->givePermissionTo('approve-purchase-requests');

        $this->command->info('Permiso para aprobar solicitudes de compra creado y asignado a roles correspondientes.');
    }
}
