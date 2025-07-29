<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignJefeInmediatoRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assign:jefe-inmediato {email : Email del usuario}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asignar el rol jefe_inmediato a un usuario por su email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        // Buscar el usuario
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("❌ Usuario con email '{$email}' no encontrado");
            return 1;
        }
        
        // Verificar que el rol existe
        $role = Role::where('name', 'jefe_inmediato')->first();
        
        if (!$role) {
            $this->error('❌ El rol jefe_inmediato no existe. Ejecute primero: php artisan db:seed --class=RolesAndPermissionsSeeder');
            return 1;
        }
        
        // Verificar si ya tiene el rol
        if ($user->hasRole('jefe_inmediato')) {
            $this->warn("⚠️  El usuario {$user->name} ({$email}) ya tiene el rol jefe_inmediato");
            return 0;
        }
        
        // Asignar el rol
        $user->assignRole('jefe_inmediato');
        
        $this->info("✅ Rol jefe_inmediato asignado exitosamente a {$user->name} ({$email})");
        
        // Mostrar los roles actuales del usuario
        $this->info("Roles actuales del usuario:");
        foreach ($user->roles as $userRole) {
            $this->line("  - {$userRole->name}");
        }
        
        return 0;
    }
}
