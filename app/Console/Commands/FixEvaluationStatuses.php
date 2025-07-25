<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PerformanceEvaluation;

class FixEvaluationStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evaluations:fix-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige los estados de las evaluaciones de desempeño que tienen puntajes del supervisor pero estado incorrecto';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Buscando evaluaciones con estados incorrectos...');

        // Buscar evaluaciones que tienen puntajes del supervisor pero estado incorrecto
        $evaluations = PerformanceEvaluation::where(function($query) {
            $query->where('status', 'self_completed')
                  ->whereNotNull('objectives_supervisor_score')
                  ->whereNotNull('competencies_supervisor_score');
        })->orWhere(function($query) {
            $query->where('status', 'supervisor_review')
                  ->whereNotNull('supervisor_evaluation_completed_at');
        })->get();

        if ($evaluations->isEmpty()) {
            $this->info('No se encontraron evaluaciones con estados incorrectos.');
            return;
        }

        $fixed = 0;
        foreach ($evaluations as $evaluation) {
            $oldStatus = $evaluation->status;
            
            // Si tiene fecha de completado del supervisor, debería estar en 'completed'
            if ($evaluation->supervisor_evaluation_completed_at) {
                $evaluation->update(['status' => 'completed']);
                $this->info("Evaluación #{$evaluation->id}: {$oldStatus} → completed");
                $fixed++;
            }
            // Si tiene puntajes del supervisor pero no fecha de completado, debería estar en 'supervisor_review'
            elseif ($evaluation->objectives_supervisor_score && $evaluation->competencies_supervisor_score) {
                $evaluation->update(['status' => 'supervisor_review']);
                $this->info("Evaluación #{$evaluation->id}: {$oldStatus} → supervisor_review");
                $fixed++;
            }
        }

        $this->info("Se corrigieron {$fixed} evaluaciones.");
    }
}
