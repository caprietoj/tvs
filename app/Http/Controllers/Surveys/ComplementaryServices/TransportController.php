<?php

namespace App\Http\Controllers\Surveys\ComplementaryServices;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class TransportController extends Controller
{
    public function index()
    {
        $surveys = DB::table('complementary_services_surveys')
            ->select('period', 'year', 'month', 'created_at')
            ->groupBy(['period', 'year', 'month', 'created_at'])
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $periods = $surveys->map(function ($survey) {
            try {
                return [
                    'id' => $survey->period,
                    'label' => $survey->period, // Usar el período directamente para evitar errores de formato
                    'year' => $survey->year,
                    'month' => $survey->month,
                    'date' => $survey->created_at
                ];
            } catch (\Exception $e) {
                return [
                    'id' => $survey->period,
                    'label' => $survey->period,
                    'year' => $survey->year,
                    'month' => $survey->month,
                    'date' => $survey->created_at
                ];
            }
        })->unique('id');

        $dashboardData = $this->getDashboardData();
        
        return view('surveys.complementary-services.transport.index', compact('periods', 'dashboardData'));
    }

    public function upload()
    {
        return view('surveys.complementary-services.transport.upload');
    }

    public function processUpload(Request $request)
    {
        $request->validate([
            'survey_file' => 'required|mimes:xlsx,xls|max:10240',
            'year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            'month' => 'required|string|in:01,02,03,04,05,06,07,08,09,10,11,12',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            $file = $request->file('survey_file');
            $year = (int) $request->year;
            $month = (int) $request->month;
            $period = $request->year . '-' . $request->month;
            $description = $request->description ?? '';
            
            $result = $this->processSingleFile($file, $period, $year, $month, $description);
            
            // Si es una petición AJAX, devolver JSON
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => $result
                ]);
            }
            
