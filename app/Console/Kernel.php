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
        
        // Limpiar archivos ICS temporales cada 6 horas
        $schedule->command('calendar:clean-temp-ics --older-than=6')->everySixHours();
        
        // Reparación automática de órdenes de compra (solo dry-run para logging)
        $schedule->command('orders:repair --dry-run')->daily()
            ->description('Validación diaria de integridad de órdenes de compra');
            
        // Actualizar estados de salidas pedagógicas de Programada a Realizada
        $schedule->command('salidas:update-estado')->daily()
            ->description('Actualizar automáticamente estados de salidas pedagógicas');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}