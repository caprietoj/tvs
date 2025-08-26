<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Crear usuarios de prueba para el sistema de compras
     */
    public function run(): void
    {
        // Usuario administrador de prueba
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@tvs.edu.co'],
            [
                'name' => 'Administrador de Prueba',
                'email' => 'admin@tvs.edu.co',
                'password' => Hash::make('password123'),
                'cargo' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Usuario de compras
        $comprasUser = User::updateOrCreate(
            ['email' => 'compras@tvs.edu.co'],
            [
                'name' => 'Usuario de Compras',
                'email' => 'compras@tvs.edu.co',
                'password' => Hash::make('password123'),
                'cargo' => 'compras',
                'email_verified_at' => now(),
            ]
        );

        // Usuario coordinador
        $coordinadorUser = User::updateOrCreate(
            ['email' => 'coordinador@tvs.edu.co'],
            [
                'name' => 'Coordinador de Prueba',
                'email' => 'coordinador@tvs.edu.co',
                'password' => Hash::make('password123'),
                'cargo' => 'coordinador',
                'email_verified_at' => now(),
            ]
        );

        // Usuario docente
        $docenteUser = User::updateOrCreate(
            ['email' => 'docente@tvs.edu.co'],
            [
                'name' => 'Docente de Prueba',
                'email' => 'docente@tvs.edu.co',
                'password' => Hash::make('password123'),
                'cargo' => 'docente',
                'email_verified_at' => now(),
            ]
        );

        // Usuario empleado
        $empleadoUser = User::updateOrCreate(
            ['email' => 'empleado@tvs.edu.co'],
            [
                'name' => 'Empleado de Prueba',
                'email' => 'empleado@tvs.edu.co',
                'password' => Hash::make('password123'),
                'cargo' => 'empleado',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Usuarios de prueba creados exitosamente:');
        $this->command->info('- Admin: admin@tvs.edu.co (password: password123)');
        $this->command->info('- Compras: compras@tvs.edu.co (password: password123)');
        $this->command->info('- Coordinador: coordinador@tvs.edu.co (password: password123)');
        $this->command->info('- Docente: docente@tvs.edu.co (password: password123)');
        $this->command->info('- Empleado: empleado@tvs.edu.co (password: password123)');
    }
}