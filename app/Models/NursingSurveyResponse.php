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
        $experienceData = self::getResponseAnalysis('experiencia_enfermeria', $period);
        $data['experience'] = [
            'labels' => $experienceData->pluck('experiencia_enfermeria')->toArray(),
            'data' => $experienceData->pluck('total')->toArray()
        ];

        // Análisis de presentación personal
        $presentationData = self::getResponseAnalysis('presentacion_personal', $period);
        $data['presentation'] = [
            'labels' => $presentationData->pluck('presentacion_personal')->toArray(),
            'data' => $presentationData->pluck('total')->toArray()
        ];

        // Análisis de disponibilidad
        $availabilityData = self::getResponseAnalysis('disponibilidad_personal', $period);
        $data['availability'] = [
            'labels' => $availabilityData->pluck('disponibilidad_personal')->toArray(),
            'data' => $availabilityData->pluck('total')->toArray()
        ];

        // Análisis de profesionalismo
        $professionalismData = self::getResponseAnalysis('profesionalismo', $period);
        $data['professionalism'] = [
            'labels' => $professionalismData->pluck('profesionalismo')->toArray(),
            'data' => $professionalismData->pluck('total')->toArray()
        ];

        // Análisis de respuesta efectiva
        $responseData = self::getResponseAnalysis('respuesta_efectiva', $period);
        $data['effective_response'] = [
            'labels' => $responseData->pluck('respuesta_efectiva')->toArray(),
            'data' => $responseData->pluck('total')->toArray()
        ];

        // Análisis de limpieza y orden
        $cleanlinessData = self::getResponseAnalysis('limpieza_orden', $period);
        $data['cleanliness'] = [
            'labels' => $cleanlinessData->pluck('limpieza_orden')->toArray(),
            'data' => $cleanlinessData->pluck('total')->toArray()
        ];

        // Análisis de reportes oportunos
        $reportsData = self::getResponseAnalysis('reportes_oportunos', $period);
        $data['reports'] = [
            'labels' => $reportsData->pluck('reportes_oportunos')->toArray(),
            'data' => $reportsData->pluck('total')->toArray()
        ];

        // Análisis de claridad de reportes
        $clarityData = self::getResponseAnalysis('claridad_reportes', $period);
        $data['clarity'] = [
            'labels' => $clarityData->pluck('claridad_reportes')->toArray(),
            'data' => $clarityData->pluck('total')->toArray()
        ];

        return $data;
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
}
