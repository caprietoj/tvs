<?php

namespace App\Http\Controllers;

use App\Models\ParentStudentSurvey;
use App\Support\SurveyAnswerEvaluator;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;

class ParentStudentSurveyController extends Controller
{
    public function index(Request $request)
    {
        $surveys = DB::table('parent_student_surveys')
            ->select('period', 'year', 'month', 'created_at')
            ->where('period', '!=', '2024-05')  // Excluir período 2024-05
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

        // Obtener período seleccionado o el más reciente
        $selectedPeriod = $request->get('period', null);
        $dashboardData = $this->getDashboardData($selectedPeriod);
        
        return view('surveys.parent-student.index', compact('periods', 'dashboardData', 'selectedPeriod'));
    }

    public function upload()
    {
        // Obtener estadísticas para mostrar en la vista
        $recentUploads = ParentStudentSurvey::whereDate('uploaded_at', today())->count();
        $totalRecords = ParentStudentSurvey::count();
        
        return view('surveys.parent-student.upload', compact('recentUploads', 'totalRecords'));
    }

    /**
     * Generar y descargar la plantilla de Excel para la carga de encuestas.
     * El orden de las columnas debe coincidir con extractRowData/extractCafeteriaData/extractTransportData.
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Encuesta');

        $headers = [
            'A' => 'timestamp',
            'B' => 'grado',
            'C' => 'calidad_sabor',
            'D' => 'porcion_alimentos',
            'E' => 'menu_ofrecido',
            'F' => 'variedad_menu',
            'G' => 'temperatura_comida',
            'H' => 'limpieza_comedor',
            'I' => 'servicio_tienda',
            'J' => 'trato_personal_cafeteria',
            'K' => 'aspectos_positivos_cafeteria',
            'L' => 'oportunidades_mejora_cafeteria',
            'M' => '',
            'N' => '',
            'O' => '',
            'P' => 'numero_ruta',
            'Q' => 'puntualidad',
            'R' => 'limpieza_vehiculo',
            'S' => 'trato_personal_transporte',
            'T' => 'comunicacion',
            'U' => 'aspectos_positivos_transporte',
            'V' => 'oportunidades_mejora_transporte',
            'W' => 'retiro_transporte',
        ];

        $example = [
            'A' => now()->format('Y-m-d H:i:s'),
            'B' => 'Quinto',
            'C' => 'Excelente',
            'D' => 'Muy satisfecho',
            'E' => 'Excelente',
            'F' => 'Sí',
            'G' => 'Sí',
            'H' => 'Limpio y ordenado',
            'I' => 'Excelente',
            'J' => 'Excelente',
            'K' => 'Buen servicio',
            'L' => 'Mantener la variedad',
            'P' => 'Ruta 1',
            'Q' => 'Sí',
            'R' => 'Sí',
            'S' => 'Sí',
            'T' => 'Sí',
            'U' => 'Puntualidad',
            'V' => 'Mejorar la comunicación',
        ];

        foreach ($headers as $column => $header) {
            $sheet->setCellValue($column . '1', $header);
        }

        foreach ($example as $column => $value) {
            $sheet->setCellValue($column . '2', $value);
        }

        $sheet->getStyle('A1:W1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '28A745'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        foreach (range('A', 'W') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'plantilla_encuesta_padres.xlsx';
        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $excelOutput = ob_get_clean();

        return response($excelOutput, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
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

            // Al recargar un período se reemplazan los datos previos de ese tipo de
            // encuesta para evitar duplicados y conservar los valores corregidos.
            DB::table('parent_student_surveys')
                ->where('period', $request->period)
                ->where('survey_type', $request->survey_type)
                ->delete();

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

                    $columnMap = $this->buildColumnMap($worksheet, $request->survey_type);
                    Log::info("Mapa de columnas detectado: " . json_encode($columnMap));
                    
                    // Auto-detectar proveedor si está habilitado
                    $provider = $request->auto_detect_provider ? 
                        $this->detectProvider($worksheet, $fileName) : 
                        'Manual';

                    // Procesar desde la fila 2 (asumiendo que la 1 tiene headers)
                    for ($row = 2; $row <= $highestRow; $row++) {
                        try {
                            Log::info("Procesando fila $row");
                            $data = $this->extractRowData($worksheet, $row, $request->survey_type, $columnMap);
                            
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
                        } catch (\Throwable $e) {
                            if ($this->isRetryableDatabaseError($e)) {
                                throw $e;
                            }

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
                    
                } catch (\Throwable $e) {
                    if ($this->isRetryableDatabaseError($e)) {
                        throw $e;
                    }

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

        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            
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

    private function isRetryableDatabaseError(\Throwable $e): bool
    {
        if (!$e instanceof QueryException) {
            return false;
        }

        $message = $e->getMessage();

        return (string) $e->getCode() === '40001'
            || str_contains($message, 'Deadlock')
            || str_contains($message, '1213')
            || str_contains($message, 'Lock wait timeout');
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

    private function buildColumnMap($worksheet, $surveyType)
    {
        $map = [];
        $sharedCounters = [];
        $currentSection = null;
        $lastColumn = Coordinate::columnIndexFromString($worksheet->getHighestColumn());

        for ($index = 1; $index <= $lastColumn; $index++) {
            $column = Coordinate::stringFromColumnIndex($index);
            $header = SurveyAnswerEvaluator::normalize($worksheet->getCell($column . '1')->getValue());

            if ($header === '') {
                continue;
            }

            $field = $this->classifyHeader($header, $surveyType, $sharedCounters, $currentSection);

            if ($field !== null && !array_key_exists($field, $map)) {
                $map[$field] = $column;
            }
        }

        $fallbacks = $surveyType === 'transport_only'
            ? [
                'timestamp' => 'A',
                'student_grade' => 'B',
                'route_number' => 'P',
                'punctuality' => 'Q',
                'vehicle_cleanliness' => 'R',
                'staff_treatment_transport' => 'S',
                'communication' => 'T',
                'positive_aspects_transport' => 'U',
                'improvement_opportunities_transport' => 'V',
                'withdrawal_reason_transport' => 'W',
            ]
            : [
                'timestamp' => 'A',
                'student_grade' => 'B',
                'food_quality' => 'C',
                'portion_satisfaction' => 'D',
                'menu_offered' => 'E',
                'menu_variety' => 'F',
                'food_temperature' => 'G',
                'dining_cleanliness' => 'H',
                'store_service' => 'I',
                'staff_treatment_cafeteria' => 'J',
                'positive_aspects_cafeteria' => 'K',
                'improvement_opportunities_cafeteria' => 'L',
                'withdrawal_reason_cafeteria' => 'M',
                'route_number' => 'P',
                'punctuality' => 'Q',
                'vehicle_cleanliness' => 'R',
                'staff_treatment_transport' => 'S',
                'communication' => 'T',
                'positive_aspects_transport' => 'U',
                'improvement_opportunities_transport' => 'V',
                'withdrawal_reason_transport' => 'W',
            ];

        $recognizedQuestions = array_diff(array_keys($map), ['timestamp', 'student_grade']);

        if (empty($recognizedQuestions)) {
            foreach ($fallbacks as $field => $column) {
                if (!isset($map[$field])) {
                    $map[$field] = $column;
                }
            }
        } else {
            $map['timestamp'] = $map['timestamp'] ?? 'A';
            $map['student_grade'] = $map['student_grade'] ?? 'B';
        }

        return $map;
    }

    private function classifyHeader($header, $surveyType, array &$sharedCounters, ?string &$currentSection = null)
    {
        $isTransport = str_contains($header, 'transport');
        $isCafeteria = str_contains($header, 'cafeter')
            || str_contains($header, 'comedor')
            || str_contains($header, 'aliment')
            || str_contains($header, 'tienda');

        if (str_contains($header, 'marca temporal') || str_contains($header, 'timestamp') || str_contains($header, 'fecha')) {
            return 'timestamp';
        }

        if (str_contains($header, 'grado') || str_contains($header, 'curso')) {
            return 'student_grade';
        }

        if (str_contains($header, 'usa') && $isCafeteria) {
            $currentSection = 'cafeteria';

            return 'uses_cafeteria';
        }

        if (str_contains($header, 'usa') && $isTransport) {
            $currentSection = 'transport';

            return 'uses_transport';
        }

        if (str_contains($header, 'ruta')) {
            return 'route_number';
        }

        if (str_contains($header, 'puntualidad')) {
            return 'punctuality';
        }

        if (str_contains($header, 'limpieza') && (str_contains($header, 'vehiculo') || $isTransport)) {
            return 'vehicle_cleanliness';
        }

        if (str_contains($header, 'limpieza') && (str_contains($header, 'comedor') || $isCafeteria)) {
            return 'dining_cleanliness';
        }

        if (str_contains($header, 'limpieza')) {
            if ($isTransport || $surveyType === 'transport_only' || $currentSection === 'transport') {
                return 'vehicle_cleanliness';
            }

            if ($isCafeteria || $surveyType === 'cafeteria_only' || $currentSection === 'cafeteria') {
                return 'dining_cleanliness';
            }
        }

        if (str_contains($header, 'calidad') && (str_contains($header, 'sabor') || str_contains($header, 'comida') || str_contains($header, 'alimento'))) {
            return 'food_quality';
        }

        if (str_contains($header, 'porcion')) {
            return 'portion_satisfaction';
        }

        if (str_contains($header, 'variedad')) {
            return 'menu_variety';
        }

        if (str_contains($header, 'menu')) {
            return 'menu_offered';
        }

        if (str_contains($header, 'temperatura')) {
            return 'food_temperature';
        }

        if (str_contains($header, 'tienda')) {
            return 'store_service';
        }

        if (str_contains($header, 'comunicacion')) {
            return 'communication';
        }

        if (str_contains($header, 'aspecto') && str_contains($header, 'positiv')) {
            return $this->sharedServiceField('positive_aspects', $isTransport, $isCafeteria, $surveyType, $sharedCounters, $currentSection);
        }

        if (str_contains($header, 'oportunidad') && str_contains($header, 'mejora')) {
            return $this->sharedServiceField('improvement_opportunities', $isTransport, $isCafeteria, $surveyType, $sharedCounters, $currentSection);
        }

        if (str_contains($header, 'retiro') || str_contains($header, 'retiraria')) {
            return $this->sharedServiceField('withdrawal_reason', $isTransport, $isCafeteria, $surveyType, $sharedCounters, $currentSection);
        }

        if (str_contains($header, 'trato')) {
            return $this->sharedServiceField('staff_treatment', $isTransport, $isCafeteria, $surveyType, $sharedCounters, $currentSection);
        }

        return null;
    }

    private function sharedServiceField($base, $isTransport, $isCafeteria, $surveyType, array &$sharedCounters, ?string $currentSection = null)
    {
        if ($isTransport) {
            return $base . '_transport';
        }

        if ($isCafeteria) {
            return $base . '_cafeteria';
        }

        if ($surveyType === 'transport_only') {
            return $base . '_transport';
        }

        if ($surveyType === 'cafeteria_only') {
            return $base . '_cafeteria';
        }

        if ($currentSection !== null) {
            return $base . '_' . $currentSection;
        }

        $sharedCounters[$base] = ($sharedCounters[$base] ?? 0) + 1;

        return $sharedCounters[$base] === 1
            ? $base . '_cafeteria'
            : $base . '_transport';
    }

    private function extractRowData($worksheet, $row, $surveyType, array $columnMap = [])
    {
        $data = [];
        
        try {
            $timestampColumn = $columnMap['timestamp'] ?? 'A';
            $gradeColumn = $columnMap['student_grade'] ?? 'B';
            $timestamp = $worksheet->getCell($timestampColumn . $row)->getValue();
            $studentGrade = trim($worksheet->getCell($gradeColumn . $row)->getValue() ?: '');
            
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
                    $data = array_merge($data, $this->extractCafeteriaData($worksheet, $row, $columnMap));
                    $data = array_merge($data, $this->extractTransportData($worksheet, $row, $columnMap));
                    $data['uses_cafeteria'] = $this->resolveServiceUsage($worksheet, $row, $columnMap, 'cafeteria', $data);
                    $data['uses_transport'] = $this->resolveServiceUsage($worksheet, $row, $columnMap, 'transport', $data);
                    break;
                    
                case 'cafeteria_only':
                    $data = array_merge($data, $this->extractCafeteriaData($worksheet, $row, $columnMap));
                    $data['uses_cafeteria'] = $this->resolveServiceUsage($worksheet, $row, $columnMap, 'cafeteria', $data, 'Sí');
                    $data['uses_transport'] = 'No';
                    break;
                    
                case 'transport_only':
                    $data = array_merge($data, $this->extractTransportData($worksheet, $row, $columnMap));
                    $data['uses_transport'] = $this->resolveServiceUsage($worksheet, $row, $columnMap, 'transport', $data, 'Sí');
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

    private function resolveServiceUsage($worksheet, $row, array $columnMap, $service, array $data, $default = null)
    {
        $field = $service === 'cafeteria' ? 'uses_cafeteria' : 'uses_transport';

        if (isset($columnMap[$field])) {
            $value = $worksheet->getCell($columnMap[$field] . $row)->getCalculatedValue();

            if ($value !== null && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        $ratingFields = $service === 'cafeteria'
            ? ['food_quality', 'portion_satisfaction', 'menu_offered', 'menu_variety', 'food_temperature', 'dining_cleanliness', 'store_service', 'staff_treatment_cafeteria', 'positive_aspects_cafeteria', 'improvement_opportunities_cafeteria', 'withdrawal_reason_cafeteria']
            : ['route_number', 'punctuality', 'vehicle_cleanliness', 'staff_treatment_transport', 'communication', 'positive_aspects_transport', 'improvement_opportunities_transport', 'withdrawal_reason_transport'];

        foreach ($ratingFields as $ratingField) {
            if (trim((string) ($data[$ratingField] ?? '')) !== '') {
                return 'Sí';
            }
        }

        return $default ?? 'No';
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
    
    private function extractCafeteriaData($worksheet, $row, array $columnMap = [])
    {
        return [
            'food_quality' => $this->readMappedCell($worksheet, $row, $columnMap, 'food_quality'),
            'portion_satisfaction' => $this->readMappedCell($worksheet, $row, $columnMap, 'portion_satisfaction'),
            'menu_offered' => $this->readMappedCell($worksheet, $row, $columnMap, 'menu_offered'),
            'menu_variety' => $this->readMappedCell($worksheet, $row, $columnMap, 'menu_variety'),
            'food_temperature' => $this->readMappedCell($worksheet, $row, $columnMap, 'food_temperature'),
            'dining_cleanliness' => $this->readMappedCell($worksheet, $row, $columnMap, 'dining_cleanliness'),
            'store_service' => $this->readMappedCell($worksheet, $row, $columnMap, 'store_service'),
            'staff_treatment_cafeteria' => $this->readMappedCell($worksheet, $row, $columnMap, 'staff_treatment_cafeteria'),
            'positive_aspects_cafeteria' => $this->readMappedCell($worksheet, $row, $columnMap, 'positive_aspects_cafeteria'),
            'improvement_opportunities_cafeteria' => $this->readMappedCell($worksheet, $row, $columnMap, 'improvement_opportunities_cafeteria'),
            'withdrawal_reason_cafeteria' => $this->readMappedCell($worksheet, $row, $columnMap, 'withdrawal_reason_cafeteria'),
        ];
    }
    
    private function extractTransportData($worksheet, $row, array $columnMap = [])
    {
        return [
            'route_number' => $this->readMappedCell($worksheet, $row, $columnMap, 'route_number'),
            'punctuality' => $this->readMappedCell($worksheet, $row, $columnMap, 'punctuality'),
            'vehicle_cleanliness' => $this->readMappedCell($worksheet, $row, $columnMap, 'vehicle_cleanliness'),
            'staff_treatment_transport' => $this->readMappedCell($worksheet, $row, $columnMap, 'staff_treatment_transport'),
            'communication' => $this->readMappedCell($worksheet, $row, $columnMap, 'communication'),
            'positive_aspects_transport' => $this->readMappedCell($worksheet, $row, $columnMap, 'positive_aspects_transport'),
            'improvement_opportunities_transport' => $this->readMappedCell($worksheet, $row, $columnMap, 'improvement_opportunities_transport'),
            'withdrawal_reason_transport' => $this->readMappedCell($worksheet, $row, $columnMap, 'withdrawal_reason_transport'),
        ];
    }

    private function readMappedCell($worksheet, $row, array $columnMap, $field)
    {
        if (!isset($columnMap[$field])) {
            return null;
        }

        $value = $worksheet->getCell($columnMap[$field] . $row)->getCalculatedValue();

        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return $value;
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
        Log::info('Period1 INPUT: ' . $period1);
        Log::info('Period2 INPUT: ' . $period2);
        Log::info('Service: ' . $service);
        Log::info('Grade: ' . $grade);
        Log::info('Request data: ' . json_encode($request->all()));
        Log::info('Request URL: ' . $request->fullUrl());
        Log::info('Request method: ' . $request->method());

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

        $comparisonData = $this->buildComparisonData($period1, $period2, $grade, $service);
        
        // Log de depuración
        Log::info('Comparison Data Built:', [
            'cafeteria_period1_usuarios' => $comparisonData['cafeteria_period1']['total_usuarios'] ?? 'N/A',
            'cafeteria_period2_usuarios' => $comparisonData['cafeteria_period2']['total_usuarios'] ?? 'N/A',
            'cafeteria_period1_keys' => array_keys($comparisonData['cafeteria_period1']),
            'cafeteria_period2_keys' => array_keys($comparisonData['cafeteria_period2']),
            'service' => $comparisonData['service'] ?? 'N/A'
        ]);
        
        // Obtener períodos disponibles para la vista
        $periods = DB::table('parent_student_surveys')
            ->select('period')
            ->groupBy('period')
            ->orderBy('period', 'desc')
            ->get();
        
        return view('surveys.parent-student.comparison', compact('comparisonData', 'periods'));
    }

    private function buildComparisonData($period1, $period2, $grade = 'all', $service = 'both')
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
            'service' => $service,
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

    private function getDashboardData($selectedPeriod = null)
    {
        // Si se proporciona un período, usarlo; si no, usar el más reciente
        if ($selectedPeriod) {
            $period = DB::table('parent_student_surveys')
                ->select('period', 'year', 'month')
                ->where('period', $selectedPeriod)
                ->first();
            
            if (!$period) {
                // Si el período seleccionado no existe, usar el más reciente
                $period = DB::table('parent_student_surveys')
                    ->select('period', 'year', 'month')
                    ->orderBy('year', 'desc')
                    ->orderBy('month', 'desc')
                    ->first();
            }
        } else {
            $period = DB::table('parent_student_surveys')
                ->select('period', 'year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->first();
        }

        if (!$period) {
            return [
                'has_data' => false,
                'message' => 'No hay datos cargados. Por favor, cargue al menos una encuesta.'
            ];
        }

        // Obtener datos del período seleccionado o más reciente
        $periodData = ParentStudentSurvey::where('period', $period->period)->get();

        // CORRECCIÓN: Usar solo datos del período seleccionado
        // En lugar de buscar períodos diferentes para cafetería y transporte,
        // usaremos únicamente los datos del período seleccionado
        
        // Filtrar datos de cafetería del período actual
        $cafeteriaData = $periodData->filter(function ($item) {
            return SurveyAnswerEvaluator::usesService($item, 'cafeteria');
        });

        // Filtrar datos de transporte del período actual  
        $transportData = $periodData->filter(function ($item) {
            return SurveyAnswerEvaluator::usesService($item, 'transport');
        });

        // Calcular métricas usando solo datos del período actual
        $cafeteriaMetrics = $this->calculateSinglePeriodCafeteriaMetrics($cafeteriaData);
        $transportMetrics = $this->calculateSinglePeriodTransportMetrics($transportData);

        // Calcular respuestas esperadas
        $expectedResponses = $this->calculateExpectedResponses($period->period);

        // Contar usuarios totales solo del período actual
        $totalCafeteriaUsers = $cafeteriaData->count();
        $totalTransportUsers = $transportData->count();

        // Procesar datos de grados de manera más inteligente
        $gradeData = $this->processGradeDistribution($periodData);

        return [
            'has_data' => true,
            'latest_period' => $period->period,
            'cafeteria_period' => $period->period, // Siempre usar el período seleccionado
            'transport_period' => $period->period,  // Siempre usar el período seleccionado
            'cafeteria' => $cafeteriaMetrics,
            'transport' => $transportMetrics,
            'total_responses' => $periodData->count(),
            'expected_responses' => $expectedResponses,
            'cafeteria_users' => $totalCafeteriaUsers,
            'transport_users' => $totalTransportUsers,
            'grades' => $gradeData['categorized'],
            'grades_detailed' => $gradeData['detailed'],
            'grade_stats' => $gradeData['stats'],
            'transport_data' => $transportData,  // Agregar datos de transporte para los modales
            'needs_reimport' => $this->periodNeedsReimport($periodData),
            'periods_info' => [
                'latest' => $period->period,
                'cafeteria_source' => $period->period,  // Mismo período
                'transport_source' => $period->period   // Mismo período
            ]
        ];
    }

    /**
     * Procesar distribución de grados de manera inteligente
     */
    private function periodNeedsReimport($data): bool
    {
        $ratingFields = [
            'food_quality',
            'portion_satisfaction',
            'menu_offered',
            'menu_variety',
            'food_temperature',
            'dining_cleanliness',
            'staff_treatment_cafeteria',
            'punctuality',
            'vehicle_cleanliness',
            'staff_treatment_transport',
            'communication',
        ];

        foreach ($data as $row) {
            foreach ($ratingFields as $field) {
                $value = trim((string) ($row->{$field} ?? ''));

                if ($value === '') {
                    continue;
                }

                if (str_contains(SurveyAnswerEvaluator::normalize($value), 'en este caso') || mb_strlen($value) > 40) {
                    return true;
                }
            }
        }

        return false;
    }

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
                        return SurveyAnswerEvaluator::usesService($item, 'cafeteria');
                    })->count(),
                'transport_users' => $data->where('student_grade', $grade)
                    ->filter(function($item) {
                        return SurveyAnswerEvaluator::usesService($item, 'transport');
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
            return SurveyAnswerEvaluator::usesService($item, 'cafeteria');
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
            'calidad_sabor' => $this->calculatePositivePercentage($cafeteriaUsers, 'food_quality', SurveyAnswerEvaluator::CAFETERIA_RATING_POSITIVE),
            'porcion_satisfaccion' => $this->calculatePositivePercentage($cafeteriaUsers, 'portion_satisfaction', SurveyAnswerEvaluator::SATISFACTION_POSITIVE),
            'menu_calidad' => $this->calculatePositivePercentage($cafeteriaUsers, 'menu_offered', SurveyAnswerEvaluator::CAFETERIA_RATING_POSITIVE),
            'variedad_menu' => $this->calculatePositivePercentage($cafeteriaUsers, 'menu_variety', SurveyAnswerEvaluator::YES_POSITIVE),
            'temperatura_adecuada' => $this->calculatePositivePercentage($cafeteriaUsers, 'food_temperature', SurveyAnswerEvaluator::YES_POSITIVE),
            'limpieza_comedor' => $this->calculatePositivePercentage($cafeteriaUsers, 'dining_cleanliness', SurveyAnswerEvaluator::CLEANLINESS_POSITIVE),
            'trato_personal' => $this->calculatePositivePercentage($cafeteriaUsers, 'staff_treatment_cafeteria', SurveyAnswerEvaluator::CAFETERIA_RATING_POSITIVE),
            'total_respuestas' => $total,
            'total_usuarios' => $cafeteriaUsers->count()
        ];
    }

    private function calculateSinglePeriodTransportMetrics($data)
    {
        $transportUsers = $data->filter(function ($item) {
            return SurveyAnswerEvaluator::usesService($item, 'transport');
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

        return [
            'puntualidad' => $this->calculatePositivePercentage($transportUsers, 'punctuality', SurveyAnswerEvaluator::YES_POSITIVE),
            'limpieza_vehiculo' => $this->calculatePositivePercentage($transportUsers, 'vehicle_cleanliness', SurveyAnswerEvaluator::YES_POSITIVE),
            'trato_personal' => $this->calculatePositivePercentage($transportUsers, 'staff_treatment_transport', SurveyAnswerEvaluator::YES_POSITIVE),
            'comunicacion' => $this->calculatePositivePercentage($transportUsers, 'communication', SurveyAnswerEvaluator::YES_POSITIVE),
            'total_respuestas' => $total,
            'total_usuarios' => $transportUsers->count()
        ];
    }

    private function calculatePositivePercentage($collection, $field, $positiveValues = null)
    {
        $positiveValues = $positiveValues ?? SurveyAnswerEvaluator::CAFETERIA_RATING_POSITIVE;

        return SurveyAnswerEvaluator::positivePercentage(
            $collection->pluck($field),
            $positiveValues
        );
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
