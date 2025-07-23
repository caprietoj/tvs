<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NursingSurveyResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_period',
        'timestamp',
        'dependencia',
        'experiencia_enfermeria',
        'presentacion_personal',
        'comentarios_presentacion',
        'disponibilidad_personal',
        'comentarios_disponibilidad',
        'profesionalismo',
        'comentarios_profesionalismo',
        'respuesta_efectiva',
        'comentarios_respuesta',
        'limpieza_orden',
        'comentarios_limpieza',
        'reportes_oportunos',
        'comentarios_reportes',
        'claridad_reportes',
        'uploaded_by',
        'uploaded_at'
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'uploaded_at' => 'datetime'
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Obtener estadísticas por período
     */
    public static function getStatisticsByPeriod($period = null)
    {
        $query = self::query();
        
        if ($period) {
            $query->where('survey_period', $period);
        }
        
        return $query->get();
    }

    /**
     * Calcular promedio de satisfacción general
     */
    public function calculateSatisfactionAverage()
    {
        $scores = [
            'Excelente' => 5,
            'Buena' => 4,
            'Muy buena' => 4,
            'Regular' => 3,
            'Mala' => 2,
            'Muy mala' => 1,
            'Sí' => 5,
            'No' => 1,
            'No conozco este proceso' => 0
        ];

        $fields = [
            'experiencia_enfermeria',
            'disponibilidad_personal',
            'limpieza_orden'
        ];

        $totalScore = 0;
        $validFields = 0;

        foreach ($fields as $field) {
            $value = $this->$field;
            if (isset($scores[$value]) && $scores[$value] > 0) {
                $totalScore += $scores[$value];
                $validFields++;
            }
        }

        return $validFields > 0 ? round($totalScore / $validFields, 2) : 0;
    }

    /**
     * Obtener análisis por dependencia
     */
    public static function getAnalysisByDependency($period = null)
    {
        $query = self::query();
        
        if ($period) {
            $query->where('survey_period', $period);
        }
        
        return $query->select('dependencia', DB::raw('COUNT(*) as total'))
                    ->groupBy('dependencia')
                    ->get();
    }

    /**
     * Obtener análisis de respuestas por pregunta
     */
    public static function getResponseAnalysis($field, $period = null)
    {
        $query = self::query();
        
        if ($period) {
            $query->where('survey_period', $period);
        }
        
        return $query->select($field, DB::raw('COUNT(*) as total'))
                    ->groupBy($field)
                    ->orderBy('total', 'desc')
                    ->get();
    }

    /**
     * Obtener datos para gráficos de Chart.js
     */
    public static function getChartData($period = null)
    {
        $data = [];
        
        // Análisis por dependencia
        $dependencyData = self::getAnalysisByDependency($period);
        $data['dependency'] = [
            'labels' => $dependencyData->pluck('dependencia')->toArray(),
            'data' => $dependencyData->pluck('total')->toArray()
        ];

        // Análisis de experiencia con enfermería
        $experienceData = self::getResponseAnalysisWithDependencies('experiencia_enfermeria', $period);
        $data['experience'] = $experienceData;

        // Análisis de presentación personal
        $presentationData = self::getResponseAnalysisWithDependencies('presentacion_personal', $period);
        $data['presentation'] = $presentationData;

        // Análisis de disponibilidad
        $availabilityData = self::getResponseAnalysisWithDependencies('disponibilidad_personal', $period);
        $data['availability'] = $availabilityData;

        // Análisis de profesionalismo
        $professionalismData = self::getResponseAnalysisWithDependencies('profesionalismo', $period);
        $data['professionalism'] = $professionalismData;

        // Análisis de respuesta efectiva
        $responseData = self::getResponseAnalysisWithDependencies('respuesta_efectiva', $period);
        $data['effective_response'] = $responseData;

        // Análisis de limpieza y orden
        $cleanlinessData = self::getResponseAnalysisWithDependencies('limpieza_orden', $period);
        $data['cleanliness'] = $cleanlinessData;

        // Análisis de reportes oportunos
        $reportsData = self::getResponseAnalysisWithDependencies('reportes_oportunos', $period);
        $data['reports'] = $reportsData;

        // Análisis de claridad de reportes
        $clarityData = self::getResponseAnalysisWithDependencies('claridad_reportes', $period);
        $data['clarity'] = $clarityData;

        return $data;
    }

    /**
     * Obtener análisis de respuestas por pregunta con información de dependencias
     */
    public static function getResponseAnalysisWithDependencies($field, $period = null)
    {
        $query = self::query();
        
        if ($period) {
            $query->where('survey_period', $period);
        }
        
        $responses = $query->select($field, 'dependencia')
                          ->whereNotNull($field)
                          ->whereNotNull('dependencia')
                          ->get();
        
        // Agrupar por respuesta y calcular dependencias
        $grouped = $responses->groupBy($field);
        
        $labels = [];
        $data = [];
        $dependencies = [];
        
        foreach ($grouped as $response => $items) {
            $labels[] = $response;
            $data[] = $items->count();
            $dependencies[] = $items->pluck('dependencia')->unique()->values()->toArray();
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
            'dependencies' => $dependencies
        ];
    }

    /**
     * Obtener períodos disponibles
     */
    public static function getAvailablePeriods()
    {
        return self::select('survey_period')
                  ->distinct()
                  ->orderBy('survey_period', 'desc')
                  ->pluck('survey_period');
    }

    /**
     * Obtener datos de tendencia de satisfacción mejorados y más dicientes
     */
    public static function getTrendData()
    {
        // Obtener los últimos 6 períodos disponibles
        $periods = self::select('survey_period')
                      ->distinct()
                      ->orderBy('survey_period', 'desc')
                      ->limit(6)
                      ->pluck('survey_period')
                      ->reverse()
                      ->values();

        $labels = [];
        $satisfactionGeneral = [];
        $experienciaEnfermeria = [];
        $profesionalismo = [];
        $limpiezaOrden = [];
        $totalResponses = [];
        $trendAnalysis = [];

        $scores = [
            'Excelente' => 100,
            'Muy buena' => 85,
            'Buena' => 70,
            'Regular' => 50,
            'Mala' => 25,
            'Muy mala' => 10,
            'Sí' => 100,
            'No' => 0,
            'No conozco este proceso' => null
        ];

        foreach ($periods as $period) {
            $responses = self::where('survey_period', $period)->get();
            
            if ($responses->count() > 0) {
                // Formatear período
                $formattedPeriod = \Carbon\Carbon::createFromFormat('Y-m', $period)->format('M Y');
                $labels[] = $formattedPeriod;
                $totalResponses[] = $responses->count();

                // Calcular satisfacción general (promedio de todas las métricas clave)
                $generalSatisfaction = 0;
                $validGeneralResponses = 0;

                // Calcular satisfacción por categoría específica
                $experienciaScore = self::calculateCategoryScore($responses, 'experiencia_enfermeria', $scores);
                $profesionalismoScore = self::calculateCategoryScore($responses, 'profesionalismo', $scores);
                $limpiezaScore = self::calculateCategoryScore($responses, 'limpieza_orden', $scores);

                $experienciaEnfermeria[] = $experienciaScore;
                $profesionalismo[] = $profesionalismoScore;
                $limpiezaOrden[] = $limpiezaScore;

                // Calcular satisfacción general como promedio de categorías clave
                $categoryScores = array_filter([$experienciaScore, $profesionalismoScore, $limpiezaScore], function($score) {
                    return $score !== null;
                });
                
                $generalScore = count($categoryScores) > 0 ? round(collect($categoryScores)->sum() / count($categoryScores), 1) : 0;
                $satisfactionGeneral[] = $generalScore;
            }
        }

        // Análisis de tendencias
        $trendAnalysis = self::analyzeTrends($satisfactionGeneral, $labels);

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Satisfacción General',
                    'data' => $satisfactionGeneral,
                    'borderColor' => '#007bff',
                    'backgroundColor' => 'rgba(0, 123, 255, 0.1)',
                    'tension' => 0.4
                ],
                [
                    'label' => 'Experiencia con Enfermería',
                    'data' => $experienciaEnfermeria,
                    'borderColor' => '#28a745',
                    'backgroundColor' => 'rgba(40, 167, 69, 0.1)',
                    'tension' => 0.4
                ],
                [
                    'label' => 'Profesionalismo',
                    'data' => $profesionalismo,
                    'borderColor' => '#ffc107',
                    'backgroundColor' => 'rgba(255, 193, 7, 0.1)',
                    'tension' => 0.4
                ],
                [
                    'label' => 'Limpieza y Orden',
                    'data' => $limpiezaOrden,
                    'borderColor' => '#17a2b8',
                    'backgroundColor' => 'rgba(23, 162, 184, 0.1)',
                    'tension' => 0.4
                ]
            ],
            'totalResponses' => $totalResponses,
            'analysis' => $trendAnalysis,
            'summary' => [
                'avgSatisfaction' => count($satisfactionGeneral) > 0 ? round(collect($satisfactionGeneral)->sum() / count($satisfactionGeneral), 1) : 0,
                'maxSatisfaction' => count($satisfactionGeneral) > 0 ? max($satisfactionGeneral) : 0,
                'minSatisfaction' => count($satisfactionGeneral) > 0 ? min($satisfactionGeneral) : 0,
                'totalPeriods' => count($labels),
                'totalResponsesSum' => collect($totalResponses)->sum(),
                'avgResponsesPerPeriod' => count($totalResponses) > 0 ? round(collect($totalResponses)->sum() / count($totalResponses), 1) : 0
            ]
        ];
    }

    /**
     * Calcular puntuación para una categoría específica
     */
    private static function calculateCategoryScore($responses, $field, $scores)
    {
        $totalScore = 0;
        $validResponses = 0;

        foreach ($responses as $response) {
            $value = $response->$field;
            if (isset($scores[$value]) && $scores[$value] !== null) {
                $totalScore += $scores[$value];
                $validResponses++;
            }
        }

        return $validResponses > 0 ? round($totalScore / $validResponses, 1) : null;
    }

    /**
     * Analizar tendencias en los datos
     */
    private static function analyzeTrends($data, $labels)
    {
        if (count($data) < 2) {
            return [
                'trend' => 'insufficient_data',
                'message' => 'Datos insuficientes para análisis de tendencia',
                'change' => 0
            ];
        }

        $firstValue = $data[0];
        $lastValue = end($data);
        $change = $lastValue - $firstValue;
        $percentChange = $firstValue > 0 ? round(($change / $firstValue) * 100, 1) : 0;

        // Calcular tendencia general
        $trend = 'stable';
        $message = '';

        if ($change > 5) {
            $trend = 'improving';
            $message = "Tendencia positiva: +{$percentChange}% desde " . $labels[0];
        } elseif ($change < -5) {
            $trend = 'declining';
            $message = "Tendencia negativa: {$percentChange}% desde " . $labels[0];
        } else {
            $trend = 'stable';
            $message = "Tendencia estable: {$percentChange}% desde " . $labels[0];
        }

        // Identificar el mejor y peor período
        $maxIndex = array_search(max($data), $data);
        $minIndex = array_search(min($data), $data);

        return [
            'trend' => $trend,
            'message' => $message,
            'change' => $change,
            'percentChange' => $percentChange,
            'bestPeriod' => [
                'period' => $labels[$maxIndex],
                'score' => $data[$maxIndex]
            ],
            'worstPeriod' => [
                'period' => $labels[$minIndex],
                'score' => $data[$minIndex]
            ]
        ];
    }
}
