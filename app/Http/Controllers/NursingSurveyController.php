<?php

namespace App\Http\Controllers;

use App\Models\NursingSurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class NursingSurveyController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Mostrar el dashboard con análisis
     */
    public function index(Request $request)
    {
        $selectedPeriod = $request->get('period');
        $availablePeriods = NursingSurveyResponse::getAvailablePeriods();
        
        if (!$selectedPeriod && $availablePeriods->isNotEmpty()) {
            $selectedPeriod = $availablePeriods->first();
        }

        $totalResponses = NursingSurveyResponse::when($selectedPeriod, function($query) use ($selectedPeriod) {
            return $query->where('survey_period', $selectedPeriod);
        })->count();

        $chartData = NursingSurveyResponse::getChartData($selectedPeriod);
        $dependencyAnalysis = NursingSurveyResponse::getAnalysisByDependency($selectedPeriod);
        $trendData = NursingSurveyResponse::getTrendData();
        
        // Asegurar que chartData tenga estructura por defecto si está vacío
        if (empty($chartData)) {
            $chartData = [
                'experience' => ['labels' => [], 'data' => []],
                'presentation' => ['labels' => [], 'data' => []],
                'availability' => ['labels' => [], 'data' => []],
                'professionalism' => ['labels' => [], 'data' => []],
                'effective_response' => ['labels' => [], 'data' => []],
                'cleanliness' => ['labels' => [], 'data' => []],
                'reports' => ['labels' => [], 'data' => []],
                'clarity' => ['labels' => [], 'data' => []]
            ];
        }
        
        $responses = NursingSurveyResponse::when($selectedPeriod, function($query) use ($selectedPeriod) {
            return $query->where('survey_period', $selectedPeriod);
        })->with('uploader')->orderBy('timestamp', 'desc')->paginate(10);

        return view('surveys.internal-client.enfermeria.index', [
            'totalResponses' => $totalResponses,
            'chartData' => $chartData,
            'dependenciesData' => $dependencyAnalysis,
            'dashboardData' => [
                'trend_data' => $trendData
            ],
            'availablePeriods' => $availablePeriods,
            'selectedPeriod' => $selectedPeriod,
            'responses' => $responses
        ]);
    }

    /**
     * Mostrar formulario de upload
     */
    public function upload()
    {
        return view('surveys.internal-client.enfermeria.upload');
    }

    /**
     * Procesar el archivo Excel subido
     */
    public function processUpload(Request $request)
    {
        $request->validate([
            'survey_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'survey_year' => 'required|integer|min:2020|max:2030',
            'survey_month' => 'required|integer|min:1|max:12'
        ]);

        $surveyPeriod = $request->survey_year . '-' . str_pad($request->survey_month, 2, '0', STR_PAD_LEFT);

        try {
            DB::beginTransaction();

            // Eliminar registros existentes del mismo período
            NursingSurveyResponse::where('survey_period', $surveyPeriod)->delete();

            // Procesar archivo
            $file = $request->file('survey_file');
            $results = $this->processExcelFile($file, $surveyPeriod);

            DB::commit();

            return redirect()->route('surveys.internal-client.enfermeria')
                ->with('success', "Encuesta procesada exitosamente. Se importaron {$results['success']} respuestas.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error procesando encuesta de enfermería: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al procesar el archivo: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Procesar archivo Excel
     */
    private function processExcelFile($file, $surveyPeriod)
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        $headers = $rows[0];
        $successCount = 0;
        $errorCount = 0;

        // Mapeo de columnas basado en el formato esperado
        $columnMapping = [
            'timestamp' => 0,           // Marca temporal
            'dependencia' => 1,         // Seleccione su dependencia
            'experiencia_enfermeria' => 2, // 1. ¿Cómo califica su experiencia...
            'presentacion_personal' => 3,  // 2. ¿Considera que la presentación personal...
            'comentarios_presentacion' => 4, // Comentarios presentación
            'disponibilidad_personal' => 5,  // 3. ¿Cómo califica la disponibilidad...
            'comentarios_disponibilidad' => 6, // Comentarios disponibilidad
            'profesionalismo' => 7,     // 4. ¿Considera que el personal actúa...
            'comentarios_profesionalismo' => 8, // Comentarios profesionalismo
            'respuesta_efectiva' => 9,  // 5. ¿Considera que la respuesta es efectiva...
            'comentarios_respuesta' => 10, // Comentarios respuesta
            'limpieza_orden' => 11,     // 6. ¿Cómo califica la limpieza...
            'comentarios_limpieza' => 12, // Comentarios limpieza
            'reportes_oportunos' => 13, // 7. ¿El área realiza reportes oportunos...
            'comentarios_reportes' => 14, // Comentarios reportes
            'claridad_reportes' => 15   // 8. ¿Considera que los reportes son claros...
        ];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            if (empty($row[0])) continue; // Saltar filas vacías

            try {
                // Procesar timestamp
                $timestamp = $this->parseTimestamp($row[0]);
                
                $response = new NursingSurveyResponse([
                    'survey_period' => $surveyPeriod,
                    'timestamp' => $timestamp,
                    'dependencia' => $this->cleanValue($row[1]),
                    'experiencia_enfermeria' => $this->cleanValue($row[2]),
                    'presentacion_personal' => $this->cleanValue($row[3]),
                    'comentarios_presentacion' => $this->cleanValue($row[4]),
                    'disponibilidad_personal' => $this->cleanValue($row[5]),
                    'comentarios_disponibilidad' => $this->cleanValue($row[6]),
                    'profesionalismo' => $this->cleanValue($row[7]),
                    'comentarios_profesionalismo' => $this->cleanValue($row[8]),
                    'respuesta_efectiva' => $this->cleanValue($row[9]),
                    'comentarios_respuesta' => $this->cleanValue($row[10]),
                    'limpieza_orden' => $this->cleanValue($row[11]),
                    'comentarios_limpieza' => $this->cleanValue($row[12]),
                    'reportes_oportunos' => $this->cleanValue($row[13]),
                    'comentarios_reportes' => $this->cleanValue($row[14]),
                    'claridad_reportes' => $this->cleanValue($row[15]),
                    'uploaded_by' => Auth::id(),
                    'uploaded_at' => now()
                ]);

                $response->save();
                $successCount++;

            } catch (\Exception $e) {
                Log::error("Error procesando fila {$i}: " . $e->getMessage());
                $errorCount++;
            }
        }

        return [
            'success' => $successCount,
            'errors' => $errorCount
        ];
    }

    /**
     * Limpiar y validar valores
     */
    private function cleanValue($value)
    {
        if (is_null($value)) return null;
        
        $cleaned = trim($value);
        return empty($cleaned) ? null : $cleaned;
    }

    /**
     * Parsear timestamp desde Excel
     */
    private function parseTimestamp($timestamp)
    {
        try {
            // Si es un número (fecha de Excel)
            if (is_numeric($timestamp)) {
                return Carbon::instance(Date::excelToDateTimeObject($timestamp));
            }
            
            // Si es una cadena de texto
            if (is_string($timestamp)) {
                // Formato: 6/6/2025 12:12:10
                return Carbon::createFromFormat('n/j/Y H:i:s', $timestamp);
            }
            
            return now();
        } catch (\Exception $e) {
            Log::error("Error parseando timestamp: {$timestamp}");
            return now();
        }
    }

    /**
     * Exportar datos a Excel
     */
    public function export(Request $request)
    {
        $period = $request->get('period');
        $responses = NursingSurveyResponse::when($period, function($query) use ($period) {
            return $query->where('survey_period', $period);
        })->orderBy('timestamp')->get();

        $headers = [
            'Período',
            'Timestamp',
            'Dependencia',
            'Experiencia Enfermería',
            'Presentación Personal',
            'Comentarios Presentación',
            'Disponibilidad Personal',
            'Comentarios Disponibilidad',
            'Profesionalismo',
            'Comentarios Profesionalismo',
            'Respuesta Efectiva',
            'Comentarios Respuesta',
            'Limpieza y Orden',
            'Comentarios Limpieza',
            'Reportes Oportunos',
            'Comentarios Reportes',
            'Claridad Reportes'
        ];

        $filename = 'encuesta_enfermeria_' . ($period ?? 'todos') . '.csv';

        return response()->stream(function() use ($responses, $headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($responses as $response) {
                fputcsv($handle, [
                    $response->survey_period,
                    $response->timestamp->format('Y-m-d H:i:s'),
                    $response->dependencia,
                    $response->experiencia_enfermeria,
                    $response->presentacion_personal,
                    $response->comentarios_presentacion,
                    $response->disponibilidad_personal,
                    $response->comentarios_disponibilidad,
                    $response->profesionalismo,
                    $response->comentarios_profesionalismo,
                    $response->respuesta_efectiva,
                    $response->comentarios_respuesta,
                    $response->limpieza_orden,
                    $response->comentarios_limpieza,
                    $response->reportes_oportunos,
                    $response->comentarios_reportes,
                    $response->claridad_reportes
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Mostrar resultados detallados de las encuestas
     */
    public function results(Request $request)
    {
        $selectedPeriod = $request->get('period');
        $availablePeriods = NursingSurveyResponse::getAvailablePeriods();
        
        if (!$selectedPeriod && $availablePeriods->isNotEmpty()) {
            $selectedPeriod = $availablePeriods->first();
        }

        $totalResponses = NursingSurveyResponse::when($selectedPeriod, function($query) use ($selectedPeriod) {
            return $query->where('survey_period', $selectedPeriod);
        })->count();

        $chartData = NursingSurveyResponse::getChartData($selectedPeriod);
        $dependencyAnalysis = NursingSurveyResponse::getAnalysisByDependency($selectedPeriod);
        $trendData = NursingSurveyResponse::getTrendData();
        
        return view('surveys.internal-client.enfermeria.results', [
            'totalResponses' => $totalResponses,
            'chartData' => $chartData,
            'dependenciesData' => $dependencyAnalysis,
            'dashboardData' => [
                'trend_data' => $trendData
            ],
            'selectedPeriod' => $selectedPeriod,
            'availablePeriods' => $availablePeriods
        ]);
    }
}
