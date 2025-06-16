<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\PerformanceEvaluation;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CreateTestPerformanceEvaluations extends Command
{
    protected $signature = 'test:create-performance-evaluations {--count=5}';
    protected $description = 'Crear evaluaciones de desempeño de prueba';

    public function handle()
    {
        $count = $this->option('count');
        
        $this->info("Creando {$count} evaluaciones de desempeño de prueba...");
          // Obtener usuarios para las pruebas
        $employees = User::limit(10)->get();
        $supervisors = User::role(['admin'])->limit(5)->get();
        
        // Si no hay suficientes admins, usar usuarios regulares
        if ($supervisors->count() === 0) {
            $supervisors = User::limit(5)->get();
        }
        
        if ($employees->count() === 0) {
            $this->error('No hay usuarios disponibles para crear evaluaciones de prueba');
            return 1;
        }
        
        if ($supervisors->count() === 0) {
            $this->error('No hay supervisores disponibles para crear evaluaciones de prueba');
            return 1;
        }
        
        for ($i = 0; $i < $count; $i++) {
            $employee = $employees->random();
            $supervisor = $supervisors->random();
            
            // Crear evaluación
            $evaluation = PerformanceEvaluation::create([
                'user_id' => $employee->id,
                'evaluator_id' => $supervisor->id,
                'evaluation_type' => collect(['periodo_prueba', 'periodica'])->random(),
                'evaluation_period_start' => Carbon::now()->subMonths(6),
                'evaluation_period_end' => Carbon::now()->subDays(1),
                'status' => 'draft'
            ]);
            
            // Simular autoevaluación completada para algunas evaluaciones
            if (rand(1, 10) > 3) {
                $this->createSampleSelfEvaluation($evaluation);
                
                // Simular evaluación del supervisor para algunas
                if (rand(1, 10) > 5) {
                    $this->createSampleSupervisorEvaluation($evaluation);
                }
            }
            
            $this->line("Evaluación creada: {$employee->name} evaluado por {$supervisor->name} - Estado: {$evaluation->status}");
        }
        
        $this->info('Evaluaciones de prueba creadas exitosamente!');
        return 0;
    }
    
    private function createSampleSelfEvaluation(PerformanceEvaluation $evaluation)
    {
        // Objetivos del cargo
        $objectivesSection = [
            'fortalecimiento_institucional' => [
                'contribuye_mision_vision' => rand(3, 5),
                'conoce_politicas' => rand(3, 5),
                'participa_mejoras' => rand(3, 5),
                'promueve_valores' => rand(3, 5)
            ],
            'vocacion_servicio' => [
                'atencion_usuarios' => rand(3, 5),
                'compromiso_servicio' => rand(3, 5),
                'amabilidad_trato' => rand(3, 5)
            ],
            'conocimiento' => [
                'dominio_cargo' => rand(3, 5),
                'actualizacion_conocimientos' => rand(3, 5)
            ]
        ];
        
        // Competencias organizacionales
        $organizationalCompetencies = [
            'disposicion_cambio' => rand(3, 5),
            'comunicacion' => rand(3, 5),
            'orientacion_resultados' => rand(3, 5),
            'uso_eficiente_recursos' => rand(3, 5),
            'relacion_demas' => rand(3, 5),
            'liderazgo' => rand(3, 5),
            'trabajo_equipo' => rand(3, 5),
            'gestion_documental' => rand(3, 5),
            'negociacion_mediacion' => rand(3, 5)
        ];
        
        // Competencias técnicas
        $technicalCompetencies = [
            'conocimiento_ingles' => collect(['No aplica', 'Básico', 'Intermedio', 'Avanzado'])->random(),
            'postgrados' => collect(['No tiene', 'Especialización', 'Maestría', 'Doctorado'])->random(),
            'formacion_adicional' => 'Cursos de capacitación en el área'
        ];
        
        // Seguridad y salud en el trabajo
        $safetyHealthSection = [
            'cumple_normas_sst' => collect(['si', 'no'])->random(),
            'participa_capacitaciones' => 'si',
            'reporta_incidentes' => 'si',
            'usa_epp' => 'si',
            'conoce_procedimientos' => 'si'
        ];
        
        // Calcular puntajes
        $objectivesScore = $this->calculateObjectivesScore($objectivesSection);
        $competenciesScore = $this->calculateCompetenciesScore($organizationalCompetencies);
        
        $evaluation->update([
            'objectives_section' => $objectivesSection,
            'objectives_self_score' => $objectivesScore,
            'organizational_competencies' => $organizationalCompetencies,
            'competencies_self_score' => $competenciesScore,
            'technical_competencies' => $technicalCompetencies,
            'safety_health_section' => $safetyHealthSection,
            'self_observations' => 'Observaciones de ejemplo para la autoevaluación. Me siento satisfecho con mi desempeño durante este período.',
            'status' => 'self_completed',
            'self_evaluation_completed_at' => now()
        ]);
        
        $evaluation->update([
            'final_self_score' => $evaluation->calculateSelfScore(),
            'performance_level' => $evaluation->getPerformanceLevel($evaluation->calculateSelfScore())
        ]);
    }
    
    private function createSampleSupervisorEvaluation(PerformanceEvaluation $evaluation)
    {
        // Simular evaluación del supervisor con pequeñas variaciones
        $objectivesSection = [
            'fortalecimiento_institucional' => [
                'contribuye_mision_vision' => rand(3, 5),
                'conoce_politicas' => rand(3, 5),
                'participa_mejoras' => rand(3, 5),
                'promueve_valores' => rand(3, 5)
            ],
            'vocacion_servicio' => [
                'atencion_usuarios' => rand(3, 5),
                'compromiso_servicio' => rand(3, 5),
                'amabilidad_trato' => rand(3, 5)
            ],
            'conocimiento' => [
                'dominio_cargo' => rand(3, 5),
                'actualizacion_conocimientos' => rand(3, 5)
            ]
        ];
        
        $organizationalCompetencies = [
            'disposicion_cambio' => rand(3, 5),
            'comunicacion' => rand(3, 5),
            'orientacion_resultados' => rand(3, 5),
            'uso_eficiente_recursos' => rand(3, 5),
            'relacion_demas' => rand(3, 5),
            'liderazgo' => rand(3, 5),
            'trabajo_equipo' => rand(3, 5),
            'gestion_documental' => rand(3, 5),
            'negociacion_mediacion' => rand(3, 5)
        ];
        
        // Calcular puntajes del supervisor
        $objectivesScore = $this->calculateObjectivesScore($objectivesSection);
        $competenciesScore = $this->calculateCompetenciesScore($organizationalCompetencies);
        
        $evaluation->update([
            'objectives_supervisor_score' => $objectivesScore,
            'competencies_supervisor_score' => $competenciesScore,
            'supervisor_observations' => 'Observaciones del supervisor. El empleado demuestra un buen desempeño en general con oportunidades de mejora en algunas áreas específicas.',
            'status' => 'completed',
            'supervisor_evaluation_completed_at' => now()
        ]);
        
        $supervisorScore = $evaluation->calculateSupervisorScore();
        $averageScore = ($evaluation->final_self_score + $supervisorScore) / 2;
        
        $evaluation->update([
            'final_supervisor_score' => $supervisorScore,
            'final_average_score' => $averageScore,
            'performance_level' => $evaluation->getPerformanceLevel($averageScore)
        ]);
    }
    
    private function calculateObjectivesScore(array $objectives): float
    {
        $objectivesQuestions = PerformanceEvaluation::getObjectivesQuestions();
        $totalScore = 0;
        
        foreach ($objectivesQuestions as $section => $config) {
            if (isset($objectives[$section])) {
                $sectionScore = 0;
                $questionCount = count($config['questions']);
                
                foreach ($objectives[$section] as $score) {
                    $sectionScore += (int) $score;
                }
                
                $sectionAverage = $questionCount > 0 ? $sectionScore / $questionCount : 0;
                $totalScore += $sectionAverage * $config['weight'];
            }
        }
        
        return round($totalScore, 2);
    }
    
    private function calculateCompetenciesScore(array $competencies): float
    {
        $totalScore = 0;
        $competencyCount = count($competencies);
        
        foreach ($competencies as $score) {
            $totalScore += (int) $score;
        }
        
        return $competencyCount > 0 ? round($totalScore / $competencyCount, 2) : 0;
    }
}
