<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class ComprasUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si el usuario ya existe
        $existingUser = User::where('email', 'compras@tvs.edu.co')->first();
        
        if (!$existingUser) {
            // Crear el usuario compras@tvs.edu.co
            $user = User::create([
                'name' => 'Usuario Compras',
                'email' => 'compras@tvs.edu.co',
                'password' => Hash::make('compras123'), // Contraseña temporal
                'department' => 'Compras',
                'first_login' => true,
                'email_verified_at' => now(),
            ]);
            
            // Asignar el rol 'compras'
            $comprasRole = Role::where('name', 'compras')->first();
            if ($comprasRole) {
                $user->assignRole($comprasRole);
                $this->command->info('Usuario compras@tvs.edu.co creado y asignado al rol "compras"');
            } else {
                $this->command->warn('Rol "compras" no encontrado. Creando usuario sin rol.');
            }
        } else {
            // Si el usuario existe, verificar que tenga el rol 'compras'
            $comprasRole = Role::where('name', 'compras')->first();
            if ($comprasRole && !$existingUser->hasRole('compras')) {
                $existingUser->assignRole($comprasRole);
                $this->command->info('Rol "compras" asignado al usuario existente compras@tvs.edu.co');
            } else {
                $this->command->info('Usuario compras@tvs.edu.co ya existe y tiene el rol "compras"');
            }
        }
    }
}