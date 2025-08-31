<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SalidaPedagogica;
use Carbon\Carbon;

class UpdateSalidasEstado extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salidas:update-estado';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza automáticamente el estado de las salidas pedagógicas de Programada a Realizada cuando la fecha ya pasó';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando actualización de estados de salidas pedagógicas...');

        $salidasProgramadas = SalidaPedagogica::where('estado', 'Programada')
            ->whereDate('fecha_salida', '<', Carbon::now())
            ->get();

        $updated = 0;

        foreach ($salidasProgramadas as $salida) {
            $salida->update(['estado' => 'Realizada']);
            $updated++;
            
            $this->line("✓ Salida {$salida->consecutivo} actualizada a 'Realizada' (Fecha: {$salida->fecha_salida->format('d/m/Y')})");
        }

        if ($updated > 0) {
            $this->info("✅ Se actualizaron {$updated} salidas pedagógicas.");
        } else {
            $this->info("ℹ️  No hay salidas pedagógicas que actualizar.");
        }

        return Command::SUCCESS;
    }
}
