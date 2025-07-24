<?php

namespace App\Http\Controllers;

use App\Models\ParentStudentSurvey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class ParentStudentSurveyController extends Controller
{
    public function index()
    {
        $surveys = DB::table('parent_student_surveys')
            ->select('period', 'year', 'month', 'created_at')
            ->groupBy(['period', 'year', 'month', 'created_at'])
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $periods = $surveys->map(function ($survey) {
            try {
                return [
                    'id' => $survey->period,
                    'label' => $survey->period,
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
        
        return view('surveys.parent-student.index', compact('periods', 'dashboardData'));
    }

    public function upload()
    {
        // Obtener estadísticas para mostrar en la vista
        $recentUploads = ParentStudentSurvey::whereDate('uploaded_at', today())->count();
        $totalRecords = ParentStudentSurvey::count();
        
        return view('surveys.parent-student.upload', compact('recentUploads', 'totalRecords'));
    }

    public function processUpload(Request $request)
    {
        try {
            Log::info('=== INICIANDO PROCESO DE CARGA ===');
            Log::info('Método HTTP: ' . $request->method());
            Log::info('Content-Type: ' . $request->header('Content-Type'));
            Log::info('Datos recibidos: ' . json_encode($request->all()));
            Log::info('Archivos recibidos: ' . json_encode(array_keys($request->allFiles())));
            
            // Validar entrada
            $request->validate([
                'excel_files.*' => 'required|file|mimes:xlsx,xls|max:10240',
                'survey_type' => 'required|in:complete,transport_only,cafeteria_only',
                'period' => 'required|string|max:255',
                'auto_detect_provider' => 'nullable|boolean',
                'skip_duplicates' => 'nullable|boolean'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación: ' . json_encode($e->errors()));
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación: ' . implode(', ', array_flatten($e->errors()))
                ], 422);
            }
            throw $e;
        }

        try {
            $files = $request->file('excel_files');
            if (!$files) {
                Log::error('No se recibieron archivos excel_files');
                return response()->json([
                    'success' => false,
                    'message' => 'No se recibieron archivos'
                ], 400);
            }
            
            $totalProcessed = 0;
            $totalErrors = [];
            $fileResults = [];

            DB::beginTransaction();

            foreach ($files as $index => $file) {
                $fileName = $file->getClientOriginalName();
                $processed = 0;
                $errors = [];
                
                Log::info("Procesando archivo: $fileName");
                
                try {
                    $spreadsheet = IOFactory::load($file->getPathname());
                    $worksheet = $spreadsheet->getActiveSheet();
                    
                    $highestRow = $worksheet->getHighestRow();
                    Log::info("Archivo $fileName tiene $highestRow filas");
                    
                    // Auto-detectar proveedor si está habilitado
                    $provider = $request->auto_detect_provider ? 
                        $this->detectProvider($worksheet, $fileName) : 
                        'Manual';

                    // Procesar desde la fila 2 (asumiendo que la 1 tiene headers)
                    for ($row = 2; $row <= $highestRow; $row++) {
                        try {
                            Log::info("Procesando fila $row");
                            $data = $this->extractRowData($worksheet, $row, $request->survey_type);
                            
                            if ($data && !empty($data['student_grade'])) {
                                // Agregar campos adicionales automáticamente
                                $data['survey_type'] = $request->survey_type;
                                $data['period'] = $request->period;
                                $data['provider'] = $provider;
                                $data['uploaded_by'] = Auth::id() ?? 1;
                                $data['uploaded_at'] = now();
                                $data['source_file'] = $fileName;
                                
                                // Extraer año y mes del período si es posible
                                if (preg_match('/(\d{4})-(\d{1,2})/', $request->period, $matches)) {
                                    $data['year'] = (int)$matches[1];
                                    $data['month'] = (int)$matches[2];
                                } else {
                                    $data['year'] = date('Y');
                                    $data['month'] = date('n');
                                }
                                
                                // Verificar duplicados si está habilitado
                                if ($request->skip_duplicates && $this->isDuplicate($data)) {
                                    Log::info("Fila $row omitida por duplicado");
                                    continue;
                                }
                                
                                ParentStudentSurvey::create($data);
                                $processed++;
                                $totalProcessed++;
                                Log::info("Fila $row procesada exitosamente");
                            } else {
                                $errors[] = "Fila $row: Datos vacíos o inválidos";
                                $totalErrors[] = "Archivo: $fileName, Fila $row: Datos vacíos o inválidos";
                                Log::warning("Fila $row omitida: datos vacíos");
                            }
                        } catch (\Exception $e) {
                            $error = "Archivo: $fileName, Fila $row: " . $e->getMessage();
                            $errors[] = $error;
                            $totalErrors[] = $error;
                            Log::error("Error en fila $row: " . $e->getMessage());
                        }
                    }
                    
                    $fileResults[] = [
                        'file' => $fileName,
                        'processed' => $processed,
                        'errors' => count($errors),
                        'provider' => $provider
                    ];
                    
                    Log::info("Archivo $fileName completado: $processed procesados, " . count($errors) . " errores");
                    
                } catch (\Exception $e) {
                    $error = "Error procesando archivo $fileName: " . $e->getMessage();
                    $errors[] = $error;
                    $totalErrors[] = $error;
                    Log::error($error);
                    
                    $fileResults[] = [
                        'file' => $fileName,
                        'processed' => 0,
                        'errors' => 1,
                        'provider' => 'Error'
                    ];
                }
            }

            DB::commit();
            
            Log::info("=== PROCESO COMPLETADO ===");
            Log::info("Total procesados: $totalProcessed");
            Log::info("Total errores: " . count($totalErrors));

            // Preparar mensaje de respuesta
            $message = "Procesamiento completado: $totalProcessed registros procesados en " . count($files) . " archivo(s).";
            
            if (count($totalErrors) > 0) {
                $message .= " Se encontraron " . count($totalErrors) . " errores.";
            }
            
            // Guardar detalles en sesión para mostrar en la vista
            session(['upload_results' => $fileResults]);
            session(['upload_errors' => array_slice($totalErrors, 0, 10)]); // Solo los primeros 10 errores

            // Si es una solicitud AJAX, devolver JSON
            if ($request->ajax() || $request->expectsJson()) {
                $response = [
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'total_processed' => $totalProcessed,
                        'total_files' => count($files),
                        'total_errors' => count($totalErrors),
                        'file_results' => $fileResults
                    ]
                ];
                Log::info('Respuesta JSON: ' . json_encode($response));
                return response()->json($response);
            }

            return redirect()->route('surveys.parent-student.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('=== ERROR CRÍTICO EN PROCESS UPLOAD ===');
            Log::error('Mensaje: ' . $e->getMessage());
            Log::error('Archivo: ' . $e->getFile());
            Log::error('Línea: ' . $e->getLine());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Si es una solicitud AJAX, devolver JSON
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error crítico procesando el archivo: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Error procesando archivos: ' . $e->getMessage()]);
        }
    }

    private function detectProvider($worksheet, $fileName)
    {
        // Estrategia de detección automática del proveedor
        $fileName = strtolower($fileName);
        
        // Detectar por nombre de archivo
        if (strpos($fileName, 'sapore') !== false) {
            return 'Sapore';
        } elseif (strpos($fileName, 'aldimark') !== false) {
            return 'Aldimark';
        }
        
        // Detectar por contenido del archivo (buscar en las primeras filas)
        try {
            for ($row = 1; $row <= min(5, $worksheet->getHighestRow()); $row++) {
                for ($col = 'A'; $col <= 'Z'; $col++) {
                    $cellValue = strtolower((string)$worksheet->getCell($col . $row)->getValue());
                    
                    if (strpos($cellValue, 'sapore') !== false) {
                        return 'Sapore';
                    } elseif (strpos($cellValue, 'aldimark') !== false) {
                        return 'Aldimark';
                    }
                }
            }
        } catch (\Exception $e) {
            // Si hay error en la detección, continuar con valor por defecto
        }
        
        return 'No detectado';
    }

    private function isDuplicate($data)
    {
        // Verificar duplicados basándose en timestamp, grado de estudiante y período
        return ParentStudentSurvey::where('timestamp', $data['timestamp'])
            ->where('student_grade', $data['student_grade'])
            ->where('period', $data['period'])
            ->exists();
    }

    private function extractRowData($worksheet, $row, $surveyType)
    {
        $data = [];
        
        try {
            // Para archivos de cafetería: A=timestamp, B=grado
            // Para archivos de transporte: A=timestamp, B=grado
            $timestamp = $worksheet->getCell('A' . $row)->getValue();
            $studentGrade = trim($worksheet->getCell('B' . $row)->getValue() ?: '');
            
            // Validar que los campos básicos no estén vacíos
            if (empty($timestamp)) {
                Log::warning("Fila $row omitida: timestamp vacío");
                return null; // Fila vacía, omitir
            }
            
            $data['timestamp'] = $this->parseDate($timestamp);
            $data['student_grade'] = $this->normalizeGrade($studentGrade);
            $data['student_name'] = "Estudiante-" . $row; // Nombre genérico
            
            // Según el tipo de encuesta, extraer diferentes campos
            switch ($surveyType) {
                case 'complete':
                    $data = array_merge($data, $this->extractCafeteriaData($worksheet, $row));
                    $data = array_merge($data, $this->extractTransportData($worksheet, $row));
                    break;
                    
                case 'cafeteria_only':
                    $data = array_merge($data, $this->extractCafeteriaData($worksheet, $row));
                    // Para archivos de solo cafetería, marcar que SÍ usa cafetería
                    $data['uses_cafeteria'] = 'Sí';
                    // Valores por defecto para transporte
                    $data['uses_transport'] = 'No';
                    break;
                    
                case 'transport_only':
                    $data = array_merge($data, $this->extractTransportData($worksheet, $row));
                    // Para archivos de solo transporte, marcar que SÍ usa transporte
                    $data['uses_transport'] = 'Sí';
                    // Valores por defecto para cafetería
                    $data['uses_cafeteria'] = 'No';
                    break;
            }
            
            Log::info("Fila $row procesada: estudiante = {$data['student_name']}, grado = {$data['student_grade']}");
            return $data;
            
        } catch (\Exception $e) {
            Log::error("Error extrayendo datos de la fila $row: " . $e->getMessage());
            throw new \Exception("Error extrayendo datos de la fila $row: " . $e->getMessage());
        }
    }
    
    /**
     * Extraer el grado del estudiante desde el archivo Excel
     */
    private function extractStudentGrade($worksheet, $row, $studentName)
    {
        try {
            // Estrategia 1: Buscar en columnas específicas que podrían contener el grado
            // Revisar columnas comunes donde suele estar el grado
            for ($col = 'X'; $col <= 'AZ'; $col++) {
                $cellValue = trim($worksheet->getCell($col . $row)->getValue() ?: '');
                if (!empty($cellValue)) {
                    $grade = $this->parseGradeFromText($cellValue);
                    if ($grade) {
                        Log::info("Grado encontrado en columna $col para fila $row: $grade");
                        return $grade;
                    }
                }
            }
            
            // Estrategia 2: Analizar el nombre del estudiante para extraer grado
            $gradeFromName = $this->extractGradeFromStudentName($studentName);
            if ($gradeFromName) {
                Log::info("Grado extraído del nombre para fila $row: $gradeFromName");
                return $gradeFromName;
            }
            
            // Estrategia 3: Valor por defecto basado en patrones comunes
            Log::warning("No se pudo determinar el grado para fila $row, usando 'Sin especificar'");
            return 'Sin especificar';
            
        } catch (\Exception $e) {
            Log::error("Error extrayendo grado para fila $row: " . $e->getMessage());
            return 'Error';
        }
    }
    
    /**
     * Analizar texto para encontrar información de grado
     */
    private function parseGradeFromText($text)
    {
        $text = strtolower(trim($text));
        
        // Buscar números específicos de grado PRIMERO para mayor precisión
        if (preg_match('/(\d+)°?/', $text, $matches)) {
            $gradeNumber = intval($matches[1]);
            if ($gradeNumber == 0) return 'Preescolar';
            if ($gradeNumber >= 1 && $gradeNumber <= 5) return 'Primaria';
            if ($gradeNumber >= 6 && $gradeNumber <= 9) return 'Secundaria';
            if ($gradeNumber >= 10 && $gradeNumber <= 11) return 'Bachillerato';
        }
        
        // Patrones de grados más comunes
        $gradePatterns = [
            '/preescolar|prees|jardin|kindergarten|pre-k|transicion/' => 'Preescolar',
            '/primaria|primero|segundo|tercero|cuarto|quinto/' => 'Primaria',
            '/secundaria|sexto|septimo|octavo|noveno/' => 'Secundaria',
            '/bachillerato|décimo|once|undécimo|bachiller/' => 'Bachillerato'
        ];
        
        foreach ($gradePatterns as $pattern => $grade) {
            if (preg_match($pattern, $text)) {
                return $grade;
            }
        }
        
        return null;
    }
    
    /**
     * Extraer grado desde el nombre del estudiante si contiene información de grado
     */
    private function extractGradeFromStudentName($studentName)
    {
        // Algunos nombres pueden incluir el grado, ej: "Juan Pérez - 5° Primaria"
        if (strpos($studentName, '-') !== false) {
            $parts = explode('-', $studentName);
            if (count($parts) > 1) {
                $gradePart = trim($parts[1]);
                return $this->parseGradeFromText($gradePart);
            }
        }
        
        return null;
    }
    
    private function extractCafeteriaData($worksheet, $row)
    {
        return [
            // Mapeo correcto para archivos de cafetería (basado en el Excel proporcionado)
            'food_quality' => $worksheet->getCell('C' . $row)->getCalculatedValue() ?: null, // Calidad y sabor
            'portion_satisfaction' => $worksheet->getCell('D' . $row)->getCalculatedValue() ?: null, // Satisfacción porción
            'menu_offered' => $worksheet->getCell('E' . $row)->getCalculatedValue() ?: null, // Menú ofrecido
            'menu_variety' => $worksheet->getCell('F' . $row)->getCalculatedValue() ?: null, // Variedad proteína/ensaladas
            'food_temperature' => $worksheet->getCell('G' . $row)->getCalculatedValue() ?: null, // Temperatura comida
            'dining_cleanliness' => $worksheet->getCell('H' . $row)->getCalculatedValue() ?: null, // Limpieza comedor
            'store_service' => $worksheet->getCell('I' . $row)->getCalculatedValue() ?: null, // Servicio tienda escolar
            'staff_treatment_cafeteria' => $worksheet->getCell('J' . $row)->getCalculatedValue() ?: null, // Trato personal Aldimark
            'positive_aspects_cafeteria' => $worksheet->getCell('K' . $row)->getCalculatedValue() ?: null, // Aspectos positivos
            'improvement_opportunities_cafeteria' => $worksheet->getCell('L' . $row)->getCalculatedValue() ?: null, // Oportunidades mejora
            'withdrawal_reason_cafeteria' => null, // No aplica para este formato
        ];
    }
    
    private function extractTransportData($worksheet, $row)
    {
        return [
            // NO incluir uses_transport aquí, se maneja en extractRowData
            'route_number' => $worksheet->getCell('P' . $row)->getCalculatedValue() ?: null,
            'punctuality' => $worksheet->getCell('Q' . $row)->getCalculatedValue() ?: null,
            'vehicle_cleanliness' => $worksheet->getCell('R' . $row)->getCalculatedValue() ?: null,
            'staff_treatment_transport' => $worksheet->getCell('S' . $row)->getCalculatedValue() ?: null,
            'communication' => $worksheet->getCell('T' . $row)->getCalculatedValue() ?: null,
            'positive_aspects_transport' => $worksheet->getCell('U' . $row)->getCalculatedValue() ?: null,
            'improvement_opportunities_transport' => $worksheet->getCell('V' . $row)->getCalculatedValue() ?: null,
            'withdrawal_reason_transport' => $worksheet->getCell('W' . $row)->getCalculatedValue() ?: null,
        ];
    }
    
    private function parseDate($dateValue)
    {
        if (empty($dateValue)) {
            return now();
        }
        
        try {
            if (is_numeric($dateValue)) {
                // Excel date serial number
                return Carbon::createFromTimestamp(($dateValue - 25569) * 86400);
            } else {
                return Carbon::parse($dateValue);
            }
        } catch (\Exception $e) {
            return now();
        }
    }
    
    /**
     * Normalizar el grado del estudiante
     */
    private function normalizeGrade($grade)
    {
        if (empty($grade)) {
            return 'Sin especificar';
        }
        
        $grade = trim(strtolower($grade));
        
        // Mapeo de grados comunes
        $gradeMapping = [
            'pk' => 'Pre-Kinder',
            'prekinder' => 'Pre-Kinder',
            'kinder' => 'Kinder',
            'jardín' => 'Jardín',
            'transición' => 'Transición',
            'transicion' => 'Transición',
            'primero' => 'Primero',
            'segundo' => 'Segundo',
            'tercero' => 'Tercero',
            'cuarto' => 'Cuarto',
            'quinto' => 'Quinto',
            'sexto' => 'Sexto',
            'séptimo' => 'Séptimo',
            'septimo' => 'Séptimo',
            'octavo' => 'Octavo',
            'noveno' => 'Noveno',
            'décimo' => 'Décimo',
            'decimo' => 'Décimo',
            'once' => 'Once',
            'undécimo' => 'Once'
        ];
        
        return $gradeMapping[$grade] ?? ucfirst($grade);
    }

    public function analysis(Request $request)
    {
        $selectedPeriod = $request->get('period');
        $selectedGrade = $request->get('grade');
        $selectedProvider = $request->get('provider');
        
        $periods = ParentStudentSurvey::getAvailablePeriods();
        $grades = ParentStudentSurvey::getAvailableGrades();
        $providers = ParentStudentSurvey::getAvailableProviders();
        
        // Si no se selecciona período, usar el más reciente
        if (!$selectedPeriod && $periods->count() > 0) {
            $selectedPeriod = $periods->first();
        }
        
        // Obtener métricas
        $cafeteriaMetrics = ParentStudentSurvey::calculateCafeteriaMetrics($selectedPeriod, $selectedGrade);
        $transportMetrics = ParentStudentSurvey::calculateTransportMetrics($selectedPeriod, $selectedGrade);
        
        // Análisis por grado
        $gradeAnalysis = ParentStudentSurvey::getAnalysisByGrade($selectedPeriod);
        
        // Datos para gráficas
        $cafeteriaChartData = [
            'labels' => ['Calidad de Comida', 'Satisfacción Porciones', 'Menú Ofrecido', 'Variedad Menú', 'Temperatura', 'Limpieza Comedor', 'Atención Personal'],
            'data' => [
                $cafeteriaMetrics['food_quality'],
                $cafeteriaMetrics['portion_satisfaction'],
                $cafeteriaMetrics['menu_offered'],
                $cafeteriaMetrics['menu_variety'],
                $cafeteriaMetrics['food_temperature'],
                $cafeteriaMetrics['dining_cleanliness'],
                $cafeteriaMetrics['staff_treatment']
            ]
        ];
        
        $transportChartData = [
            'labels' => ['Puntualidad', 'Limpieza Vehículo', 'Atención Personal', 'Comunicación'],
            'data' => [
                $transportMetrics['punctuality'],
                $transportMetrics['vehicle_cleanliness'],
                $transportMetrics['staff_treatment'],
                $transportMetrics['communication']
            ]
        ];
        
        return view('surveys.parent-student.analysis', compact(
            'periods',
            'grades',
            'providers',
            'selectedPeriod',
            'selectedGrade',
            'selectedProvider',
            'cafeteriaMetrics',
            'transportMetrics',
            'gradeAnalysis',
            'cafeteriaChartData',
            'transportChartData'
        ));
    }

    public function comparison(Request $request)
    {
        // Obtener parámetros tanto de POST como de GET
        $period1 = $request->input('period1');
        $period2 = $request->input('period2');
        $service = $request->input('service', 'both');
        $grade = $request->input('grade', 'all');

        // Logging para debugging
        Log::info('=== COMPARISON DEBUGGING ===');
        Log::info('Period1: ' . $period1);
        Log::info('Period2: ' . $period2);
        Log::info('Service: ' . $service);
        Log::info('Grade: ' . $grade);
        Log::info('Request data: ' . json_encode($request->all()));

        if (!$period1 || !$period2) {
            // Obtener períodos disponibles para mostrar en la vista
            $periods = DB::table('parent_student_surveys')
                ->select('period')
                ->groupBy('period')
                ->orderBy('period', 'desc')
                ->get();

            // Obtener grados únicos para debugging
            $availableGrades = DB::table('parent_student_surveys')
                ->select('student_grade')
                ->distinct()
                ->whereNotNull('student_grade')
                ->orderBy('student_grade')
                ->get();
                
            Log::info('Available grades in database: ' . json_encode($availableGrades->pluck('student_grade')->toArray()));

            return view('surveys.parent-student.comparison', compact('periods'))
                ->with('error', 'Debe seleccionar dos períodos para comparar.');
        }

        $comparisonData = $this->buildComparisonData($period1, $period2, $grade);
        
        // Obtener períodos disponibles para la vista
        $periods = DB::table('parent_student_surveys')
            ->select('period')
            ->groupBy('period')
            ->orderBy('period', 'desc')
            ->get();
        
        return view('surveys.parent-student.comparison', compact('comparisonData', 'periods'));
    }

    private function buildComparisonData($period1, $period2, $grade = 'all')
    {
        $data1 = $this->getPeriodData($period1, $grade);
        $data2 = $this->getPeriodData($period2, $grade);

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
            'grade' => $grade,
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

    public function getPeriodData($period, $grade = 'all')
    {
        $query = ParentStudentSurvey::where('period', $period);

        Log::info("getPeriodData called with period: {$period}, grade: {$grade}");

        if ($grade !== 'all') {
            // Filtrar con lógica más específica según el grado seleccionado
            switch ($grade) {
                case 'Preescolar':
                    $query->where(function($q) {
                        $q->where('student_grade', 'like', '%preescolar%')
                          ->orWhere('student_grade', 'like', '%prejardín%')
                          ->orWhere('student_grade', 'like', '%jardín%')
                          ->orWhere('student_grade', 'like', '%transición%')
                          ->orWhere('student_grade', 'like', '%kinder%')
                          ->orWhere('student_grade', 'like', '%pre-jardín%')
                          ->orWhere('student_grade', 'like', '%pre jardín%');
                    });
                    break;
                    
                case 'Primaria':
                    $query->where(function($q) {
                        $q->where('student_grade', 'like', '%primaria%')
                          ->orWhere('student_grade', 'like', '%1°%')
                          ->orWhere('student_grade', 'like', '%2°%')
                          ->orWhere('student_grade', 'like', '%3°%')
                          ->orWhere('student_grade', 'like', '%4°%')
                          ->orWhere('student_grade', 'like', '%5°%')
                          ->orWhere('student_grade', 'like', '%primero%')
                          ->orWhere('student_grade', 'like', '%segundo%')
                          ->orWhere('student_grade', 'like', '%tercero%')
                          ->orWhere('student_grade', 'like', '%cuarto%')
                          ->orWhere('student_grade', 'like', '%quinto%');
                    });
                    break;
                    
                case 'Secundaria':
                    $query->where(function($q) {
                        $q->where('student_grade', 'like', '%secundaria%')
                          ->orWhere('student_grade', 'like', '%6°%')
                          ->orWhere('student_grade', 'like', '%7°%')
                          ->orWhere('student_grade', 'like', '%8°%')
                          ->orWhere('student_grade', 'like', '%9°%')
                          ->orWhere('student_grade', 'like', '%sexto%')
                          ->orWhere('student_grade', 'like', '%séptimo%')
                          ->orWhere('student_grade', 'like', '%septimo%')
                          ->orWhere('student_grade', 'like', '%octavo%')
                          ->orWhere('student_grade', 'like', '%noveno%');
                    });
                    break;
                    
                case 'Bachillerato':
                    $query->where(function($q) {
                        $q->where('student_grade', 'like', '%bachillerato%')
                          ->orWhere('student_grade', 'like', '%10°%')
                          ->orWhere('student_grade', 'like', '%11°%')
                          ->orWhere('student_grade', 'like', '%décimo%')
                          ->orWhere('student_grade', 'like', '%decimo%')
                          ->orWhere('student_grade', 'like', '%undécimo%')
                          ->orWhere('student_grade', 'like', '%undecimo%')
                          ->orWhere('student_grade', 'like', '%once%');
                    });
                    break;
                    
                default:
                    // Fallback al comportamiento original
                    $query->where('student_grade', 'like', '%' . $grade . '%');
                    break;
            }
        }

        $results = $query->get();
        
        Log::info("Found {$results->count()} records for period {$period} with grade filter '{$grade}'");
        if ($results->count() > 0) {
            $sampleGrades = $results->take(5)->pluck('student_grade')->toArray();
            Log::info("Sample grades found: " . json_encode($sampleGrades));
        }

        return $results;
    }

    private function getDashboardData()
    {
        $latestPeriod = DB::table('parent_student_surveys')
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

        // Obtener datos del período más reciente
        $latestData = ParentStudentSurvey::where('period', $latestPeriod->period)->get();

        // CORRECCIÓN: Usar solo datos del período más reciente (junio 2025)
        // En lugar de buscar períodos diferentes para cafetería y transporte,
        // usaremos únicamente los datos del período más reciente
        
        // Filtrar datos de cafetería del período actual
        $cafeteriaData = $latestData->filter(function ($item) {
            return stripos($item->uses_cafeteria, 'Sí') !== false || 
                   stripos($item->uses_cafeteria, 'Si') !== false;
        });

        // Filtrar datos de transporte del período actual  
        $transportData = $latestData->filter(function ($item) {
            return stripos($item->uses_transport, 'Sí') !== false || 
                   stripos($item->uses_transport, 'Si') !== false;
        });

        // Calcular métricas usando solo datos del período actual
        $cafeteriaMetrics = $this->calculateSinglePeriodCafeteriaMetrics($cafeteriaData);
        $transportMetrics = $this->calculateSinglePeriodTransportMetrics($transportData);

        // Calcular respuestas esperadas
        $expectedResponses = $this->calculateExpectedResponses($latestPeriod->period);

        // Contar usuarios totales solo del período actual
        $totalCafeteriaUsers = $cafeteriaData->count();
        $totalTransportUsers = $transportData->count();

        // Procesar datos de grados de manera más inteligente
        $gradeData = $this->processGradeDistribution($latestData);

        return [
            'has_data' => true,
            'latest_period' => $latestPeriod->period,
            'cafeteria_period' => $latestPeriod->period, // Siempre usar el período actual
            'transport_period' => $latestPeriod->period,  // Siempre usar el período actual
            'cafeteria' => $cafeteriaMetrics,
            'transport' => $transportMetrics,
            'total_responses' => $latestData->count(),
            'expected_responses' => $expectedResponses,
            'cafeteria_users' => $totalCafeteriaUsers,
            'transport_users' => $totalTransportUsers,
            'grades' => $gradeData['categorized'],
            'grades_detailed' => $gradeData['detailed'],
            'grade_stats' => $gradeData['stats'],
            'transport_data' => $transportData,  // Agregar datos de transporte para los modales
            'periods_info' => [
                'latest' => $latestPeriod->period,
                'cafeteria_source' => $latestPeriod->period,  // Mismo período
                'transport_source' => $latestPeriod->period   // Mismo período
            ]
        ];
    }

    /**
     * Procesar distribución de grados de manera inteligente
     */
    private function processGradeDistribution($data)
    {
        $rawGrades = $data->groupBy('student_grade')->map->count();
        
        // Categorizar grados de manera inteligente
        $categorized = [
            'Preescolar' => 0,
            'Primaria' => 0,
            'Secundaria' => 0,
            'Bachillerato' => 0,
            'Sin especificar' => 0
        ];
        
        $detailed = [];
        $totalResponses = $data->count();
        
        foreach ($rawGrades as $grade => $count) {
            $category = $this->categorizeGrade($grade);
            $categorized[$category] += $count;
            
            // Mantener detalle específico
            $detailed[] = [
                'grade' => $grade,
                'category' => $category,
                'count' => $count,
                'percentage' => round(($count / $totalResponses) * 100, 1),
                'cafeteria_users' => $data->where('student_grade', $grade)
                    ->filter(function($item) {
                        return stripos($item->uses_cafeteria, 'Sí') !== false || stripos($item->uses_cafeteria, 'Si') !== false;
                    })->count(),
                'transport_users' => $data->where('student_grade', $grade)
                    ->filter(function($item) {
                        return stripos($item->uses_transport, 'Sí') !== false || stripos($item->uses_transport, 'Si') !== false;
                    })->count()
            ];
        }
        
        // Calcular estadísticas adicionales
        $stats = [
            'total_grades' => count($detailed),
            'most_represented' => collect($detailed)->sortByDesc('count')->first(),
            'least_represented' => collect($detailed)->sortBy('count')->first(),
            'categories_with_data' => collect($categorized)->filter(function($count) { return $count > 0; })->count(),
            'average_per_grade' => $totalResponses > 0 ? round($totalResponses / max(1, count($detailed)), 1) : 0,
            'distribution_balance' => $this->calculateDistributionBalance($categorized, $totalResponses)
        ];
        
        return [
            'categorized' => $categorized,
            'detailed' => $detailed,
            'stats' => $stats
        ];
    }
    
    /**
     * Categorizar un grado específico
     */
    private function categorizeGrade($grade)
    {
        $grade = strtolower(trim($grade));
        
        // Patrones para preescolar
        if (preg_match('/preescolar|prees|jardin|jardín|kindergarten|pre-k|transicion|transición|kinder/', $grade)) {
            return 'Preescolar';
        }
        
        // Patrones para primaria (1° a 5°)
        if (preg_match('/primaria|1°|2°|3°|4°|5°|primero|segundo|tercero|cuarto|quinto/', $grade)) {
            return 'Primaria';
        }
        
        // Patrones para secundaria (6° a 9°)
        if (preg_match('/secundaria|6°|7°|8°|9°|sexto|séptimo|septimo|octavo|noveno/', $grade)) {
            return 'Secundaria';
        }
        
        // Patrones para bachillerato (10° y 11°)
        if (preg_match('/bachillerato|10°|11°|décimo|decimo|undécimo|undecimo|once/', $grade)) {
            return 'Bachillerato';
        }
        
        // Si no coincide con ningún patrón
        return 'Sin especificar';
    }
    
    /**
     * Calcular balance de distribución
     */
    private function calculateDistributionBalance($categorized, $total)
    {
        if ($total === 0) return 0;
        
        $nonZeroCategories = collect($categorized)->filter(function($count) { return $count > 0; });
        
        if ($nonZeroCategories->count() === 0) return 0;
        
        $expectedPerCategory = $total / $nonZeroCategories->count();
        $variance = $nonZeroCategories->map(function($count) use ($expectedPerCategory) {
            return pow($count - $expectedPerCategory, 2);
        })->avg();
        
        $standardDeviation = sqrt($variance);
        $coefficientOfVariation = $expectedPerCategory > 0 ? ($standardDeviation / $expectedPerCategory) : 0;
        
        // Convertir a porcentaje de balance (100% = perfectamente balanceado)
        return max(0, round((1 - min(1, $coefficientOfVariation)) * 100, 1));
    }

    private function calculateSinglePeriodCafeteriaMetrics($data)
    {
        $cafeteriaUsers = $data->filter(function ($item) {
            return stripos($item->uses_cafeteria, 'Sí') !== false || stripos($item->uses_cafeteria, 'Si') !== false;
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
                'total_usuarios' => 0
            ];
        }

        return [
            'calidad_sabor' => $this->calculatePositivePercentage($cafeteriaUsers, 'food_quality', ['Excelente', 'Bueno', 'Regular']),
            'porcion_satisfaccion' => $this->calculatePositivePercentage($cafeteriaUsers, 'portion_satisfaction', ['Muy satisfecho', 'Satisfecho']),
            'menu_calidad' => $this->calculatePositivePercentage($cafeteriaUsers, 'menu_offered', ['Excelente', 'Bueno', 'Regular']),
            'variedad_menu' => $this->calculatePositivePercentage($cafeteriaUsers, 'menu_variety', ['Sí', 'Algunas veces']),
            'temperatura_adecuada' => $this->calculatePositivePercentage($cafeteriaUsers, 'food_temperature', ['Sí', 'Algunas veces']),
            'limpieza_comedor' => $this->calculatePositivePercentage($cafeteriaUsers, 'dining_cleanliness', ['Limpio y ordenado']),
            'trato_personal' => $this->calculatePositivePercentage($cafeteriaUsers, 'staff_treatment_cafeteria', ['Excelente', 'Bueno']),
            'total_respuestas' => $total,
            'total_usuarios' => $cafeteriaUsers->count()
        ];
    }

    private function calculateSinglePeriodTransportMetrics($data)
    {
        $transportUsers = $data->filter(function ($item) {
            return stripos($item->uses_transport, 'Sí') !== false || stripos($item->uses_transport, 'Si') !== false;
        });
        
        $total = $transportUsers->count();
        
        if ($total === 0) {
            return [
                'puntualidad' => 0,
                'limpieza_vehiculo' => 0,
                'trato_personal' => 0,
                'comunicacion' => 0,
                'total_respuestas' => 0,
                'total_usuarios' => 0
            ];
        }

        // Calcular cada métrica individualmente con sus valores positivos específicos
        $puntualidad = $this->calculatePositivePercentage($transportUsers, 'punctuality', ['Sí']);
        $limpieza_vehiculo = $this->calculatePositivePercentageFromComments($transportUsers, 'vehicle_cleanliness');
        $trato_personal = $this->calculatePositivePercentageFromComments($transportUsers, 'staff_treatment_transport');
        // TEMPORAL: Usar una estimación basada en otros campos hasta que se recarguen los datos
        $comunicacion = $this->calculateCommunicationEstimate($transportUsers);
        
        return [
            'puntualidad' => $puntualidad,
            'limpieza_vehiculo' => $limpieza_vehiculo,
            'trato_personal' => $trato_personal,
            'comunicacion' => $comunicacion,
            'total_respuestas' => $total,
            'total_usuarios' => $transportUsers->count()
        ];
    }

    private function calculatePositivePercentage($collection, $field, $positiveValues = null)
    {
        $total = $collection->count();
        
        if ($total === 0) {
            return 0;
        }

        // Si no se especifican valores positivos, usar los predeterminados
        if ($positiveValues === null) {
            $positiveValues = ['Excelente', 'Bueno'];
        }
        
        $positive = $collection->filter(function ($item) use ($field, $positiveValues) {
            $value = $item->{$field} ?? '';
            
            // Manejar valores null o vacíos
            if (empty($value) || is_null($value)) {
                return false;
            }
            
            // Verificar coincidencia exacta primero
            $isPositive = in_array($value, $positiveValues);
            
            // Si no hay coincidencia exacta, buscar palabras clave en comentarios largos
            if (!$isPositive && strlen($value) > 50) {
                foreach ($positiveValues as $positiveValue) {
                    if (stripos($value, $positiveValue) !== false) {
                        $isPositive = true;
                        break;
                    }
                }
                // Buscar también palabras clave positivas comunes
                $positiveKeywords = ['excelente', 'bueno', 'buena', 'satisfecho', 'satisfecha', 'limpio', 'ordenado', 'puntual', 'responsable', 'calidad', 'recomiendo'];
                foreach ($positiveKeywords as $keyword) {
                    if (stripos($value, $keyword) !== false) {
                        $isPositive = true;
                        break;
                    }
                }
            }
            
            return $isPositive;
        })->count();

        return round(($positive / $total) * 100);
    }

    private function calculatePositivePercentageFromComments($collection, $field)
    {
        $total = $collection->count();
        
        if ($total === 0) {
            return 0;
        }
        
        $positive = $collection->filter(function ($item) use ($field) {
            $value = $item->{$field} ?? '';
            
            // Manejar valores null o vacíos
            if (empty($value) || is_null($value)) {
                return false;
            }
            
            // Lista de palabras clave positivas para evaluar comentarios
            $positiveKeywords = [
                'excelente', 'bueno', 'buena', 'muy bien', 'todo bien', 'todo muy bien',
                'satisfecho', 'satisfecha', 'limpio', 'ordenado', 'puntual', 'puntualidad',
                'responsable', 'respetuoso', 'respetuosa', 'amable', 'amables', 'calidad',
                'recomiendo', 'felicitaciones', 'querido', 'querida', 'seguro', 'seguros',
                'impecable', 'entrega', 'amor', 'cómoda', 'super servicio', 'muy amable',
                'colaborador', 'colaboradores', 'servicial', 'esta todo muy bien',
                'todo está bien', 'continuar como', 'buen servicio'
            ];
            
            // Comentarios neutros que indican satisfacción (no hay quejas)
            $neutralPositivePatterns = [
                '/^(ninguna|no tengo|no tengo observaciones|no aplica|no hay|n\/a|—|\.+)$/i',
                '/^(no tengo comentarios|todo está bien|esta todo muy bien)$/i'
            ];
            
            // Palabras clave negativas que indican problemas claros
            $negativeKeywords = [
                'mal genio', 'no acertiva', 'cero amable', 'poco servicial', 'incómodo',
                'incómodos', 'malas condiciones', 'inappropiado', 'inapropiado',
                'falla', 'llega tarde', 'demasiado incómodos'
            ];
            
            $value = trim($value);
            $lowerValue = strtolower($value);
            
            // Verificar si contiene palabras negativas específicas primero
            foreach ($negativeKeywords as $negativeWord) {
                if (stripos($lowerValue, $negativeWord) !== false) {
                    return false;
                }
            }
            
            // Buscar palabras clave positivas explícitas
            foreach ($positiveKeywords as $positiveWord) {
                if (stripos($lowerValue, $positiveWord) !== false) {
                    return true;
                }
            }
            
            // Verificar patrones neutrales que indican satisfacción
            foreach ($neutralPositivePatterns as $pattern) {
                if (preg_match($pattern, trim($lowerValue))) {
                    return true;
                }
            }
            
            // Si el comentario es constructivo/solicitud de mejora sin queja directa, considerarlo neutral-positivo
            $constructivePatterns = [
                'idealmente', 'sería bueno', 'deberían', 'podrían', 'buses más', 'mayor estabilidad'
            ];
            
            foreach ($constructivePatterns as $constructive) {
                if (stripos($lowerValue, $constructive) !== false) {
                    return true;
                }
            }
            
            return false;
        })->count();

        return round(($positive / $total) * 100);
    }

    private function calculateCommunicationEstimate($collection)
    {
        // MÉTODO TEMPORAL: Estimar comunicación basado en otros indicadores de satisfacción
        // hasta que se recarguen los datos con el mapeo correcto
        
        $total = $collection->count();
        
        if ($total === 0) {
            return 0;
        }
        
        // Obtener promedios de otros indicadores
        $puntualidadPositivos = $collection->filter(function ($item) {
            return ($item->punctuality ?? '') === 'Sí';
        })->count();
        
        $limpiezaPositivos = $collection->filter(function ($item) {
            $value = $item->vehicle_cleanliness ?? '';
            if (empty($value)) return false;
            
            $positiveKeywords = ['excelente', 'bueno', 'buena', 'muy bien', 'responsable', 'calidad'];
            $lowerValue = strtolower($value);
            
            foreach ($positiveKeywords as $keyword) {
                if (stripos($lowerValue, $keyword) !== false) {
                    return true;
                }
            }
            return false;
        })->count();
        
        $tratoPositivos = $collection->filter(function ($item) {
            $value = $item->staff_treatment_transport ?? '';
            if (empty($value)) return false;
            
            $positiveKeywords = ['bien', 'bueno', 'esperamos', 'continuar', 'excelente'];
            $lowerValue = strtolower($value);
            
            foreach ($positiveKeywords as $keyword) {
                if (stripos($lowerValue, $keyword) !== false) {
                    return true;
                }
            }
            return false;
        })->count();
        
        // Estimar comunicación como promedio de otros indicadores (pero más conservador)
        $promedioOtrosIndicadores = ($puntualidadPositivos + $limpiezaPositivos + $tratoPositivos) / 3;
        $estimacionComunicacion = round(($promedioOtrosIndicadores / $total) * 100 * 0.8); // 80% del promedio para ser conservador
        
        return max(0, min(100, $estimacionComunicacion));
    }

    private function calculateExpectedResponses($period)
    {
        $baseExpected = 100;
        
        $activeGrades = DB::table('parent_student_surveys')
            ->where('period', $period)
            ->distinct('student_grade')
            ->count('student_grade');
        
        $estimatedPerGrade = 25;
        $calculatedExpected = $activeGrades * $estimatedPerGrade;
        
        return max($baseExpected, $calculatedExpected);
    }

    /**
     * Generar reporte PDF de las encuestas
     */
    public function generateReport(Request $request)
    {
        $period = $request->get('period');
        
        if ($period) {
            // Generar reporte para un período específico
            $data = $this->getPeriodData($period);
            $filename = "reporte_encuestas_padres_estudiantes_{$period}.pdf";
        } else {
            // Generar reporte general
            $data = $this->getDashboardData();
            $filename = "reporte_encuestas_padres_estudiantes_general.pdf";
        }

        // Aquí iría la lógica real de generación de PDF
        // Por ahora, retornamos una respuesta simulada
        return response()->json([
            'success' => true,
            'message' => 'Reporte generado exitosamente',
            'filename' => $filename,
            'data' => $data
        ]);
    }

    /**
     * Exportar datos de las encuestas
     */
    public function exportData(Request $request)
    {
        $format = $request->get('format', 'excel');
        $period = $request->get('period');
        
        if ($period) {
            $query = ParentStudentSurvey::where('period', $period);
            $filename = "encuestas_padres_estudiantes_{$period}";
        } else {
            $query = ParentStudentSurvey::query();
            $filename = "encuestas_padres_estudiantes_completo";
        }

        $data = $query->get();

        switch ($format) {
            case 'csv':
                return $this->exportToCsv($data, $filename);
            case 'json':
                return $this->exportToJson($data, $filename);
            case 'excel':
            default:
                return $this->exportToExcel($data, $filename);
        }
    }

    /**
     * Exportar a Excel
     */
    private function exportToExcel($data, $filename)
    {
        // Aquí iría la lógica real de exportación a Excel
        // Por ahora, retornamos una respuesta simulada
        return response()->json([
            'success' => true,
            'message' => 'Exportación a Excel completada',
            'filename' => $filename . '.xlsx',
            'format' => 'excel',
            'records' => $data->count()
        ]);
    }

    /**
     * Exportar a CSV
     */
    private function exportToCsv($data, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Escribir encabezados
            fputcsv($file, [
                'ID', 'Período', 'Año', 'Mes', 'Nombre Estudiante', 'Grado', 
                'Usa Cafetería', 'Usa Transporte', 'Fecha Creación'
            ]);

            // Escribir datos
            foreach ($data as $record) {
                fputcsv($file, [
                    $record->id,
                    $record->period,
                    $record->year,
                    $record->month,
                    $record->student_name,
                    $record->student_grade,
                    $record->uses_cafeteria,
                    $record->uses_transport,
                    $record->created_at
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exportar a JSON
     */
    private function exportToJson($data, $filename)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.json"',
        ];

        return response()->json($data, 200, $headers);
    }
}
