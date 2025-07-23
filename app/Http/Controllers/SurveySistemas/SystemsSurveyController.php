<?php

namespace App\Http\Controllers\SurveySistemas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SurveyImport;
use App\Models\SystemsSurveyResult;
use App\Exports\SystemsSurveyExport;
use Carbon\Carbon;

class SystemsSurveyController extends Controller
{
    public function index()
    {
        try {
            // Obtener estadísticas generales del último período
            $latestData = $this->getLatestStatistics();
            
            // Obtener datos para el dashboard
            $dashboardData = $this->getDashboardData();
            
            // Crear selectedPeriod en formato numérico
            $selectedPeriod = 'N/A';
            if (!empty($latestData) && isset($latestData['last_period']) && $latestData['last_period'] !== 'N/A') {
                // Buscar el último período con datos
                $lastPeriod = SystemsSurveyResult::orderBy('survey_year', 'desc')
                    ->orderBy('survey_month', 'desc')
                    ->first();
                    
                if ($lastPeriod) {
                    $selectedPeriod = $lastPeriod->survey_year . '-' . str_pad($lastPeriod->survey_month, 2, '0', STR_PAD_LEFT);
                }
            }
            
            // Verificar que los datos existen
            if (empty($latestData) || empty($dashboardData)) {
                // Datos por defecto si no hay información
                $latestData = [
                    'total_responses' => 0,
                    'average_satisfaction' => 0,
                    'last_period' => 'N/A',
                    'category_stats' => [],
                    'departments' => []
                ];
                
                $dashboardData = [
                    'trend_data' => ['labels' => [], 'values' => []],
                    'category_comparison' => ['labels' => [], 'values' => []],
                    'department_comparison' => ['labels' => [], 'values' => []],
                    'top_issues' => [],
                    'top_highlights' => [],
                    'total_responses' => 0,
                    'average_satisfaction' => 0,
                    'total_categories' => 5,
                    'total_departments' => 0
                ];
            }
            
            return view('surveys.internal-client.systems.index', compact('latestData', 'dashboardData', 'selectedPeriod'));
            
        } catch (\Exception $e) {
            // Log del error para debugging
            \Log::error('Error en SystemsSurveyController@index: ' . $e->getMessage());
            
            // Datos por defecto en caso de error
            $latestData = [
                'total_responses' => 0,
                'average_satisfaction' => 0,
                'last_period' => 'N/A',
                'category_stats' => [],
                'departments' => []
            ];
            
            $dashboardData = [
                'trend_data' => ['labels' => [], 'values' => []],
                'category_comparison' => ['labels' => [], 'values' => []],
                'department_comparison' => ['labels' => [], 'values' => []],
                'top_issues' => [],
                'top_highlights' => [],
                'total_responses' => 0,
                'average_satisfaction' => 0,
                'total_categories' => 5,
                'total_departments' => 0
            ];
            
            $selectedPeriod = 'N/A';
            
            return view('surveys.internal-client.systems.index', compact('latestData', 'dashboardData', 'selectedPeriod'));
        }
    }

    private function getLatestStatistics()
    {
        try {
            // Obtener el último período con datos
            $lastPeriod = SystemsSurveyResult::orderBy('survey_year', 'desc')
                ->orderBy('survey_month', 'desc')
                ->first();

            if (!$lastPeriod) {
                return [
                    'total_responses' => 0,
                    'average_satisfaction' => 0,
                    'last_period' => 'N/A',
                    'category_stats' => [],
                    'departments' => []
                ];
            }

            // Obtener todas las respuestas del último período
            $results = SystemsSurveyResult::where('survey_year', $lastPeriod->survey_year)
                ->where('survey_month', $lastPeriod->survey_month)
                ->get();

            // Calcular estadísticas
            $totalResponses = $results->count();
            $avgSatisfaction = $this->calculateAverageSatisfaction($results);
            $categoryStats = $this->getCategoryComparisonData();
            $departmentDist = $this->getDepartmentDistribution($results);

            return [
                'total_responses' => $totalResponses,
                'average_satisfaction' => $avgSatisfaction,
                'last_period' => $lastPeriod->getMonthName() . ' ' . $lastPeriod->survey_year,
                'category_stats' => $categoryStats,
                'departments' => $departmentDist
            ];
        } catch (\Exception $e) {
            Log::error('Error en getLatestStatistics: ' . $e->getMessage());
            return [
                'total_responses' => 0,
                'average_satisfaction' => 0,
                'last_period' => 'N/A',
                'category_stats' => [],
                'departments' => []
            ];
        }
    }