            // Respuesta tradicional para formularios normales
            return redirect()->route('surveys.complementary-services.transport.index')
                ->with('success', $result['message']);
                
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al procesar el archivo: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    public function processMultipleUpload(Request $request)
    {
        $request->validate([
            'files' => 'required|array|max:5',
            'files.*.file' => 'required|mimes:xlsx,xls|max:10240',
            'files.*.year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            'files.*.month' => 'required|string|in:01,02,03,04,05,06,07,08,09,10,11,12',
            'files.*.description' => 'nullable|string|max:255'
        ]);

        $results = [];
        $successCount = 0;
        $errorCount = 0;

        try {
            foreach ($request->files as $index => $fileData) {
                try {
                    $file = $fileData['file'];
                    $year = (int) $fileData['year'];
                    $month = (int) $fileData['month'];
                    $period = $fileData['year'] . '-' . $fileData['month'];
                    $description = $fileData['description'] ?? '';
                    
                    $result = $this->processSingleFile($file, $period, $year, $month, $description);
                    $results[] = [
                        'file' => $file->getClientOriginalName(),
                        'success' => true,
                        'message' => $result['message'],
                        'period' => $period,
                        'data' => $result
                    ];
                    $successCount++;
                    
                } catch (\Exception $e) {
                    $results[] = [
                        'file' => $fileData['file']->getClientOriginalName(),
                        'success' => false,
                        'message' => 'Error: ' . $e->getMessage(),
                        'period' => ($fileData['year'] ?? '') . '-' . ($fileData['month'] ?? '')
                    ];
                    $errorCount++;
                }
            }

            $overallMessage = "Procesamiento completado. {$successCount} archivos cargados exitosamente";
            if ($errorCount > 0) {
                $overallMessage .= ", {$errorCount} archivos con errores";
            }

            return response()->json([
                'success' => $successCount > 0,
                'message' => $overallMessage,
                'results' => $results,
                'summary' => [
                    'total' => count($request->files),
                    'success' => $successCount,
                    'errors' => $errorCount
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error general en el procesamiento: ' . $e->getMessage(),
                'results' => $results
            ], 500);
        }
    }

    private function processSingleFile($file, $period, $year, $month, $description = '')
    {
        // Load Excel file
        $spreadsheet = IOFactory::load($file->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        // Skip header row
        $headers = array_shift($rows);
        
        // Clean and process data
        $processedData = [];
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($rows as $rowIndex => $row) {
            if (empty(array_filter($row))) continue; // Skip empty rows
            
            $processedRow = $this->processRow($row, $headers, $period, $year, $month);
            if ($processedRow) {
                $processedData[] = $processedRow;
                $successCount++;
            } else {
                $errorCount++;
            }
        }
        
        // Delete existing data for this period
        DB::table('complementary_services_surveys')->where('period', $period)->delete();
        
        // Insert new data
        if (!empty($processedData)) {
            DB::table('complementary_services_surveys')->insert($processedData);
        }
        
        $message = "Encuesta cargada exitosamente para {$period}. ";
        $message .= "Se procesaron {$successCount} respuestas correctamente.";
        if ($errorCount > 0) {
            $message .= " {$errorCount} filas tuvieron errores y fueron omitidas.";
        }
        
        return [
            'message' => $message,
            'period' => $period,
            'description' => $description,
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'total_rows' => count($rows)
        ];
    }

    private function processRow($row, $headers, $period, $year, $month)
    {
        try {
            $data = [
                'period' => $period,
                'year' => $year,
                'month' => $month,
                'timestamp' => $this->parseDate($row[0] ?? null),
                'dependencia' => $this->cleanText($row[1] ?? ''),
                
                // Cafeteria questions
                'usa_cafeteria' => $this->cleanText($row[2] ?? ''),
                'calidad_sabor' => $this->cleanText($row[3] ?? ''),
                'porcion_alimentos' => $this->cleanText($row[4] ?? ''),
                'menu_ofrecido' => $this->cleanText($row[5] ?? ''),
                'variedad_menu' => $this->cleanText($row[6] ?? ''),
                'temperatura_comida' => $this->cleanText($row[7] ?? ''),
                'limpieza_comedor' => $this->cleanText($row[8] ?? ''),
                'servicio_tienda' => $this->cleanText($row[9] ?? ''),
                'trato_personal_cafeteria' => $this->cleanText($row[10] ?? ''),
                'aspectos_positivos_cafeteria' => $this->cleanText($row[11] ?? ''),
                'oportunidades_mejora_cafeteria' => $this->cleanText($row[12] ?? ''),
                'retiro_cafeteria' => $this->cleanText($row[13] ?? ''),
                
                // Transport questions
                'usa_transporte' => $this->cleanText($row[14] ?? ''),
                'puntualidad_transporte' => $this->cleanText($row[15] ?? ''),
                'limpieza_vehiculo' => $this->cleanText($row[16] ?? ''),
                'trato_personal_transporte' => $this->cleanText($row[17] ?? ''),
                'comunicacion_transporte' => $this->cleanText($row[18] ?? ''),
                'aspectos_positivos_transporte' => $this->cleanText($row[19] ?? ''),
                'oportunidades_mejora_transporte' => $this->cleanText($row[20] ?? ''),
                'retiro_transporte' => $this->cleanText($row[21] ?? ''),
                
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            // Validación básica: debe tener al menos dependencia
            if (empty($data['dependencia'])) {
                return null;
            }
            
            return $data;
            
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseDate($date)
    {
        if (empty($date)) return now();
        
        try {
            if (is_numeric($date)) {
                return Carbon::instance(Date::excelToDateTimeObject($date));
            }
            return Carbon::parse($date);
        } catch (\Exception $e) {
            return now();
        }
    }

    private function cleanText($text)
    {
        if (is_null($text)) return '';
        return trim((string) $text);
    }

    public function comparison()
    {
        $periods = DB::table('complementary_services_surveys')
            ->select('period')
            ->groupBy('period')
            ->orderBy('period', 'desc')
            ->get();

        return view('surveys.complementary-services.transport.comparison', compact('periods'));
    }

    public function generateComparison(Request $request)
    {
        // Obtener parámetros tanto de POST como de GET
        $period1 = $request->input('period1');
        $period2 = $request->input('period2');
        $service = $request->input('service', 'both');
        $dependency = $request->input('dependency', 'all');

        if (!$period1 || !$period2) {
            // Obtener períodos disponibles para mostrar en la vista
            $periods = DB::table('complementary_services_surveys')
                ->select('period')
                ->groupBy('period')
                ->orderBy('period', 'desc')
                ->get();

            return view('surveys.complementary-services.transport.comparison', compact('periods'))
                ->with('error', 'Debe seleccionar dos períodos para comparar.');
        }

        $comparisonData = $this->buildComparisonData($period1, $period2, $dependency);
        
        // Obtener períodos disponibles para la vista
        $periods = DB::table('complementary_services_surveys')
            ->select('period')
            ->groupBy('period')
            ->orderBy('period', 'desc')
            ->get();
        
        return view('surveys.complementary-services.transport.comparison', compact('comparisonData', 'periods'));
    }

    private function buildComparisonData($period1, $period2, $dependency = 'all')
    {
        $data1 = $this->getPeriodData($period1, $dependency);
        $data2 = $this->getPeriodData($period2, $dependency);

        $cafeteriaMetrics1 = $this->calculateSinglePeriodCafeteriaMetrics($data1);
        $cafeteriaMetrics2 = $this->calculateSinglePeriodCafeteriaMetrics($data2);
        
        $transportMetrics1 = $this->calculateSinglePeriodTransportMetrics($data1);
        $transportMetrics2 = $this->calculateSinglePeriodTransportMetrics($data2);

        // Calcular diferencias y tendencias
        $cafeteriaDifferences = $this->calculateDifferences($cafeteriaMetrics1, $cafeteriaMetrics2);
        $transportDifferences = $this->calculateDifferences($transportMetrics1, $transportMetrics2);

        return [
            'period1' => $period1,
            'period2' => $period2,
            'dependency' => $dependency,
            'responses_period1' => $data1->count(),
            'responses_period2' => $data2->count(),
            'cafeteria_period1' => $cafeteriaMetrics1,
            'cafeteria_period2' => $cafeteriaMetrics2,
            'transport_period1' => $transportMetrics1,
            'transport_period2' => $transportMetrics2,
            'cafeteria_differences' => $cafeteriaDifferences,
            'transport_differences' => $transportDifferences
        ];
    }

    private function calculateDifferences($metrics1, $metrics2)
    {
        $differences = [];
        
        foreach ($metrics1 as $key => $value1) {
            if (isset($metrics2[$key]) && is_numeric($value1) && is_numeric($metrics2[$key])) {
                $difference = $metrics2[$key] - $value1;
                $differences[$key] = [
                    'period1' => $value1,
                    'period2' => $metrics2[$key],
                    'difference' => $difference,
                    'percentage_change' => $value1 > 0 ? round(($difference / $value1) * 100, 1) : 0,
                    'trend' => $difference > 0 ? 'mejora' : ($difference < 0 ? 'disminucion' : 'estable'),
                    'trend_class' => $difference > 0 ? 'text-success' : ($difference < 0 ? 'text-danger' : 'text-muted')
                ];
            }
        }
        
        return $differences;
    }

    private function getPeriodData($period, $dependency = 'all')
    {
        $query = DB::table('complementary_services_surveys')
            ->where('period', $period);

        if ($dependency !== 'all') {
            $query->where('dependencia', 'like', '%' . $dependency . '%');
        }

        return $query->get();
    }

    private function calculateSinglePeriodCafeteriaMetrics($data)
    {
        // Buscar usuarios que usan cafetería (si respuesta contiene "Si.")
        $cafeteriaUsers = $data->filter(function ($item) {
            return stripos($item->usa_cafeteria, 'Si.') !== false;
        });
        $total = $cafeteriaUsers->count();
        
        if ($total === 0) {
            return [
                'calidad_sabor' => 0,
                'porcion_satisfaccion' => 0,
                'menu_calidad' => 0,
                'variedad_menu' => 0,
                'temperatura_adecuada' => 0,
                'limpieza_comedor' => 0,
                'trato_personal' => 0,
                'total_respuestas' => 0,
                'total_usuarios' => 0,
                'aspectos_positivos' => [],
                'oportunidades_mejora' => []
            ];
        }

        return [
            'calidad_sabor' => $this->calculatePositivePercentage($cafeteriaUsers, 'calidad_sabor', ['Excelente', 'Bueno']),
            'porcion_satisfaccion' => $this->calculatePositivePercentage($cafeteriaUsers, 'porcion_alimentos', ['Muy satisfecho', 'Satisfecho']),
            'menu_calidad' => $this->calculatePositivePercentage($cafeteriaUsers, 'menu_ofrecido', ['Excelente', 'Bueno']),
            'variedad_menu' => $this->calculatePositivePercentage($cafeteriaUsers, 'variedad_menu', ['Sí', 'Algunas veces']),
            'temperatura_adecuada' => $this->calculatePositivePercentage($cafeteriaUsers, 'temperatura_comida', ['Sí', 'Algunas veces']),
            'limpieza_comedor' => $this->calculatePositivePercentage($cafeteriaUsers, 'limpieza_comedor', ['Limpio y ordenado']),
            'trato_personal' => $this->calculatePositivePercentage($cafeteriaUsers, 'trato_personal_cafeteria', ['Excelente', 'Bueno']),
            'total_respuestas' => $total,
            'total_usuarios' => $cafeteriaUsers->count(),
            'aspectos_positivos' => $this->extractComments($cafeteriaUsers, 'aspectos_positivos_cafeteria'),
            'oportunidades_mejora' => $this->extractComments($cafeteriaUsers, 'oportunidades_mejora_cafeteria')
        ];
    }

    private function calculateSinglePeriodTransportMetrics($data)
    {
        // Buscar usuarios que usan transporte (si respuesta contiene "Si.")
        $transportUsers = $data->filter(function ($item) {
            return stripos($item->usa_transporte, 'Si.') !== false;
        });
        $total = $transportUsers->count();
        
        if ($total === 0) {
            return [
                'puntualidad' => 0,
                'limpieza_vehiculo' => 0,
                'trato_personal' => 0,
                'comunicacion' => 0,
                'total_respuestas' => 0,
                'total_usuarios' => 0,
                'aspectos_positivos' => [],
                'oportunidades_mejora' => []
            ];
        }

        return [
            'puntualidad' => $this->calculatePositivePercentage($transportUsers, 'puntualidad_transporte', ['Sí', 'Algunas veces']),
            'limpieza_vehiculo' => $this->calculatePositivePercentage($transportUsers, 'limpieza_vehiculo', ['Sí']),
            'trato_personal' => $this->calculatePositivePercentage($transportUsers, 'trato_personal_transporte', ['Sí']),
            'comunicacion' => $this->calculatePositivePercentage($transportUsers, 'comunicacion_transporte', ['Sí']),
            'total_respuestas' => $total,
            'total_usuarios' => $transportUsers->count(),
            'aspectos_positivos' => $this->extractComments($transportUsers, 'aspectos_positivos_transporte'),
            'oportunidades_mejora' => $this->extractComments($transportUsers, 'oportunidades_mejora_transporte')
        ];
    }

    private function calculatePositivePercentage($collection, $field, $positiveValues)
    {
        $total = $collection->whereNotNull($field)->where($field, '!=', '')->count();
        if ($total === 0) return 0;
        
        $positive = $collection->whereIn($field, $positiveValues)->count();
        return round(($positive / $total) * 100, 1);
    }

    private function extractComments($collection, $field)
    {
        $comments = $collection
            ->whereNotNull($field)
            ->where($field, '!=', '')
            ->pluck($field)
            ->filter(function ($comment) {
                $comment = trim(strtolower($comment));
                // Filtrar comentarios vacíos o genéricos
                return !empty($comment) && 
                       !in_array($comment, ['ninguna', 'ninguno', 'n/a', 'na', 'no', 'no aplica', '-']);
            })
            ->take(10) // Limitar a 10 comentarios más relevantes
            ->toArray();
        
        return array_values($comments);
    }

    private function getDashboardData()
    {
        $latestPeriod = DB::table('complementary_services_surveys')
            ->select('period')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->first();

        if (!$latestPeriod) {
            return [
                'has_data' => false,
                'message' => 'No hay datos cargados. Por favor, cargue al menos una encuesta.'
            ];
        }

        $data = $this->getPeriodData($latestPeriod->period);
        $cafeteriaMetrics = $this->calculateSinglePeriodCafeteriaMetrics($data);
        $transportMetrics = $this->calculateSinglePeriodTransportMetrics($data);

        // Calcular respuestas esperadas (ejemplo: basado en dependencias o configuración)
        $expectedResponses = $this->calculateExpectedResponses($latestPeriod->period);

        return [
            'has_data' => true,
            'latest_period' => $latestPeriod->period,
            'cafeteria' => $cafeteriaMetrics,
            'transport' => $transportMetrics,
            'total_responses' => $data->count(),
            'expected_responses' => $expectedResponses,
            'cafeteria_users' => $data->filter(function ($item) {
                return stripos($item->usa_cafeteria, 'Si.') !== false;
            })->count(),
            'transport_users' => $data->filter(function ($item) {
                return stripos($item->usa_transporte, 'Si.') !== false;
            })->count(),
            'dependencies' => $data->groupBy('dependencia')->map->count()
        ];
    }

    /**
     * Calcula las respuestas esperadas para un período
     * Esto puede basarse en configuración, dependencias activas, o datos históricos
     */
    private function calculateExpectedResponses($period)
    {
        // Ejemplo: calcular basado en dependencias o configuración
        // Puedes ajustar esta lógica según tus necesidades específicas
        
        // Opción 1: Número fijo configurado
        $baseExpected = 100; // Número base esperado
        
        // Opción 2: Basado en dependencias activas
        $activeDependencies = DB::table('complementary_services_surveys')
            ->where('period', $period)
            ->distinct('dependencia')
            ->count('dependencia');
        
        // Estimar respuestas esperadas: 20-30 respuestas por dependencia
        $estimatedPerDependency = 25;
        $calculatedExpected = $activeDependencies * $estimatedPerDependency;
        
        // Retornar el mayor entre el base y el calculado
        return max($baseExpected, $calculatedExpected);
    }

    public function export()
    {
        // Implementation for data export
        return response()->json(['message' => 'Export functionality to be implemented']);
    }
}
