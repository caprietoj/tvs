<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class CreateTestUsersCommand extends Command
{
    protected $signature = 'users:create-test';
    protected $description = 'Crear usuarios de prueba para evaluaciones';

    public function handle()
    {
        // Crear roles si no existen
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $rrhhRole = Role::firstOrCreate(['name' => 'RRHH']);

        // Usuario admin
        $admin = User::firstOrCreate(
            ['email' => 'jefesistemas@tvs.edu.co'],
            [
                'name' => 'Cristian Andres Prieto J.',
                'password' => Hash::make('Cr1st1an2024*'),
                'email_verified_at' => now(),
            ]
        );

        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
            $this->info("✅ Rol admin asignado a {$admin->name}");
        } else {
            $this->info("✅ {$admin->name} ya tiene rol admin");
        }

        // Usuario RRHH
        $rrhh = User::firstOrCreate(
            ['email' => 'rrhh@tvs.edu.co'],
            [
                'name' => 'Recursos Humanos',
                'password' => Hash::make('rrhh2024'),
                'email_verified_at' => now(),
            ]
        );

        if (!$rrhh->hasRole('RRHH')) {
            $rrhh->assignRole('RRHH');
            $this->info("✅ Rol RRHH asignado a {$rrhh->name}");
        } else {
            $this->info("✅ {$rrhh->name} ya tiene rol RRHH");
        }

        $this->newLine();
        $this->info("=== USUARIOS DE PRUEBA CREADOS ===");
        $this->info("Admin: jefesistemas@tvs.edu.co / Cr1st1an2024*");
        $this->info("RRHH: rrhh@tvs.edu.co / rrhh2024");

        return 0;
    }
}