    private function getDashboardData()
    {
        try {
            // Obtener datos para gráficos del dashboard
            $trendData = $this->getOverallTrendData();
            $categoryComparison = $this->getCategoryComparisonData();
            $topHighlightsData = $this->getTopHighlightsData();
            $topIssuesData = $this->getTopIssuesData();
            
            // Obtener datos estadísticos adicionales
            $totalResponses = SystemsSurveyResult::count();
            $avgSatisfaction = $this->calculateAverageSatisfaction(SystemsSurveyResult::all());
            $totalDepartments = SystemsSurveyResult::distinct('dependencia')->count();

            return [
                'trend_data' => $trendData ?: ['labels' => [], 'values' => []],
                'category_comparison' => $categoryComparison ?: ['labels' => [], 'values' => []],
                'department_comparison' => $categoryComparison ?: ['labels' => [], 'values' => []],
                'top_issues' => $topIssuesData ? $topIssuesData->toArray() : [],
                'top_highlights' => $topHighlightsData ? $topHighlightsData->toArray() : [],
                'total_responses' => $totalResponses,
                'average_satisfaction' => $avgSatisfaction,
                'total_categories' => 5, // Número fijo de categorías que manejamos
                'total_departments' => $totalDepartments
            ];
        } catch (\Exception $e) {
            return [
                'trend_data' => ['labels' => [], 'values' => []],
                'category_comparison' => ['labels' => [], 'values' => []],
                'department_comparison' => ['labels' => [], 'values' => []],
                'top_issues' => [],
                'top_highlights' => [],
                'total_responses' => 0,
                'average_satisfaction' => 0,
                'total_categories' => 5,
                'total_departments' => 0
            ];
        }
    }

    private function getCategoryComparisonData()
    {
        $categories = [
            'tiempos_respuesta' => ['label' => 'Tiempos de Respuesta', 'icon' => 'fas fa-clock', 'color' => 'primary'],
            'estado_equipos' => ['label' => 'Estado de Equipos', 'icon' => 'fas fa-laptop', 'color' => 'warning'],
            'apoyo_usabilidad' => ['label' => 'Apoyo en Usabilidad', 'icon' => 'fas fa-graduation-cap', 'color' => 'secondary'],
            'calidad_internet' => ['label' => 'Calidad Internet', 'icon' => 'fas fa-wifi', 'color' => 'info'],
            'intervencion_eventos' => ['label' => 'Intervención en Eventos', 'icon' => 'fas fa-calendar', 'color' => 'success']
        ];

        $scaleMapping = [
            'Excelente' => 5, 'Bueno' => 4, 'Regular' => 3, 'Malo' => 2, 'Deficiente' => 1
        ];

        try {
            $results = SystemsSurveyResult::all();
            $categoryStats = [];

            foreach ($categories as $field => $info) {
                $values = [];
                $distribution = [];

                foreach ($results as $result) {
                    $value = $result->$field;
                if (isset($scaleMapping[$value])) {
                    $values[] = $scaleMapping[$value];
                }
                
                if (!isset($distribution[$value])) {
                    $distribution[$value] = 0;
                }
                $distribution[$value]++;
            }

            if (!empty($values)) {
                $categoryStats[$field] = [
                    'label' => $info['label'],
                    'icon' => $info['icon'],
                    'color' => $info['color'],
                    'promedio' => round(array_sum($values) / count($values), 2),
                    'porcentaje' => round((array_sum($values) / count($values)) * 20, 1),
                    'total_respuestas' => count($values),
                    'distribucion' => $distribution
                ];
            }
        }

        return [
            'labels' => array_column($categoryStats, 'label'),
            'values' => array_column($categoryStats, 'porcentaje'),
            'details' => $categoryStats
        ];
        
        } catch (\Exception $e) {
            return ['labels' => [], 'values' => [], 'details' => []];
        }
    }

