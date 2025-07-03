<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SpaceReservationPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear el permiso para aprobar reservas de espacios
        Permission::firstOrCreate(['name' => 'approve-space-reservations']);
        
        // Asignar el permiso al rol de administrador
        $adminRole = Role::findByName('admin');
        if ($adminRole) {
            $adminRole->givePermissionTo('approve-space-reservations');
        }
        
        // Asignar el permiso al rol de reservation_manager (gestor de reservas)
        $reservationManagerRole = Role::findByName('reservation_manager');
        if ($reservationManagerRole) {
            $reservationManagerRole->givePermissionTo('approve-space-reservations');
        } else {
            // Si el rol no existe, crearlo
            $reservationManagerRole = Role::create(['name' => 'reservation_manager']);
            $reservationManagerRole->givePermissionTo('approve-space-reservations');
        }
        
        // También puedes asignar este permiso a roles que ya existen como admin-espacios
        $spaceAdminRole = Role::findByName('admin-espacios');
        if ($spaceAdminRole) {
            $spaceAdminRole->givePermissionTo('approve-space-reservations');
        }
    }
}
