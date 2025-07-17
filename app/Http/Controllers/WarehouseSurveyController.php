<?php

namespace App\Http\Controllers;

use App\Models\WarehouseSurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class WarehouseSurveyController extends Controller
{
    /**
     * Mostrar el dashboard de encuestas de almacén
     */
    public function index()
    {
        // Obtener el período más reciente
        $latestPeriod = WarehouseSurveyResponse::latest('survey_period')->first();
        
        if (!$latestPeriod) {
            return view('surveys.internal-client.warehouse.index', [
                'hasData' => false,
                'message' => 'No hay datos de encuestas disponibles. Por favor, sube el primer archivo de resultados.'
            ]);
        }

        // Obtener estadísticas del período más reciente
        $latestStats = $this->getAnalyticsByPeriod($latestPeriod->survey_period);
        
        // Obtener evolución histórica (últimos 12 meses)
        $historicalData = $this->getHistoricalData();
        
        return view('surveys.internal-client.warehouse.index', [
            'hasData' => true,
            'latestPeriod' => $latestPeriod->survey_period,
            'latestStats' => $latestStats,
            'historicalData' => $historicalData
        ]);
    }

    /**
     * Mostrar el formulario para subir nueva encuesta
     */
    public function upload()
    {
        try {
            return view('surveys.internal-client.warehouse.upload');
        } catch (\Exception $e) {
            Log::error('Error in warehouse upload view: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar la página: ' . $e->getMessage());
        }
    }

    /**
     * Procesar la subida del archivo Excel
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
            
            // Eliminar datos existentes para el mismo período
            WarehouseSurveyResponse::where('survey_period', $surveyPeriod)->delete();
            
            // Procesar el archivo
            $file = $request->file('survey_file');
            $results = $this->processExcelFile($file, $surveyPeriod);
            
            DB::commit();
            
            return redirect()->route('surveys.internal-client.warehouse')
                ->with('success', "Encuesta procesada exitosamente. Se importaron {$results['success']} respuestas.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error procesando encuesta de almacén: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error procesando el archivo: ' . $e->getMessage())
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
        
        $success = 0;
        $errors = [];
        
        // Saltar la primera fila (encabezados)
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            // Verificar que la fila tenga datos
            if (empty($row[0]) || empty($row[1])) {
                continue;
            }
            
            try {
                // Mapear las columnas según la estructura proporcionada
                $response = [
                    'survey_period' => $surveyPeriod,
                    'timestamp' => $this->parseTimestamp($row[0]),
                    'dependencia' => $this->normalizeDependencia($row[1]),
                    'califica_experiencia' => $this->normalizeRating($row[2]),
                    'califica_tiempos' => $this->normalizeRating($row[3]),
                    'requerimiento_oportuno' => $this->normalizeYesNo($row[4]),
                    'materiales_disponibles' => $this->normalizeYesNo($row[5]),
                    'comentarios_disponibilidad' => $this->cleanText($row[6]),
                    'califica_servicio_persona' => $this->normalizeRating($row[7]),
                    'califica_calidad_materiales' => $this->normalizeRating($row[8]),
                    'comentarios_calidad' => $this->cleanText($row[9]),
                    'opciones_cotizaciones' => $this->normalizeYesNo($row[10]),
                    'comentarios_cotizaciones' => $this->cleanText($row[11]),
                    'proveedores_cumplen' => $this->normalizeYesNo($row[12]),
                    'comentarios_proveedores' => $this->cleanText($row[13]),
                    'aspectos_destacados' => $this->cleanText($row[14]),
                    'oportunidades_mejora' => $this->cleanText($row[15]),
                    'uploaded_by' => Auth::id()
                ];
                
                WarehouseSurveyResponse::create($response);
                $success++;
                
            } catch (\Exception $e) {
                $errors[] = "Fila " . ($i + 1) . ": " . $e->getMessage();
            }
        }
        
        return [
            'success' => $success,
            'errors' => $errors
        ];
    }

    /**
     * Normalizar timestamp
     */
    private function parseTimestamp($timestamp)
    {
        if (is_numeric($timestamp)) {
            // Excel timestamp
            $baseDate = new \DateTime('1900-01-01');
            $baseDate->add(new \DateInterval('P' . ($timestamp - 2) . 'D'));
            return $baseDate;
        }
        
        return \DateTime::createFromFormat('n/j/Y G:i:s', $timestamp) ?: now();
    }

    /**
     * Normalizar dependencia
     */
    private function normalizeDependencia($dependencia)
    {
        $map = [
            'docente' => 'Docente',
            'administrativo' => 'Administrativo',
            'directivo' => 'Directivo'
        ];
        
        $normalized = strtolower(trim($dependencia));
        return $map[$normalized] ?? ucfirst($normalized);
    }

    /**
     * Normalizar calificaciones
     */
    private function normalizeRating($rating)
    {
        $map = [
            'excelente' => 'Excelente',
            'bueno' => 'Bueno',
            'regular' => 'Regular',
            'deficiente' => 'Deficiente'
        ];
        
        $normalized = strtolower(trim($rating));
        return $map[$normalized] ?? 'Regular';
    }

    /**
     * Normalizar respuestas Sí/No
     */
    private function normalizeYesNo($response)
    {
        $normalized = strtolower(trim($response));
        return in_array($normalized, ['sí', 'si', 'yes', 'y']) ? 'Sí' : 'No';
    }

    /**
     * Limpiar texto
     */
    private function cleanText($text)
    {
        if (empty($text) || strtolower(trim($text)) === 'n/a') {
            return null;
        }
        
        return trim($text);
    }

    /**
     * Obtener análisis por período
     */
    private function getAnalyticsByPeriod($period)
    {
        $responses = WarehouseSurveyResponse::where('survey_period', $period)->get();
        
        if ($responses->isEmpty()) {
            return null;
        }

        $stats = [
            'total_responses' => $responses->count(),
            'satisfaction_average' => 0,
            'by_dependencia' => [],
            'by_question' => [],
            'top_highlights' => [],
            'top_issues' => []
        ];

        // Calcular promedio general de satisfacción
        $totalSatisfaction = $responses->sum(function ($response) {
            return $response->calculateSatisfactionAverage();
        });
        $stats['satisfaction_average'] = round(($totalSatisfaction / $responses->count()) * 25, 1);

        // Estadísticas por dependencia
        $stats['by_dependencia'] = $responses->groupBy('dependencia')->map(function ($group) {
            $totalSatisfaction = $group->sum(function ($response) {
                return $response->calculateSatisfactionAverage();
            });
            return [
                'count' => $group->count(),
                'satisfaction' => round(($totalSatisfaction / $group->count()) * 25, 1)
            ];
        })->toArray();

        // Estadísticas por pregunta
        $stats['by_question'] = [
            'experiencia' => $this->getQuestionStats($responses, 'califica_experiencia'),
            'tiempos' => $this->getQuestionStats($responses, 'califica_tiempos'),
            'oportunidad' => $this->getYesNoStats($responses, 'requerimiento_oportuno'),
            'disponibilidad' => $this->getYesNoStats($responses, 'materiales_disponibles'),
            'servicio_persona' => $this->getQuestionStats($responses, 'califica_servicio_persona'),
            'calidad_materiales' => $this->getQuestionStats($responses, 'califica_calidad_materiales'),
            'cotizaciones' => $this->getYesNoStats($responses, 'opciones_cotizaciones'),
            'proveedores' => $this->getYesNoStats($responses, 'proveedores_cumplen')
        ];

        // Aspectos destacados más mencionados
        $stats['top_highlights'] = $responses->whereNotNull('aspectos_destacados')
            ->pluck('aspectos_destacados')
            ->filter()
            ->groupBy(function ($item) {
                return strtolower(trim($item));
            })
            ->map(function ($group) {
                return [
                    'text' => $group->first(),
                    'count' => $group->count()
                ];
            })
            ->sortByDesc('count')
            ->take(10)
            ->values()
            ->toArray();

        // Oportunidades de mejora más mencionadas
        $stats['top_issues'] = $responses->whereNotNull('oportunidades_mejora')
            ->pluck('oportunidades_mejora')
            ->filter()
            ->groupBy(function ($item) {
                return strtolower(trim($item));
            })
            ->map(function ($group) {
                return [
                    'text' => $group->first(),
                    'count' => $group->count()
                ];
            })
            ->sortByDesc('count')
            ->take(10)
            ->values()
            ->toArray();

        return $stats;
    }

    /**
     * Obtener estadísticas para preguntas de calificación
     */
    private function getQuestionStats($responses, $field)
    {
        $counts = $responses->groupBy($field)->map->count();
        $total = $responses->count();
        
        return [
            'excelente' => round(($counts['Excelente'] ?? 0) / $total * 100, 1),
            'bueno' => round(($counts['Bueno'] ?? 0) / $total * 100, 1),
            'regular' => round(($counts['Regular'] ?? 0) / $total * 100, 1),
            'deficiente' => round(($counts['Deficiente'] ?? 0) / $total * 100, 1),
            'average_score' => round($responses->avg(function ($response) use ($field) {
                return WarehouseSurveyResponse::getScoreForRating($response->$field);
            }), 2)
        ];
    }

    /**
     * Obtener estadísticas para preguntas Sí/No
     */
    private function getYesNoStats($responses, $field)
    {
        $counts = $responses->groupBy($field)->map->count();
        $total = $responses->count();
        
        return [
            'si' => round(($counts['Sí'] ?? 0) / $total * 100, 1),
            'no' => round(($counts['No'] ?? 0) / $total * 100, 1),
            'average_score' => round($responses->avg(function ($response) use ($field) {
                return WarehouseSurveyResponse::getScoreForYesNo($response->$field);
            }), 2)
        ];
    }

    /**
     * Obtener datos históricos
     */
    private function getHistoricalData()
    {
        return WarehouseSurveyResponse::selectRaw('
            survey_period,
            COUNT(*) as total_responses,
            AVG(
                (CASE califica_experiencia
                    WHEN "Deficiente" THEN 1
                    WHEN "Regular" THEN 2
                    WHEN "Bueno" THEN 3
                    WHEN "Excelente" THEN 4
                    ELSE 0
                END +
                CASE califica_tiempos
                    WHEN "Deficiente" THEN 1
                    WHEN "Regular" THEN 2
                    WHEN "Bueno" THEN 3
                    WHEN "Excelente" THEN 4
                    ELSE 0
                END +
                CASE requerimiento_oportuno WHEN "Sí" THEN 4 ELSE 1 END +
                CASE materiales_disponibles WHEN "Sí" THEN 4 ELSE 1 END +
                CASE califica_servicio_persona
                    WHEN "Deficiente" THEN 1
                    WHEN "Regular" THEN 2
                    WHEN "Bueno" THEN 3
                    WHEN "Excelente" THEN 4
                    ELSE 0
                END +
                CASE califica_calidad_materiales
                    WHEN "Deficiente" THEN 1
                    WHEN "Regular" THEN 2
                    WHEN "Bueno" THEN 3
                    WHEN "Excelente" THEN 4
                    ELSE 0
                END +
                CASE opciones_cotizaciones WHEN "Sí" THEN 4 ELSE 1 END +
                CASE proveedores_cumplen WHEN "Sí" THEN 4 ELSE 1 END) / 8
            ) * 25 as satisfaction_percentage
        ')
        ->groupBy('survey_period')
        ->orderBy('survey_period', 'desc')
        ->take(12)
        ->get()
        ->reverse()
        ->values();
    }

    /**
     * Exportar datos a Excel
     */
    public function export(Request $request)
    {
        $period = $request->get('period');
        
        if ($period) {
            $responses = WarehouseSurveyResponse::where('survey_period', $period)->get();
        } else {
            $responses = WarehouseSurveyResponse::all();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Encabezados
        $headers = [
            'Período', 'Fecha/Hora', 'Dependencia', 'Experiencia', 'Tiempos', 
            'Requerimiento Oportuno', 'Materiales Disponibles', 'Comentarios Disponibilidad',
            'Servicio Persona', 'Calidad Materiales', 'Comentarios Calidad',
            'Opciones Cotizaciones', 'Comentarios Cotizaciones', 'Proveedores Cumplen',
            'Comentarios Proveedores', 'Aspectos Destacados', 'Oportunidades Mejora'
        ];
        
        $sheet->fromArray($headers, null, 'A1');
        
        // Datos
        $row = 2;
        foreach ($responses as $response) {
            $sheet->fromArray([
                $response->survey_period,
                $response->timestamp->format('Y-m-d H:i:s'),
                $response->dependencia,
                $response->califica_experiencia,
                $response->califica_tiempos,
                $response->requerimiento_oportuno,
                $response->materiales_disponibles,
                $response->comentarios_disponibilidad,
                $response->califica_servicio_persona,
                $response->califica_calidad_materiales,
                $response->comentarios_calidad,
                $response->opciones_cotizaciones,
                $response->comentarios_cotizaciones,
                $response->proveedores_cumplen,
                $response->comentarios_proveedores,
                $response->aspectos_destacados,
                $response->oportunidades_mejora
            ], null, 'A' . $row);
            $row++;
        }
        
        $writer = new Xlsx($spreadsheet);
        $filename = 'encuesta_almacen_' . ($period ?: 'completa') . '.xlsx';
        
        $tempFile = tempnam(sys_get_temp_dir(), 'warehouse_survey');
        $writer->save($tempFile);
        
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}