    private function getDepartmentDistribution($results)
    {
        $distribution = [];
        foreach ($results as $result) {
            $dep = $result->dependencia;
            if (!isset($distribution[$dep])) {
                $distribution[$dep] = 0;
            }
            $distribution[$dep]++;
        }
        return $distribution;
    }

    private function getOverallTrendData()
    {
        $results = SystemsSurveyResult::selectRaw('survey_year, survey_month, COUNT(*) as count')
            ->groupBy('survey_year', 'survey_month')
            ->orderBy('survey_year', 'desc')
            ->orderBy('survey_month', 'desc')
            ->limit(6)
            ->get()
            ->reverse();

        $labels = [];
        $values = [];

        foreach ($results as $result) {
            $monthName = [
                1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
                7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
            ][$result->survey_month];

            $labels[] = $monthName . ' ' . $result->survey_year;
            
            $periodResults = SystemsSurveyResult::where('survey_year', $result->survey_year)
                ->where('survey_month', $result->survey_month)
                ->get();
            
            $values[] = $this->calculateAverageSatisfaction($periodResults);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function getTopIssuesData()
    {
        return SystemsSurveyResult::selectRaw('oportunidades_mejora, COUNT(*) as count')
            ->whereNotNull('oportunidades_mejora')
            ->where('oportunidades_mejora', '!=', '')
            ->where('oportunidades_mejora', '!=', 'N/A')
            ->where('oportunidades_mejora', '!=', 'Ninguna')
            ->groupBy('oportunidades_mejora')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();
    }

    private function getTopHighlightsData()
    {
        return SystemsSurveyResult::selectRaw('aspectos_destacados, COUNT(*) as count')
            ->whereNotNull('aspectos_destacados')
            ->where('aspectos_destacados', '!=', '')
            ->where('aspectos_destacados', '!=', 'N/A')
            ->groupBy('aspectos_destacados')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();
    }

    public function upload()
    {
        return view('surveys.internal-client.systems.upload');
    }

    public function processUpload(Request $request)
    {
        try {
            // Validar la entrada
            $validated = $request->validate([
                'survey_file' => 'required|mimes:xlsx,xls',
                'survey_year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
                'survey_month' => 'required|integer|min:1|max:12'
            ]);

            $file = $request->file('survey_file');
            $year = $request->input('survey_year');
            $month = $request->input('survey_month');
            
            Log::info('Procesando archivo de sistemas', [
                'year' => $year,
                'month' => $month,
                'filename' => $file->getClientOriginalName()
            ]);
            
            // Verificar que el archivo se subió correctamente
            if (!$file || !$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo no es válido'
                ], 422);
            }
            
            $data = Excel::toArray([], $file);
            
            // Obtener la primera hoja
            $sheetData = $data[0] ?? [];
            
            // Verificar que hay datos
            if (count($sheetData) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo no contiene suficientes datos'
                ], 422);
            }
            
            Log::info('Archivo leído correctamente', ['rows' => count($sheetData)]);
            
            // Procesar datos
            $processedData = $this->processSurveyData($sheetData, $year, $month);
            
            Log::info('Datos procesados', ['count' => count($processedData)]);
            
            // Guardar en base de datos
            $this->saveSurveyData($processedData, $year, $month);
            
            Log::info('Datos guardados en base de datos');
            
            // Generar análisis estadístico
            $analysis = $this->generateStatisticalAnalysis($processedData);
            
            Log::info('Análisis generado');
            
