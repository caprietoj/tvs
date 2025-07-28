<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class TestGate extends Command
{
    protected $signature = 'test:gate';
    protected $description = 'Test Gates for debugging';

    public function handle()
    {
        $this->info('=== TESTING GATES ===');
        
        // Registrar Gate manualmente para probar
        Gate::define('institucional-access-test', function ($user) {
            return $user->hasRole('admin') || $user->email === 'asistentegeneral@tvs.edu.co';
        });
        
        $this->info('Gate registered manually');
        
        // Verificar si está definido
        if (Gate::has('institucional-access-test')) {
            $this->info('✅ Gate institucional-access-test is defined');
        } else {
            $this->error('❌ Gate institucional-access-test is NOT defined');
        }
        
        // Probar con usuarios
        $user = User::where('email', 'asistentegeneral@tvs.edu.co')->first();
        if ($user) {
            $result = Gate::forUser($user)->allows('institucional-access-test');
            $this->info("asistentegeneral@tvs.edu.co access: " . ($result ? 'YES' : 'NO'));
        }
        
        $admin = User::role('admin')->first();
        if ($admin) {
            $result = Gate::forUser($admin)->allows('institucional-access-test');
            $this->info("Admin access: " . ($result ? 'YES' : 'NO'));
        }
        
        return 0;
    }
}
