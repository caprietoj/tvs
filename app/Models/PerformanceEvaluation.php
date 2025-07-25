<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'evaluator_id',
        'evaluation_type',
        'status',
        'evaluation_period_start',
        'evaluation_period_end',
        'objectives_section',
        'objectives_self_score',
        'objectives_supervisor_score',
        'objectives_section_supervisor',
        'organizational_competencies',
        'competencies_self_score',
        'competencies_supervisor_score',
        'organizational_competencies_supervisor',
        'technical_competencies',
        'safety_health_section',
        'self_observations',
        'supervisor_observations',
        'final_self_score',
        'final_supervisor_score',
        'final_average_score',
        'performance_level',
        'self_evaluation_completed_at',
        'supervisor_evaluation_completed_at'
    ];

    protected $casts = [
        'objectives_section' => 'array',
        'objectives_section_supervisor' => 'array',
        'organizational_competencies' => 'array',
        'organizational_competencies_supervisor' => 'array',
        'technical_competencies' => 'array',
        'safety_health_section' => 'array',
        'evaluation_period_start' => 'date',
        'evaluation_period_end' => 'date',
        'self_evaluation_completed_at' => 'datetime',
        'supervisor_evaluation_completed_at' => 'datetime',
        'objectives_self_score' => 'decimal:2',
        'objectives_supervisor_score' => 'decimal:2',
        'competencies_self_score' => 'decimal:2',
        'competencies_supervisor_score' => 'decimal:2',
        'final_self_score' => 'decimal:2',
        'final_supervisor_score' => 'decimal:2',
        'final_average_score' => 'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    /**
     * Relación con las sesiones de retroalimentación
     */
    public function feedbackSessions()
    {
        return $this->hasMany(FeedbackSession::class);
    }

    /**
     * Verificar si tiene sesiones de retroalimentación programadas
     */
    public function hasFeedbackSessions()
    {
        return $this->feedbackSessions()->exists();
    }

    /**
     * Obtener la sesión de retroalimentación más reciente
     */
    public function getLatestFeedbackSession()
    {
        return $this->feedbackSessions()->latest('scheduled_datetime')->first();
    }

    /**
     * Obtener las preguntas de objetivos del cargo
     */
    public static function getObjectivesQuestions(): array
    {
        return [
            'fortalecimiento_institucional' => [
                'weight' => 0.75,
                'title' => 'Fortalecimiento Institucional',
                'questions' => [
                    'contribuye_mision_vision' => 'Contribuye al cumplimiento de la misión y visión institucional',
                    'conoce_politicas' => 'Conoce y aplica las políticas y procedimientos institucionales',
                    'participa_mejoras' => 'Participa activamente en iniciativas de mejora institucional',
                    'promueve_valores' => 'Promueve los valores y principios organizacionales'
                ]
            ],
            'vocacion_servicio' => [
                'weight' => 0.20,
                'title' => 'Vocación de Servicio',
                'questions' => [
                    'atencion_usuarios' => 'Brinda atención de calidad a usuarios internos y externos',
                    'compromiso_servicio' => 'Demuestra compromiso con el servicio educativo',
                    'amabilidad_trato' => 'Mantiene un trato amable y respetuoso'
                ]
            ],
            'conocimiento' => [
                'weight' => 0.05,
                'title' => 'Conocimiento',
                'questions' => [
                    'dominio_cargo' => 'Demuestra dominio de las funciones de su cargo',
                    'actualizacion_conocimientos' => 'Se mantiene actualizado en su área de conocimiento'
                ]
            ]
        ];
    }

    /**
     * Obtener las competencias organizacionales
     */
    public static function getOrganizationalCompetencies(): array
    {
        return [
            'disposicion_cambio' => 'Disposición al cambio',
            'comunicacion' => 'Comunicación',
            'orientacion_resultados' => 'Orientación a resultados',
            'uso_eficiente_recursos' => 'Uso eficiente de recursos',
            'relacion_demas' => 'Relación con los demás',
            'liderazgo' => 'Liderazgo',
            'trabajo_equipo' => 'Trabajo en equipo',
            'gestion_documental' => 'Gestión documental',
            'negociacion_mediacion' => 'Negociación y mediación'
        ];
    }

    /**
     * Obtener las competencias técnicas
     */
    public static function getTechnicalCompetencies(): array
    {
        return [
            'conocimiento_ingles' => [
                'title' => 'Conocimiento de inglés',
                'options' => ['No aplica', 'Básico', 'Intermedio', 'Avanzado']
            ],
            'postgrados' => [
                'title' => 'Postgrados',
                'options' => ['No tiene', 'Especialización', 'Maestría', 'Doctorado']
            ],
            'formacion_adicional' => [
                'title' => 'Formación adicional',
                'type' => 'text'
            ]
        ];
    }

    /**
     * Obtener preguntas de seguridad y salud en el trabajo
     */
    public static function getSafetyHealthQuestions(): array
    {
        return [
            'cumple_normas_sst' => '¿Cumple con las normas de seguridad y salud en el trabajo?',
            'participa_capacitaciones' => '¿Participa en las capacitaciones de SST programadas?',
            'reporta_incidentes' => '¿Reporta oportunamente incidentes o condiciones inseguras?',
            'usa_epp' => '¿Utiliza correctamente los elementos de protección personal?',
            'conoce_procedimientos' => '¿Conoce los procedimientos de emergencia?'
        ];
    }

    /**
     * Calcular el puntaje final de la autoevaluación
     */
    public function calculateSelfScore(): float
    {
        $objectivesScore = $this->objectives_self_score ?? 0;
        $competenciesScore = $this->competencies_self_score ?? 0;
        
        return ($objectivesScore * 0.30) + ($competenciesScore * 0.70);
    }

    /**
     * Calcular el puntaje final del supervisor
     */
    public function calculateSupervisorScore(): float
    {
        $objectivesScore = $this->objectives_supervisor_score ?? 0;
        $competenciesScore = $this->competencies_supervisor_score ?? 0;
        
        return ($objectivesScore * 0.30) + ($competenciesScore * 0.70);
    }

    /**
     * Obtener el nivel de desempeño basado en el puntaje (escala 1-5)
     */
    public function getPerformanceLevel(float $score): string
    {
        if ($score >= 4.5) {
            return 'Supera las expectativas de desempeño';
        } elseif ($score >= 3.5) {
            return 'Buen desempeño con caracteristicas proactivas';
        } elseif ($score >= 2.5) {
            return 'Cumple con lo establecido sin proactividad';
        } elseif ($score >= 1.5) {
            return 'Aceptable';
        } else {
            return 'No cumple';
        }
    }

    /**
     * Verificar si el usuario puede realizar autoevaluación
     */
    public function canSelfEvaluate(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Verificar si el supervisor puede evaluar
     */
    public function canSupervisorEvaluate(): bool
    {
        return in_array($this->status, ['self_completed', 'supervisor_review']);
    }

    /**
     * Marcar autoevaluación como completada
     */
    public function completeSelfEvaluation(): void
    {
        $this->update([
            'status' => 'self_completed',
            'self_evaluation_completed_at' => now(),
            'final_self_score' => $this->calculateSelfScore(),
            'performance_level' => $this->getPerformanceLevel($this->calculateSelfScore())
        ]);
    }

    /**
     * Marcar evaluación del supervisor como completada
     */
    public function completeSupervisorEvaluation(): void
    {
        $supervisorScore = $this->calculateSupervisorScore();
        $averageScore = ($this->final_self_score + $supervisorScore) / 2;
        
        $this->update([
            'status' => 'completed',
            'supervisor_evaluation_completed_at' => now(),
            'final_supervisor_score' => $supervisorScore,
            'final_average_score' => $averageScore,
            'performance_level' => $this->getPerformanceLevel($averageScore)
        ]);
    }
}
