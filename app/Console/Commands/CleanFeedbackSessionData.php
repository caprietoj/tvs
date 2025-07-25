<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FeedbackSession;
use Carbon\Carbon;

class CleanFeedbackSessionData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feedback-sessions:clean-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia datos corruptos de las sesiones de retroalimentación';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando datos de sesiones de retroalimentación...');

        $sessions = FeedbackSession::all();
        $cleaned = 0;

        foreach ($sessions as $session) {
            $needsUpdate = false;
            $updateData = [];

            // Verificar scheduled_datetime
            if ($session->scheduled_datetime === null || $session->scheduled_datetime === '') {
                $this->warn("Sesión #{$session->id}: scheduled_datetime es null o vacío");
                $updateData['scheduled_datetime'] = now()->addDay()->setTime(9, 0);
                $needsUpdate = true;
            }

            // Verificar si scheduled_datetime es una fecha válida
            try {
                if ($session->scheduled_datetime && !($session->scheduled_datetime instanceof Carbon)) {
                    $this->warn("Sesión #{$session->id}: scheduled_datetime no es una instancia válida de Carbon");
                    $updateData['scheduled_datetime'] = Carbon::parse($session->scheduled_datetime);
                    $needsUpdate = true;
                }
            } catch (\Exception $e) {
                $this->error("Sesión #{$session->id}: Error al parsear scheduled_datetime - {$e->getMessage()}");
                $updateData['scheduled_datetime'] = now()->addDay()->setTime(9, 0);
                $needsUpdate = true;
            }

            if ($needsUpdate) {
                $session->update($updateData);
                $cleaned++;
                $this->info("Sesión #{$session->id}: Datos corregidos");
            }
        }

        if ($cleaned > 0) {
            $this->info("Se corrigieron {$cleaned} sesiones de retroalimentación.");
        } else {
            $this->info("No se encontraron datos corruptos.");
        }
    }
}