            return response()->json([
                'success' => true,
                'message' => 'Archivo procesado exitosamente',
                'data' => $processedData,
                'analysis' => $analysis
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación en sistemas', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . implode(', ', array_map(function($field_errors) {
                    return implode(', ', $field_errors);
                }, $e->errors()))
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error procesando encuesta de sistemas', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    private function processSurveyData($data, $year, $month)
    {
        $processed = [];
        $headers = $data[0];
        
        // Mapear las columnas según la estructura proporcionada
        $columnMapping = [
            'timestamp' => 0,
            'dependencia' => 1,
            'tiempos_respuesta' => 2,
            'efectividad_tecnica' => 3,
            'profesionalismo' => 4,
            'comentarios_personal' => 5,
            'estado_equipos' => 6,
            'comentarios_equipos' => 7,
            'apoyo_usabilidad' => 8,
            'plataformas_interaccion' => 9,
            'otra_plataforma' => 10,
            'calidad_internet' => 11,
            'problemas_conectividad' => 12,
            'intervencion_eventos' => 13,
            'comentarios_eventos' => 14,
            'aspectos_destacados' => 15,
            'oportunidades_mejora' => 16
        ];
        
        // Procesar cada fila de datos (saltando el header)
        for ($i = 1; $i < count($data); $i++) {
            $row = $data[$i];
            
            if (empty($row[0])) continue; // Saltar filas vacías
            
            $processed[] = [
                'id' => $i,
                'timestamp' => $this->parseTimestamp($row[0]),
                'dependencia' => $row[1] ?? '',
                'tiempos_respuesta' => $row[2] ?? '',
                'efectividad_tecnica' => $row[3] ?? '',
                'profesionalismo' => $row[4] ?? '',
                'comentarios_personal' => $row[5] ?? '',
                'estado_equipos' => $row[6] ?? '',
                'comentarios_equipos' => $row[7] ?? '',
                'apoyo_usabilidad' => $row[8] ?? '',
                'plataformas_interaccion' => $row[9] ?? '',
                'otra_plataforma' => $row[10] ?? '',
                'calidad_internet' => $row[11] ?? '',
                'problemas_conectividad' => $row[12] ?? '',
                'intervencion_eventos' => $row[13] ?? '',
                'comentarios_eventos' => $row[14] ?? '',
                'aspectos_destacados' => $row[15] ?? '',
                'oportunidades_mejora' => $row[16] ?? '',
                'year' => $year,
                'month' => $month
            ];
        }
        
        return $processed;
    }

    private function saveSurveyData($data, $year, $month)
    {
        // Eliminar datos existentes para el mismo año y mes
        SystemsSurveyResult::where('survey_year', $year)
            ->where('survey_month', $month)
            ->delete();

        // Guardar nuevos datos
        foreach ($data as $response) {
            SystemsSurveyResult::create([
                'response_timestamp' => $response['timestamp'],
                'dependencia' => $response['dependencia'],
                'tiempos_respuesta' => $response['tiempos_respuesta'],
                'efectividad_tecnica' => $response['efectividad_tecnica'],
                'profesionalismo' => $response['profesionalismo'],
                'comentarios_personal' => $response['comentarios_personal'],
                'estado_equipos' => $response['estado_equipos'],
                'comentarios_equipos' => $response['comentarios_equipos'],
                'apoyo_usabilidad' => $response['apoyo_usabilidad'],
                'plataformas_interaccion' => $response['plataformas_interaccion'],
                'otra_plataforma' => $response['otra_plataforma'],
                'calidad_internet' => $response['calidad_internet'],
                'problemas_conectividad' => $response['problemas_conectividad'],
                'intervencion_eventos' => $response['intervencion_eventos'],
                'comentarios_eventos' => $response['comentarios_eventos'],
                'aspectos_destacados' => $response['aspectos_destacados'],
                'oportunidades_mejora' => $response['oportunidades_mejora'],
                'survey_year' => $year,
                'survey_month' => $month
            ]);
        }
    }

    private function parseTimestamp($timestamp)
    {
        try {
            // Si es una fecha en formato string
            if (is_string($timestamp)) {
                return Carbon::parse($timestamp)->format('Y-m-d H:i:s');
            }
            
            // Si es un número o timestamp
            if (is_numeric($timestamp)) {
                return Carbon::createFromTimestamp($timestamp)->format('Y-m-d H:i:s');
            }
            
            return $timestamp;
        } catch (\Exception $e) {
            return $timestamp;
        }
    }

    private function generateStatisticalAnalysis($data)
    {
        $analysis = [
            'total_respuestas' => count($data),
            'dependencias' => [],
            'satisfaccion_general' => [],
            'tiempos_respuesta' => [],
            'efectividad_tecnica' => [],
            'profesionalismo' => [],
            'estado_equipos' => [],
            'apoyo_usabilidad' => [],
            'calidad_internet' => [],
            'intervencion_eventos' => [],
            'plataformas_mas_usadas' => [],
            'problemas_conectividad_frecuentes' => [],
            'aspectos_destacados_frecuentes' => [],
            'oportunidades_mejora_frecuentes' => [],
            'comentarios_positivos' => [],
            'comentarios_negativos' => []
        ];

        // Contadores para cada categoría
        $scaleMapping = [
            'Excelente' => 5,
            'Muy efectiva' => 5,
            'Buena' => 4,
            'Bueno' => 4,
            'Efectiva' => 4,
            'Regular' => 3,
            'Malo' => 2,
            'Deficiente' => 1
        ];

        foreach ($data as $response) {
            // Análisis por dependencia
            $dependencia = $response['dependencia'];
            if (!isset($analysis['dependencias'][$dependencia])) {
                $analysis['dependencias'][$dependencia] = 0;
            }
            $analysis['dependencias'][$dependencia]++;

            // Análisis de satisfacción por categoría
            $categories = [
                'tiempos_respuesta' => $response['tiempos_respuesta'],
                'efectividad_tecnica' => $response['efectividad_tecnica'],
                'profesionalismo' => $response['profesionalismo'],
                'estado_equipos' => $response['estado_equipos'],
                'apoyo_usabilidad' => $response['apoyo_usabilidad'],
                'calidad_internet' => $response['calidad_internet'],
                'intervencion_eventos' => $response['intervencion_eventos']
            ];

            foreach ($categories as $category => $value) {
                if (!isset($analysis[$category])) {
                    $analysis[$category] = [];
                }
                if (!isset($analysis[$category][$value])) {
                    $analysis[$category][$value] = 0;
                }
                $analysis[$category][$value]++;
            }

            // Análisis de plataformas
            $plataformas = explode(',', $response['plataformas_interaccion']);
            foreach ($plataformas as $plataforma) {
                $plataforma = trim($plataforma);
                if (!empty($plataforma)) {
                    if (!isset($analysis['plataformas_mas_usadas'][$plataforma])) {
                        $analysis['plataformas_mas_usadas'][$plataforma] = 0;
                    }
                    $analysis['plataformas_mas_usadas'][$plataforma]++;
                }
            }

            // Análisis de problemas de conectividad
            $problemas = $response['problemas_conectividad'];
            if (!empty($problemas)) {
                if (!isset($analysis['problemas_conectividad_frecuentes'][$problemas])) {
                    $analysis['problemas_conectividad_frecuentes'][$problemas] = 0;
                }
                $analysis['problemas_conectividad_frecuentes'][$problemas]++;
            }

            // Análisis de aspectos destacados
            $aspectos = $response['aspectos_destacados'];
            if (!empty($aspectos)) {
                if (!isset($analysis['aspectos_destacados_frecuentes'][$aspectos])) {
                    $analysis['aspectos_destacados_frecuentes'][$aspectos] = 0;
                }
                $analysis['aspectos_destacados_frecuentes'][$aspectos]++;
            }

            // Análisis de oportunidades de mejora
            $oportunidades = $response['oportunidades_mejora'];
            if (!empty($oportunidades)) {
                if (!isset($analysis['oportunidades_mejora_frecuentes'][$oportunidades])) {
                    $analysis['oportunidades_mejora_frecuentes'][$oportunidades] = 0;
                }
                $analysis['oportunidades_mejora_frecuentes'][$oportunidades]++;
            }
        }

        // Calcular porcentajes y estadísticas
        $analysis['estadisticas'] = $this->calculateStatistics($data, $scaleMapping);
        
        // Ordenar arrays por frecuencia
        arsort($analysis['plataformas_mas_usadas']);
        arsort($analysis['problemas_conectividad_frecuentes']);
        arsort($analysis['aspectos_destacados_frecuentes']);
        arsort($analysis['oportunidades_mejora_frecuentes']);

        return $analysis;
    }

    private function calculateStatistics($data, $scaleMapping)
    {
        $stats = [];
        $categories = [
            'tiempos_respuesta' => 'Tiempos de Respuesta',
            'efectividad_tecnica' => 'Efectividad Técnica',
            'profesionalismo' => 'Profesionalismo',
            'estado_equipos' => 'Estado de Equipos',
            'apoyo_usabilidad' => 'Apoyo en Usabilidad',
            'calidad_internet' => 'Calidad Internet',
            'intervencion_eventos' => 'Intervención en Eventos'
        ];

        foreach ($categories as $field => $label) {
            $values = [];
            foreach ($data as $response) {
                $value = $response[$field];
                if (isset($scaleMapping[$value])) {
                    $values[] = $scaleMapping[$value];
                }
            }

            if (!empty($values)) {
                $stats[$field] = [
                    'label' => $label,
                    'promedio' => round(array_sum($values) / count($values), 2),
                    'porcentaje' => round((array_sum($values) / count($values)) * 20, 2),
                    'total_respuestas' => count($values),
                    'distribucion' => array_count_values($values)
                ];
            }
        }

        // Satisfacción general
        $allValues = [];
        foreach ($categories as $field => $label) {
            foreach ($data as $response) {
                $value = $response[$field];
                if (isset($scaleMapping[$value])) {
                    $allValues[] = $scaleMapping[$value];
                }
            }
        }

        if (!empty($allValues)) {
            $stats['satisfaccion_general'] = [
                'promedio' => round(array_sum($allValues) / count($allValues), 2),
                'porcentaje' => round((array_sum($allValues) / count($allValues)) * 20, 2),
                'total_evaluaciones' => count($allValues)
            ];
        }

        return $stats;
    }

    public function results(Request $request)
    {
        $query = SystemsSurveyResult::query();

        // Aplicar filtros
        if ($request->has('year') && $request->year) {
            $query->where('survey_year', $request->year);
        }
        if ($request->has('month') && $request->month) {
            $query->where('survey_month', $request->month);
        }
        if ($request->has('department') && $request->department) {
            $query->where('dependencia', $request->department);
        }

        // Obtener resultados con paginación
        $page = $request->get('page', 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        $allResults = $query->orderBy('dependencia', 'asc')->get();
        $results = $allResults->slice($offset, $perPage);

        // Obtener datos para filtros
        $years = SystemsSurveyResult::distinct()->pluck('survey_year')->sort()->values();
        $departments = SystemsSurveyResult::distinct()->pluck('dependencia')->sort()->values();

        // Calcular estadísticas
        $totalResponses = $allResults->count();
        $avgSatisfaction = $this->calculateAverageSatisfaction($allResults);
        $totalDepartments = $departments->count();
        $lastPeriod = $this->getLastPeriod();

        // Datos para gráficos  
        $trendData = $this->getTrendData($request);
        $categoryData = $this->getCategoryData($allResults);

        return view('surveys.internal-client.systems.results', compact(
            'results', 'allResults', 'years', 'departments', 'totalResponses', 
            'avgSatisfaction', 'totalDepartments', 'lastPeriod',
            'trendData', 'categoryData'
        ));
    }

    public function details($id)
    {
        $result = SystemsSurveyResult::findOrFail($id);
        
        return view('surveys.internal-client.systems.details', compact('result'));
    }

    public function export(Request $request)
    {
        $query = SystemsSurveyResult::query();

        // Aplicar filtros
        if ($request->has('year') && $request->year) {
            $query->where('survey_year', $request->year);
        }
        if ($request->has('month') && $request->month) {
            $query->where('survey_month', $request->month);
        }
        if ($request->has('department') && $request->department) {
            $query->where('dependencia', $request->department);
        }

        $results = $query->orderBy('response_timestamp', 'desc')->get();

        return Excel::download(new SystemsSurveyExport($results), 'encuesta-sistemas.xlsx');
    }

    private function calculateAverageSatisfaction($results)
    {
        if ($results->count() === 0) {
            return 0;
        }

        $scaleMapping = [
            'Excelente' => 5,
            'Muy efectiva' => 5,
            'Buena' => 4,
            'Bueno' => 4,
            'Efectiva' => 4,
            'Regular' => 3,
            'Malo' => 2,
            'Deficiente' => 1
        ];

        $totalScore = 0;
        $totalQuestions = 0;

        foreach ($results as $result) {
            $fields = [
                'tiempos_respuesta', 'efectividad_tecnica', 'profesionalismo',
                'estado_equipos', 'apoyo_usabilidad', 'calidad_internet',
                'intervencion_eventos'
            ];

            foreach ($fields as $field) {
                if (isset($scaleMapping[$result->$field])) {
                    $totalScore += $scaleMapping[$result->$field];
                    $totalQuestions++;
                }
            }
        }

        return $totalQuestions > 0 ? round(($totalScore / $totalQuestions) * 20, 1) : 0;
    }

    private function getLastPeriod()
    {
        $lastResult = SystemsSurveyResult::orderBy('survey_year', 'desc')
            ->orderBy('survey_month', 'desc')
            ->first();

        if ($lastResult) {
            return $lastResult->getMonthName() . ' ' . $lastResult->survey_year;
        }

        return 'N/A';
    }

    private function getTrendData($request)
    {
        $query = SystemsSurveyResult::query();
        
        if ($request->has('department') && $request->department) {
            $query->where('dependencia', $request->department);
        }

        $results = $query->selectRaw('survey_year, survey_month, COUNT(*) as count')
            ->groupBy('survey_year', 'survey_month')
            ->orderBy('survey_year')
            ->orderBy('survey_month')
            ->get();

        $labels = [];
        $values = [];

        foreach ($results as $result) {
            $monthName = [
                1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
                5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
                9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
            ][$result->survey_month];

            $labels[] = $monthName . ' ' . $result->survey_year;
            
            // Calcular satisfacción para este período
            $periodResults = SystemsSurveyResult::where('survey_year', $result->survey_year)
                ->where('survey_month', $result->survey_month);
            
            if ($request->has('department') && $request->department) {
                $periodResults->where('dependencia', $request->department);
            }
            
            $values[] = $this->calculateAverageSatisfaction($periodResults->get());
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    private function getCategoryData($results)
    {
        $categories = [
            'tiempos_respuesta' => 'Tiempos de Respuesta',
            'efectividad_tecnica' => 'Efectividad Técnica',
            'profesionalismo' => 'Profesionalismo',
            'estado_equipos' => 'Estado de Equipos',
            'apoyo_usabilidad' => 'Apoyo en Usabilidad',
            'calidad_internet' => 'Calidad Internet',
            'intervencion_eventos' => 'Intervención en Eventos'
        ];

        $scaleMapping = [
            'Excelente' => 5,
            'Muy efectiva' => 5,
            'Buena' => 4,
            'Bueno' => 4,
            'Efectiva' => 4,
            'Regular' => 3,
            'Malo' => 2,
            'Deficiente' => 1
        ];

        $labels = [];
        $values = [];

        foreach ($categories as $field => $label) {
            $totalScore = 0;
            $count = 0;

            foreach ($results as $result) {
                if (isset($scaleMapping[$result->$field])) {
                    $totalScore += $scaleMapping[$result->$field];
                    $count++;
                }
            }

            $labels[] = $label;
            $values[] = $count > 0 ? round(($totalScore / $count) * 20, 1) : 0;
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }
}
