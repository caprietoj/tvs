<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('equipment:reset')->dailyAt('18:00');
        
        // Check every minute for expired loans
        $schedule->command('equipment:reset-inventory')->everyMinute();
        
        // Proceso de devolución automática de equipos al finalizar los períodos de clase
        $schedule->command('equipment:process-returns')->everyTenMinutes();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}