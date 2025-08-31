<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\BudgetExecution;
use App\Models\PresupuestoItem;
use App\Models\PresupuestoSpreadsheet;
use App\Models\PresupuestoAprobado;
use App\Models\PresupuestoSeccion;
use App\Services\PresupuestoProcessorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class PresupuestoController extends Controller
{
    protected $processorService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PresupuestoProcessorService $processorService)
    {
        $this->processorService = $processorService;
        
        // Aplicar middleware de autenticación a todas las rutas
        $this->middleware('auth');
        
        // Aplicar middleware de autorización para rol admin
        $this->middleware('can:admin')->except([]);
    }

    /**
     * Display the budget execution page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Verificación adicional de autorización
        if (!auth()->user()->can('admin')) {
            abort(403, 'No tiene permisos para acceder a esta sección. Solo los administradores pueden ver el presupuesto.');
        }
        
        // Obtener todos los datos de ejecución presupuestal
        $budgetExecutions = BudgetExecution::all();
        
        // Calcular totales y resúmenes
        $totalBudgetAmount = $budgetExecutions->sum('budget_amount');
        $totalExecutedAmount = $budgetExecutions->sum('executed_amount');
        $overallExecutionPercentage = $totalBudgetAmount > 0 ? 
            ($totalExecutedAmount / $totalBudgetAmount) * 100 : 0;
        
        // Datos por departamento
        $departmentData = $budgetExecutions->groupBy('department')->map(function ($items, $department) {
            $budgetSum = $items->sum('budget_amount');
            $executedSum = $items->sum('executed_amount');
            $percentage = $budgetSum > 0 ? ($executedSum / $budgetSum) * 100 : 0;
            
            return [
                'department' => $department,
                'budget_amount' => $budgetSum,
                'executed_amount' => $executedSum,
                'execution_percentage' => $percentage,
                'items_count' => $items->count()
            ];
        })->values();
        
        // Datos por mes (si existe)
        $monthlyData = $budgetExecutions->groupBy('month')->map(function ($items, $month) {
            $budgetSum = $items->sum('budget_amount');
            $executedSum = $items->sum('executed_amount');
            $percentage = $budgetSum > 0 ? ($executedSum / $budgetSum) * 100 : 0;
            
            return [
                'month' => $month,
                'budget_amount' => $budgetSum,
                'executed_amount' => $executedSum,
                'execution_percentage' => $percentage
            ];
        })->values();
        
        // Análisis de alertas
        $alerts = $budgetExecutions->filter(function ($item) {
            return $item->execution_percentage > 90; // Ejecuciones críticas
        });
        
        // Obtener departamentos con bajo rendimiento
        $underperformingDepartments = $departmentData->filter(function ($dept) {
            return $dept['execution_percentage'] < 50;
        });
        
        // Obtener departamentos con sobreejecución
        $overexecutingDepartments = $departmentData->filter(function ($dept) {
            return $dept['execution_percentage'] > 100;
        });
        
        $data = [
            // Información general
            'colegio' => 'COLEGIO VICTORIA SAS',
            'total_budget_amount' => $totalBudgetAmount,
            'total_executed_amount' => $totalExecutedAmount,
            'overall_execution_percentage' => $overallExecutionPercentage,
            'balance' => $totalBudgetAmount - $totalExecutedAmount,
            
            // Datos detallados
            'budget_executions' => $budgetExecutions,
            'department_data' => $departmentData,
            'monthly_data' => $monthlyData,
            
            // Análisis y alertas
            'alerts' => $alerts,
            'underperforming_departments' => $underperformingDepartments,
            'overexecuting_departments' => $overexecutingDepartments,
            
            // Estadísticas adicionales
            'total_departments' => $budgetExecutions->unique('department')->count(),
            'average_execution_percentage' => $departmentData->avg('execution_percentage'),
            
            // Datos de Excel (sesión)
            'excel_data' => session('excel_data', []),
            
            // Datos para gráficos
            'chart_data' => [
                'departments' => $departmentData->pluck('department'),
                'budget_amounts' => $departmentData->pluck('budget_amount'),
                'executed_amounts' => $departmentData->pluck('executed_amount'),
                'execution_percentages' => $departmentData->pluck('execution_percentage')
            ]
        ];

        return view('presupuesto', $data);
    }

    /**
     * Update budget data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            // Validar los datos recibidos
            $validatedData = $request->validate([
                'id' => 'required|exists:budget_executions,id',
                'department' => 'required|string|max:255',
                'month' => 'required|string|max:50',
                'budget_amount' => 'required|numeric|min:0',
                'executed_amount' => 'required|numeric|min:0',
            ]);

            // Buscar y actualizar el registro
            $budgetExecution = BudgetExecution::findOrFail($validatedData['id']);
            
            $budgetExecution->update([
                'department' => $validatedData['department'],
                'month' => $validatedData['month'],
                'budget_amount' => $validatedData['budget_amount'],
                'executed_amount' => $validatedData['executed_amount']
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Presupuesto actualizado correctamente',
                'data' => [
                    'execution_percentage' => $budgetExecution->execution_percentage,
                    'analysis' => $budgetExecution->analysis
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el presupuesto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store new budget execution data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Validar los datos recibidos
            $validatedData = $request->validate([
                'department' => 'required|string|max:255',
                'month' => 'required|string|max:50',
                'budget_amount' => 'required|numeric|min:0',
                'executed_amount' => 'required|numeric|min:0',
            ]);

            // Crear nuevo registro
            $budgetExecution = BudgetExecution::create($validatedData);
            
            return response()->json([
                'success' => true,
                'message' => 'Ejecución presupuestal creada correctamente',
                'data' => $budgetExecution
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la ejecución presupuestal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete budget execution data.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $budgetExecution = BudgetExecution::findOrFail($id);
            $budgetExecution->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Ejecución presupuestal eliminada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la ejecución presupuestal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save budget execution data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function guardarEjecucion(Request $request)
    {
        try {
            // Validar los datos de ejecución
            $validatedData = $request->validate([
                'department' => 'required|string|max:255',
                'month' => 'required|string|max:50',
                'budget_amount' => 'required|numeric|min:0',
                'executed_amount' => 'required|numeric|min:0',
            ]);

            // Buscar si ya existe un registro para el departamento y mes
            $existingRecord = BudgetExecution::where('department', $validatedData['department'])
                                           ->where('month', $validatedData['month'])
                                           ->first();

            if ($existingRecord) {
                // Actualizar registro existente
                $existingRecord->update([
                    'budget_amount' => $validatedData['budget_amount'],
                    'executed_amount' => $validatedData['executed_amount']
                ]);
                $budgetExecution = $existingRecord;
                $message = 'Ejecución presupuestal actualizada correctamente';
            } else {
                // Crear nuevo registro
                $budgetExecution = BudgetExecution::create($validatedData);
                $message = 'Ejecución presupuestal guardada correctamente';
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'id' => $budgetExecution->id,
                    'execution_percentage' => $budgetExecution->execution_percentage,
                    'analysis' => $budgetExecution->analysis
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la ejecución: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar datos de una celda del spreadsheet
     */
    public function guardarCelda(Request $request)
    {
        try {
            // Log de debug
            \Log::info('🔄 Recibida petición para guardar celda', [
                'datos' => $request->all(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Validar los datos de entrada
            $validatedData = $request->validate([
                'tabla_nombre' => 'required|string|max:255',
                'concepto' => 'required|string|max:255',
                'columna' => 'required|string|max:100',
                'valor' => 'required|numeric',
                'fila_orden' => 'nullable|integer',
                'columna_orden' => 'nullable|integer',
                'es_total' => 'boolean'
            ]);

            \Log::info('✅ Datos validados correctamente', $validatedData);

            // Buscar si ya existe la celda
            $celda = PresupuestoSpreadsheet::where('tabla_nombre', $validatedData['tabla_nombre'])
                                        ->where('concepto', $validatedData['concepto'])
                                        ->where('columna', $validatedData['columna'])
                                        ->first();

            if ($celda) {
                // Actualizar celda existente
                $celda->update([
                    'valor' => $validatedData['valor'],
                    'fila_orden' => $validatedData['fila_orden'] ?? $celda->fila_orden,
                    'columna_orden' => $validatedData['columna_orden'] ?? $celda->columna_orden,
                    'es_total' => $validatedData['es_total'] ?? false,
                    'tipo_dato' => 'manual'
                ]);
                $message = 'Celda actualizada correctamente';
            } else {
                // Crear nueva celda
                $celda = PresupuestoSpreadsheet::create([
                    'tabla_nombre' => $validatedData['tabla_nombre'],
                    'concepto' => $validatedData['concepto'],
                    'columna' => $validatedData['columna'],
                    'valor' => $validatedData['valor'],
                    'fila_orden' => $validatedData['fila_orden'] ?? 0,
                    'columna_orden' => $validatedData['columna_orden'] ?? 0,
                    'es_total' => $validatedData['es_total'] ?? false,
                    'tipo_dato' => 'manual'
                ]);
                $message = 'Celda guardada correctamente';
            }

            \Log::info('💾 Celda guardada exitosamente', [
                'id' => $celda->id,
                'tabla' => $celda->tabla_nombre,
                'concepto' => $celda->concepto,
                'valor' => $celda->valor
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'id' => $celda->id,
                    'valor' => $celda->valor,
                    'tabla_nombre' => $celda->tabla_nombre,
                    'concepto' => $celda->concepto,
                    'columna' => $celda->columna
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('❌ Error de validación al guardar celda', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('❌ Error crítico al guardar celda', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la celda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar datos de múltiples celdas del spreadsheet
     */
    public function guardarCeldaMasivo(Request $request)
    {
        try {
            // Log de debug
            \Log::info('🔄 Recibida petición para guardado masivo', [
                'total_celdas' => count($request->input('celdas', [])),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Validar los datos de entrada
            $validatedData = $request->validate([
                'celdas' => 'required|array',
                'celdas.*.tabla_nombre' => 'required|string|max:255',
                'celdas.*.concepto' => 'required|string|max:255',
                'celdas.*.columna' => 'required|string|max:100',
                'celdas.*.valor' => 'required|numeric',
                'celdas.*.fila_orden' => 'nullable|integer',
                'celdas.*.columna_orden' => 'nullable|integer',
                'celdas.*.es_total' => 'boolean'
            ]);

            \Log::info('✅ Datos validados correctamente', ['total_celdas' => count($validatedData['celdas'])]);

            $celdasGuardadas = 0;
            $celdasActualizadas = 0;
            $errores = [];

            foreach ($validatedData['celdas'] as $index => $celdaData) {
                try {
                    // Buscar si ya existe la celda
                    $celda = PresupuestoSpreadsheet::where('tabla_nombre', $celdaData['tabla_nombre'])
                                                ->where('concepto', $celdaData['concepto'])
                                                ->where('columna', $celdaData['columna'])
                                                ->first();

                    if ($celda) {
                        // Actualizar celda existente
                        $celda->update([
                            'valor' => $celdaData['valor'],
                            'fila_orden' => $celdaData['fila_orden'] ?? $celda->fila_orden,
                            'columna_orden' => $celdaData['columna_orden'] ?? $celda->columna_orden,
                            'es_total' => $celdaData['es_total'] ?? false,
                            'tipo_dato' => 'manual'
                        ]);
                        $celdasActualizadas++;
                    } else {
                        // Crear nueva celda
                        PresupuestoSpreadsheet::create([
                            'tabla_nombre' => $celdaData['tabla_nombre'],
                            'concepto' => $celdaData['concepto'],
                            'columna' => $celdaData['columna'],
                            'valor' => $celdaData['valor'],
                            'fila_orden' => $celdaData['fila_orden'] ?? 0,
                            'columna_orden' => $celdaData['columna_orden'] ?? 0,
                            'es_total' => $celdaData['es_total'] ?? false,
                            'tipo_dato' => 'manual'
                        ]);
                        $celdasGuardadas++;
                    }
                } catch (\Exception $e) {
                    $errores[] = [
                        'celda_index' => $index,
                        'tabla' => $celdaData['tabla_nombre'],
                        'concepto' => $celdaData['concepto'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            $totalProcesadas = $celdasGuardadas + $celdasActualizadas;

            \Log::info('💾 Guardado masivo completado', [
                'nuevas' => $celdasGuardadas,
                'actualizadas' => $celdasActualizadas,
                'total_procesadas' => $totalProcesadas,
                'errores' => count($errores)
            ]);

            return response()->json([
                'success' => true,
                'message' => "Guardado completado: {$celdasGuardadas} nuevas, {$celdasActualizadas} actualizadas",
                'data' => [
                    'guardadas' => $celdasGuardadas,
                    'actualizadas' => $celdasActualizadas,
                    'total_procesadas' => $totalProcesadas,
                    'errores' => $errores
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('❌ Error de validación en guardado masivo', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('❌ Error crítico en guardado masivo', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar las celdas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export budget data to Excel.
     *
     * @return \Illuminate\Http\Response
     */
    public function export()
    {
        try {
            // Obtener todos los datos
            $budgetExecutions = BudgetExecution::all();
            
            if ($budgetExecutions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay datos para exportar'
                ], 404);
            }

            // Crear contenido CSV
            $csvData = [];
            $csvData[] = ['Departamento', 'Mes', 'Presupuesto Asignado', 'Monto Ejecutado', 'Porcentaje de Ejecución', 'Análisis', 'Fecha de Creación'];
            
            foreach ($budgetExecutions as $execution) {
                $csvData[] = [
                    $execution->department,
                    $execution->month,
                    number_format($execution->budget_amount, 2),
                    number_format($execution->executed_amount, 2),
                    number_format($execution->execution_percentage, 2) . '%',
                    $execution->analysis,
                    $execution->created_at->format('Y-m-d H:i:s')
                ];
            }

            // Generar nombre de archivo con timestamp
            $filename = 'ejecucion_presupuestal_' . date('Y-m-d_H-i-s') . '.csv';
            
            // Crear contenido del archivo
            $output = fopen('php://temp', 'r+');
            foreach ($csvData as $row) {
                fputcsv($output, $row, ';'); // Usar punto y coma como separador para Excel
            }
            rewind($output);
            $csvContent = stream_get_contents($output);
            fclose($output);

            // Retornar respuesta de descarga
            return response($csvContent)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Transfer-Encoding', 'binary')
                ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process accounting extract.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function procesarExtractoContable(Request $request)
    {
        try {
            // Validar el archivo subido
            $request->validate([
                'archivo' => 'required|file|mimes:xlsx,xls,csv|max:10240', // Max 10MB
            ]);

            // Por ahora, simplemente retornamos éxito
            // En el futuro aquí se procesaría el archivo Excel/CSV
            // y se importarían los datos a la base de datos
            
            return response()->json([
                'success' => true,
                'message' => 'Funcionalidad de procesamiento de extracto contable en desarrollo',
                'note' => 'El archivo fue recibido correctamente pero el procesamiento automático aún no está implementado'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación del archivo',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el extracto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get budget execution data for API.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData()
    {
        try {
            $budgetExecutions = BudgetExecution::all();
            
            return response()->json([
                'success' => true,
                'data' => $budgetExecutions,
                'summary' => [
                    'total_budget' => $budgetExecutions->sum('budget_amount'),
                    'total_executed' => $budgetExecutions->sum('executed_amount'),
                    'overall_percentage' => $budgetExecutions->avg('execution_percentage')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the budget spreadsheet view with Excel-like tabs.
     *
     * @return \Illuminate\Http\Response
     */
    public function spreadsheet()
    {
        // Verificación adicional de autorización
        if (!auth()->user()->can('admin')) {
            abort(403, 'No tiene permisos para acceder a esta sección. Solo los administradores pueden ver el presupuesto.');
        }
        
        // Definir las hojas de cálculo
        $sheets = [
            'BUDGET' => 'Presupuesto Principal',
            'Secciones' => 'Secciones Generales',
            'Detallado secciones1' => 'Detallado Secciones 1',
            'Equipo y Dotacion Salones' => 'Equipo y Dotación Salones',
            'Aseo y Cafeteria' => 'Aseo y Cafetería',
            'Dotaciones' => 'Dotaciones',
            'Agasajos' => 'Agasajos',
            'Tecnología' => 'Tecnología',
            'Gts Contrat' => 'Gastos de Contratos',
            'Afiliaciones y Suscrip' => 'Afiliaciones y Suscripciones',
            'IB' => 'IB',
            'Deportes' => 'Deportes',
            'Entrenamientos' => 'Entrenamientos',
            'Servicios Publicos' => 'Servicios Públicos',
            'Reparaciones Mayores' => 'Reparaciones Mayores',
            'Reparacion muebles' => 'Reparación de Muebles',
            'Mercadeo' => 'Mercadeo',
            'Honorarios' => 'Honorarios'
        ];

        // Obtener datos reales de presupuesto de la base de datos SOLO para "Detallado secciones1"
        // Las demás hojas estarán vacías hasta que se especifique su contenido
        $presupuestoItems = PresupuestoItem::orderBy('seccion')
            ->orderByRaw("CASE WHEN rubro = 'TOTAL' THEN 1 ELSE 0 END") // TOTAL al final
            ->orderBy('rubro')
            ->orderBy('fecha')
            ->orderBy('cuenta')
            ->get();
        
        // Datos de ejemplo ELIMINADOS - solo "Detallado secciones1" tendrá datos reales
        $sampleData = [];
        
        // Reorganizar datos para búsqueda eficiente en la vista (vacío por ahora)
        $optimizedData = [];
        
        // Calcular el número máximo de filas necesarias basado en todos los datos
        $maxRows = $presupuestoItems->count() + 20; // Todos los registros + filas extra
        
        // Obtener datos dinámicos para las secciones
        $seccionesData = $this->getSectionData();
        
        // Obtener presupuestos totales de secciones
        $year = date('Y');
        $presupuestosTotalesSecciones = PresupuestoSeccion::obtenerTodosPresupuestos($year);
        
        // Obtener resumen consolidado por concepto
        $resumenConceptos = $this->getConceptSummary();
        
        // Obtener datos del budget principal
        $budgetData = $this->getBudgetData();
        
        // Obtener datos específicos por concepto
        $budgetDataByConcept = $this->getBudgetDataByConcept();
        
        // Obtener meses disponibles para filtros
        $availableMonths = $this->getAvailableMonths();
        
        // Cargar datos guardados del spreadsheet
        $spreadsheetData = $this->loadSpreadsheetData();
        
        return view('presupuesto.spreadsheet', compact('sheets', 'sampleData', 'optimizedData', 'maxRows', 'presupuestoItems', 'seccionesData', 'resumenConceptos', 'budgetData', 'budgetDataByConcept', 'spreadsheetData', 'availableMonths', 'presupuestosTotalesSecciones'));
    }

    /**
     * Get budget principal data
     */
    private function getBudgetData()
    {
        return [
            'estudiantes' => [
                'presupuesto' => 260,
                'becas' => 14,
                'pagando' => 246
            ],
            'resumen_ingresos' => [
                'ingresos_escolares' => [
                    'presupuesto_aprobado' => 10487847718,
                    'julio' => 0,
                    'agosto' => 0,
                    'septiembre' => 0,
                    'octubre' => 0,
                    'noviembre' => 0,
                    'diciembre' => 0,
                    'enero' => 0,
                    'febrero' => 0
                ],
                'ingresos_otros_escolares' => [
                    'presupuesto_aprobado' => 2369132369,
                    'julio' => 0,
                    'agosto' => 0,
                    'septiembre' => 0,
                    'octubre' => 0,
                    'noviembre' => 0,
                    'diciembre' => 0,
                    'enero' => 0,
                    'febrero' => 0
                ],
                'total_ingresos' => [
                    'presupuesto_aprobado' => 12856980087,
                    'julio' => 0,
                    'agosto' => 0,
                    'septiembre' => 0,
                    'octubre' => 0,
                    'noviembre' => 0,
                    'diciembre' => 0,
                    'enero' => 0,
                    'febrero' => 0
                ]
            ],
            'resumen_gastos' => [
                'total_salarios_prestaciones_academia' => [
                    'presupuesto_aprobado' => 6600731523,
                    'julio' => 0,
                    'agosto' => 0,
                    'septiembre' => 0,
                    'octubre' => 0,
                    'noviembre' => 0,
                    'diciembre' => 0,
                    'enero' => 0,
                    'febrero' => 0
                ],
                'total_salarios_prestaciones_administrativos_sena' => [
                    'presupuesto_aprobado' => 1453226337,
                    'julio' => 0,
                    'agosto' => 0,
                    'septiembre' => 0,
                    'octubre' => 0,
                    'noviembre' => 0,
                    'diciembre' => 0,
                    'enero' => 0,
                    'febrero' => 0
                ],
                'total_rubros_institucionales' => [
                    'presupuesto_aprobado' => 0,
                    'julio' => 0,
                    'agosto' => 0,
                    'septiembre' => 0,
                    'octubre' => 0,
                    'noviembre' => 0,
                    'diciembre' => 0,
                    'enero' => 0,
                    'febrero' => 0
                ],
                'total_seccion_academia' => [
                    'presupuesto_aprobado' => 0,
                    'julio' => 0,
                    'agosto' => 0,
                    'septiembre' => 0,
                    'octubre' => 0,
                    'noviembre' => 0,
                    'diciembre' => 0,
                    'enero' => 0,
                    'febrero' => 0
                ],
                'total_servicios_publicos_otros_egresos' => [
                    'presupuesto_aprobado' => 0,
                    'julio' => 0,
                    'agosto' => 0,
                    'septiembre' => 0,
                    'octubre' => 0,
                    'noviembre' => 0,
                    'diciembre' => 0,
                    'enero' => 0,
                    'febrero' => 0
                ],
                'total_costos_contratos_externos' => [
                    'presupuesto_aprobado' => 0,
                    'julio' => 0,
                    'agosto' => 0,
                    'septiembre' => 0,
                    'octubre' => 0,
                    'noviembre' => 0,
                    'diciembre' => 0,
                    'enero' => 0,
                    'febrero' => 0
                ],
                'total_gastos' => [
                    'presupuesto_aprobado' => 8053957860,
                    'julio' => 0,
                    'agosto' => 0,
                    'septiembre' => 0,
                    'octubre' => 0,
                    'noviembre' => 0,
                    'diciembre' => 0,
                    'enero' => 0,
                    'febrero' => 0
                ]
            ],
            'saldo_diferencia' => [
                'saldo_contable' => 1557567499,
                'diferencia' => [
                    'presupuesto_aprobado' => 0,
                    'julio' => 0,
                    'agosto' => 0,
                    'septiembre' => 0,
                    'octubre' => -1557567499,
                    'noviembre' => 0,
                    'diciembre' => 0,
                    'enero' => 0,
                    'febrero' => 0
                ]
            ],
            'ingresos_escolares_detalle' => [
                'matriculas' => [
                    'presupuesto_aprobado' => 0,
                    'julio' => 0,
                    'agosto' => 0,
                    'septiembre' => 0,
                    'octubre' => 0,
                    'noviembre' => 0,
                    'diciembre' => 0,
                    'enero' => 0,
                    'febrero' => 0
                ],
                'pensiones' => [
                    'presupuesto_aprobado' => 0,
                    'julio' => 0,
                    'agosto' => 0,
                    'septiembre' => 0,
                    'octubre' => 0,
                    'noviembre' => 0,
                    'diciembre' => 0,
                    'enero' => 0,
                    'febrero' => 0
                ],
                'seguros_estudiantiles' => [
                    'presupuesto_aprobado' => 0,
                    'julio' => 0,
                    'agosto' => 0,
                    'septiembre' => 0,
                    'octubre' => 0,
                    'noviembre' => 0,
                    'diciembre' => 0,
                    'enero' => 0,
                    'febrero' => 0
                ],
                'desarrollo_curricular_bilingue_bibliobanco' => [
                    'presupuesto_aprobado' => 0,
                    'julio' => 0,
                    'agosto' => 0,
                    'septiembre' => 0,
                    'octubre' => 0,
                    'noviembre' => 0,
                    'diciembre' => 0,
                    'enero' => 0,
                    'febrero' => 0
                ],
                'sistematizacion_notas' => [
                    'presupuesto_aprobado' => 0,
                    'julio' => 0,
                    'agosto' => 0,
                    'septiembre' => 0,
                    'octubre' => 0,
                    'noviembre' => 0,
                    'diciembre' => 0,
                    'enero' => 0,
                    'febrero' => 0
                ],
                'materiales_generales' => [
                    'presupuesto_aprobado' => 0,
                    'julio' => 0,
                    'agosto' => 0,
                    'septiembre' => 0,
                    'octubre' => 0,
                    'noviembre' => 0,
                    'diciembre' => 0,
                    'enero' => 0,
                    'febrero' => 0
                ],
                'total_ingresos_escolares' => [
                    'presupuesto_aprobado' => 0,
                    'julio' => 0,
                    'agosto' => 0,
                    'septiembre' => 0,
                    'octubre' => 0,
                    'noviembre' => 0,
                    'diciembre' => 0,
                    'enero' => 0,
                    'febrero' => 0
                ]
            ]
        ];
    }

    /**
     * Get budget data for specific concepts by section
     */
    private function getBudgetDataByConcept()
    {
        // Intentar obtener datos de la base de datos primero
        $year = date('Y');
        $budgetData = [];
        
        $secciones = ['PREESCOLAR Y PRIMARIA', 'ESCUELA MEDIA', 'ALTA'];
        
        foreach ($secciones as $seccion) {
            $presupuestos = PresupuestoAprobado::obtenerPresupuestosPorSeccion($seccion, $year);
            
            if (!empty($presupuestos)) {
                $budgetData[$seccion] = $presupuestos;
            }
        }
        
        // Si no hay datos en BD, usar datos por defecto (hardcoded)
        if (empty($budgetData)) {
            return $this->getDefaultBudgetData();
        }
        
        return $budgetData;
    }
    
    /**
     * Datos de presupuesto por defecto (fallback)
     */
    private function getDefaultBudgetData()
    {
        return [
            'PREESCOLAR Y PRIMARIA' => [
                'Capacitación' => 500000,
                'Material Importado' => 800000,
                'Material Deportivo' => 300000,
                'Musicales' => 400000,
                'Part time teacher - reemplazos' => 1200000,
                'Apoyo Institucional' => 600000,
                'Eventos Académicos y Sociales' => 700000,
                'Insumos Tecnológicos' => 450000,
                'Salidas Académicas Sección' => 350000,
                'Alimentación' => 250000,
                'Transporte' => 200000,
                'Insumos de la Sección / Material para Clase' => 750000
            ],
            'ESCUELA MEDIA' => [
                'Capacitación' => 600000,
                'Material Importado' => 900000,
                'Material Deportivo' => 350000,
                'Musicales' => 450000,
                'Part time teacher - reemplazos' => 1300000,
                'Proyecto Comunitario' => 400000,
                'MUN TVS - Otros Colegios - GLY' => 500000,
                'Apoyo Institucional' => 650000,
                'Eventos Académicos y Sociales' => 750000,
                'Insumos Tecnológicos' => 500000,
                'Salidas Académicas Sección' => 400000,
                'Alimentación' => 300000,
                'Transporte' => 250000,
                'Insumos de la Sección / Material para Clase' => 800000
            ],
            'ALTA' => [
                'Capacitación' => 700000,
                'Material Importado' => 1000000,
                'Material Deportivo' => 400000,
                'Musicales' => 500000,
                'Part time teacher - reemplazos' => 1400000,
                'Monografía' => 350000,
                'MUN TVS - Otros Colegios - GLY' => 550000,
                'Preparación Pruebas Saber' => 600000,
                'Apoyo Institucional' => 700000,
                'Eventos Académicos y Sociales' => 800000,
                'Insumos Tecnológicos' => 550000,
                'Salidas Académicas Sección' => 450000,
                'Alimentación' => 350000,
                'Transporte' => 300000,
                'Insumos de la Sección / Material para Clase' => 850000
            ],
            'salarios-administracion' => [
                'salario-aux-transporte' => 1063770859,
                'bonificacion' => 0,
                'bono-salario' => 12720000,
                'prima' => 81174199,
                'vacaciones' => 40065644,
                'cesantias' => 81174199,
                'intereses-cesantias' => 9740904,
                'seguridad-social' => 130789322,
                'aportes-parafiscales' => 36623138,
                'comision-pago' => 0,
                'reuniones' => 7167840
            ],
            'capacitacion-indemnizaciones' => [
                'capacitacion-admin' => 1276365,
                'capacitacion-emc-docentes' => 0,
                'capacitacion-copassi' => 0,
                'indemnizaciones' => 10000000
            ],
            'rubros-institucionales' => [
                'equipos-dotacion' => 0,
                'examenes-medicos' => 18060210,
                'tecnologia-institucional' => 10520000,
                'insumos-enfermeria' => 5260000,
                'mercadeo-admisiones' => 0,
                'eventos-comunidad' => 5008698,
                'mantenimiento-general' => 0,
                'reparaciones-mayores' => 182322100,
                'reparacion-muebles' => 17200200,
                'utiles-oficina' => 34400400,
                'elementos-aseo' => 60000000,
                'gastos-agasajos' => 45867200,
                'bienestar-institucional' => 22933400,
                'eventos-internos' => 0,
                'gastos-contratacion' => 0,
                'afiliaciones-inscripciones' => 0
            ],
            'membresias-convenios' => [
                'bachillerato-internacional' => 284954040,
                'accbi' => 0,
                'red-papaz' => 0
            ],
            'servicios-publicos' => [
                'agua' => 10883921,
                'energia' => 119406196,
                'telefono' => 32518205,
                'vigilancia' => 171134680,
                'internet-arrendamientos' => 156666907
            ],
            'otros-egresos' => [
                'honorarios' => 173824567,
                'legales-financieras' => 6020070,
                'agencia' => 6312000,
                'seguros-generales' => 25930703,
                'anuario' => 31560000,
                'comisiones-bancarias' => 14812273,
                'mensajeria-acarreos' => 1806021,
                'miscelaneas' => 0,
                'impuesto-industria-comercio' => 89998861,
                'normas-internacionales-niif' => 0,
                'plan-seguridad-salud-trabajo' => 28224382,
                'otros-ingresos-retencion' => 0,
                'impuesto-renta' => 204200774,
                'arrendamientos' => 1386750135
            ],
            'secciones-academia-general' => [
                'capacitacion' => 82399832,
                'gastos-importacion-material' => 29656000,
                'biblioteca-institucional' => 4523600,
                'biblioteca' => 23661272,
                'materiales' => 12613480,
                'deportivos' => 8123857,
                'municipios' => 4970700,
                'part-time-teacher-reemplazos' => 0,
                'dotacion' => 68800800,
                'tecnologia' => 5271684,
                'catalogo-pep' => 1052000,
                'demografia' => 11300000,
                'personal-project-pai-proyecto-comunitario' => 11046000,
                'cas-intercambios' => 11046000,
                'mun-tvs-otros-colegios-gly' => 12624000,
                'preparacion-pruebas-saber' => 41028000,
                'psicologia-institucional' => 18000000,
                'consejeria-universidad' => 0,
                'eventos-academicos' => 2000000,
                'evento-ib-cano' => 26547713,
                'eventos-sociales' => 27308870,
                'direccion-general' => 441731150
            ],
            'contratos-externos' => [
                'cafeteria' => 599725348,
                'transporte' => 1231729426
            ]
        ];
    }

    /**
     * Get dynamic section data with concept mapping
     */
    private function getSectionData($mesFilter = null)
    {
        // Mapeo de conceptos antiguos a nuevos
        $conceptMapping = [
            'Capacitación' => 'Capacitación',
            'Gastos Importación/Material Importado' => 'Material Importado',
            'Material Importado' => 'Material Importado',
            'Biblioteca institucional' => 'Biblioteca Institucional',
            'Biblioteca' => 'Biblioteca Institucional',
            'Materiales' => 'Material Deportivo',
            'Deportes-Dotación' => 'Dotación - Deportes',
            'Dotación - Deportes' => 'Dotación - Deportes',
            'Musicales' => 'Musicales',
            'Part time teacher- reemplazos' => 'Part time teacher - reemplazos',
            'Part time teacher - reemplazos' => 'Part time teacher - reemplazos',
            'Dotación' => 'Dotación',
            'Exhibición PEP' => 'Exhibición PEP',
            'Monografia' => 'Monografía',
            'Monografía' => 'Monografía',
            'Personal Project PAI' => 'Proyecto Personal',
            'Proyecto Personal' => 'Proyecto Personal',
            'Proyecto Comunitario' => 'Proyecto Comunitario',
            'CAS / Intercas' => 'CAS / Intercas',
            'MUN TVS-Otros Colegios- GLY' => 'MUN TVS - Otros Colegios - GLY',
            'MUN TVS - Otros Colegios - GLY' => 'MUN TVS - Otros Colegios - GLY',
            'Preparación Pruebas saber' => 'Preparación Pruebas Saber',
            'Preparación Pruebas Saber' => 'Preparación Pruebas Saber',
            'Psicología Institucional' => 'Psicología Institucional',
            'Eventos Académicos y Sociales' => 'Eventos Académicos y Sociales',
            'Material Deportivo' => 'Material Deportivo',
            'Apoyo Institucional' => 'Apoyo Institucional',
            'Insumos Tecnológicos' => 'Insumos Tecnológicos',
            'Salidas Académicas Sección' => 'Salidas Académicas Sección',
            'Alimentación' => 'Alimentación',
            'Transporte' => 'Transporte',
            'Insumos de la Sección / Material para Clase' => 'Insumos de la Sección / Material para Clase',
            'Participación en Eventos' => 'Participación en Eventos'
        ];

        // Definir conceptos por sección
        $sectionConcepts = [
            'PREESCOLAR Y PRIMARIA' => [
                'Capacitación',
                'Material Importado',
                'Material Deportivo',
                'Musicales',
                'Part time teacher - reemplazos',
                'Apoyo Institucional',
                'Eventos Académicos y Sociales',
                'Insumos Tecnológicos',
                'Salidas Académicas Sección',
                'Alimentación',
                'Transporte',
                'Insumos de la Sección / Material para Clase'
            ],
            'ESCUELA MEDIA' => [
                'Capacitación',
                'Material Importado',
                'Material Deportivo',
                'Musicales',
                'Part time teacher - reemplazos',
                'Proyecto Comunitario',
                'MUN TVS - Otros Colegios - GLY',
                'Apoyo Institucional',
                'Eventos Académicos y Sociales',
                'Insumos Tecnológicos',
                'Salidas Académicas Sección',
                'Alimentación',
                'Transporte',
                'Insumos de la Sección / Material para Clase'
            ],
            'ALTA' => [
                'Capacitación',
                'Material Importado',
                'Material Deportivo',
                'Musicales',
                'Part time teacher - reemplazos',
                'Monografía',
                'MUN TVS - Otros Colegios - GLY',
                'Preparación Pruebas Saber',
                'Apoyo Institucional',
                'Eventos Académicos y Sociales',
                'Insumos Tecnológicos',
                'Salidas Académicas Sección',
                'Alimentación',
                'Transporte',
                'Insumos de la Sección / Material para Clase'
            ],
            'PAI' => [
                'Capacitación',
                'Material Importado',
                'Proyecto Comunitario',
                'Proyecto Personal'
            ],
            'PEP' => [
                'Capacitación',
                'Material Importado',
                'Exhibición PEP'
            ],
            'DEPORTES' => [
                'Dotación - Deportes',
                'Transporte',
                'Alimentación',
                'Participación en Eventos'
            ],
            'BIBLIOTECA' => [
                'Biblioteca Institucional'
            ],
            'PSICOLOGÍA INSTITUCIONAL' => [
                'Psicología Institucional'
            ]
        ];

        // Obtener datos de presupuesto y ejecución para cada sección
        $sectionData = [];
        $year = date('Y');
        
        // Obtener presupuestos totales de secciones
        $presupuestosSecciones = PresupuestoSeccion::obtenerTodosPresupuestos($year);
        
        foreach ($sectionConcepts as $seccion => $conceptos) {
            $sectionData[$seccion] = [];
            
            // Obtener presupuesto total de la sección
            $presupuestoTotalSeccion = $presupuestosSecciones[$seccion] ?? 0;
            
            // Calcular presupuesto por concepto (distribuir equitativamente)
            $numeroConceptos = count($conceptos);
            $presupuestoPorConcepto = $numeroConceptos > 0 ? $presupuestoTotalSeccion / $numeroConceptos : 0;
            
            foreach ($conceptos as $concepto) {
                // Inicializar valores
                $presupuesto = $presupuestoPorConcepto;
                $ejecutado = 0;
                
                // Buscar datos de ejecución por concepto exacto
                $query = PresupuestoItem::where('seccion', $seccion)
                    ->where('rubro', $concepto);
                
                // Aplicar filtro de mes si se proporciona
                if ($mesFilter) {
                    $query->whereRaw('DATE_FORMAT(fecha, "%Y-%m") = ?', [$mesFilter]);
                }
                
                $items = $query->get();
                
                if ($items->count() > 0) {
                    $ejecutado = $items->sum('valor') ?? 0;
                } else {
                    // Buscar conceptos mapeados
                    foreach ($conceptMapping as $conceptoAntiguo => $conceptoNuevo) {
                        if ($conceptoNuevo === $concepto) {
                            $queryMapeado = PresupuestoItem::where('seccion', $seccion)
                                ->where('rubro', $conceptoAntiguo);
                                
                            // Aplicar filtro de mes si se proporciona
                            if ($mesFilter) {
                                $queryMapeado->whereRaw('DATE_FORMAT(fecha, "%Y-%m") = ?', [$mesFilter]);
                            }
                            
                            $itemsMapeados = $queryMapeado->get();
                            
                            if ($itemsMapeados->count() > 0) {
                                $ejecutado = $itemsMapeados->sum('valor') ?? 0;
                                break;
                            }
                        }
                    }
                }
                
                $sectionData[$seccion][$concepto] = [
                    'presupuesto' => $presupuesto,
                    'ejecutado' => $ejecutado,
                    'saldo' => $presupuesto - $ejecutado
                ];
            }
        }
        
        return $sectionData;
    }

    /**
     * Get consolidated concept summary with old and new concept mapping
     */
    private function getConceptSummary()
    {
        // Obtener datos de las secciones primero
        $seccionesData = $this->getSectionData();
        
        // Mapeo de conceptos de secciones a conceptos de resumen
        $conceptMappingToSummary = [
            // Conceptos que mantienen el mismo nombre
            'Capacitación' => 'Capacitación',
            'Musicales' => 'Musicales',
            'Dotación' => 'Dotación',
            'Proyecto Comunitario' => 'Proyecto Comunitario',
            'CAS / Intercas' => 'CAS / Intercas',
            'Psicología Institucional' => 'Psicología Institucional',
            'Eventos Académicos y Sociales' => 'Eventos Académicos y Sociales',
            
            // Conceptos que se mapean a otros nombres en el resumen
            'Material Importado' => 'Gastos Importación/Material Importado',
            'Biblioteca Institucional' => 'Biblioteca institucional',
            'Material Deportivo' => 'Materiales',
            'Dotación - Deportes' => 'Deportes-Dotación',
            'Part time teacher - reemplazos' => 'Part time teacher- reemplazos',
            'Exhibición PEP' => 'Exhibición PEP',
            'Monografía' => 'Monografia',
            'Proyecto Personal' => 'Personal Project PAI',
            'MUN TVS - Otros Colegios - GLY' => 'MUN TVS-Otros Colegios- GLY',
            'Preparación Pruebas Saber' => 'Preparación Pruebas saber',
            
            // Conceptos adicionales que aparecen en el resumen pero no están en las secciones
            'Biblioteca' => 'Biblioteca',
            
            // Conceptos de secciones que no aparecen en el resumen se ignoran:
            // 'Apoyo Institucional', 'Insumos Tecnológicos', 'Salidas Académicas Sección',
            // 'Alimentación', 'Transporte', 'Insumos de la Sección / Material para Clase',
            // 'Participación en Eventos'
        ];

        // Conceptos principales que queremos mostrar en el resumen
        $conceptosResumen = [
            'Capacitación',
            'Gastos Importación/Material Importado', 
            'Biblioteca institucional',
            'Biblioteca',
            'Materiales',
            'Deportes-Dotación',
            'Musicales',
            'Part time teacher- reemplazos',
            'Dotación',
            'Exhibición PEP',
            'Monografia',
            'Personal Project PAI',
            'Proyecto Comunitario',
            'CAS / Intercas',
            'MUN TVS-Otros Colegios- GLY',
            'Preparación Pruebas saber',
            'Psicología Institucional',
            'Eventos Académicos y Sociales'
        ];

        $resumen = [];
        
        foreach ($conceptosResumen as $conceptoResumen) {
            $totalPresupuesto = 0;
            $totalEjecutado = 0;
            
            // Recorrer todas las secciones y sumar los valores de cada concepto
            foreach ($seccionesData as $seccion => $conceptos) {
                foreach ($conceptos as $conceptoSeccion => $datos) {
                    // Verificar si este concepto de sección corresponde al concepto de resumen
                    $conceptoMapeado = $conceptMappingToSummary[$conceptoSeccion] ?? null;
                    
                    if ($conceptoMapeado === $conceptoResumen) {
                        $totalPresupuesto += $datos['presupuesto'] ?? 0;
                        $totalEjecutado += $datos['ejecutado'] ?? 0;
                    }
                }
            }
            
            // Para conceptos que no están en las secciones, buscar directamente en la base de datos
            if ($totalPresupuesto == 0 && $totalEjecutado == 0) {
                // Buscar por el concepto exacto en toda la base de datos
                $items = PresupuestoItem::where('rubro', $conceptoResumen)->get();
                if ($items->count() > 0) {
                    $totalPresupuesto = $items->sum('presupuesto') ?? 0;
                    $totalEjecutado = $items->sum('valor') ?? 0;
                }
                
                // También buscar por variaciones del nombre
                if ($totalPresupuesto == 0 && $totalEjecutado == 0) {
                    $variaciones = [];
                    switch ($conceptoResumen) {
                        case 'Biblioteca':
                            $variaciones = ['Biblioteca institucional', 'Biblioteca Institucional'];
                            break;
                        case 'Gastos Importación/Material Importado':
                            $variaciones = ['Material Importado', 'Gastos Importación/Material Importado'];
                            break;
                        case 'Materiales':
                            $variaciones = ['Material Deportivo', 'Materiales'];
                            break;
                        case 'Deportes-Dotación':
                            $variaciones = ['Dotación - Deportes', 'Deportes-Dotación'];
                            break;
                        case 'Part time teacher- reemplazos':
                            $variaciones = ['Part time teacher - reemplazos', 'Part time teacher- reemplazos'];
                            break;
                        case 'Monografia':
                            $variaciones = ['Monografía', 'Monografia'];
                            break;
                        case 'Personal Project PAI':
                            $variaciones = ['Proyecto Personal', 'Personal Project PAI'];
                            break;
                        case 'MUN TVS-Otros Colegios- GLY':
                            $variaciones = ['MUN TVS - Otros Colegios - GLY', 'MUN TVS-Otros Colegios- GLY'];
                            break;
                        case 'Preparación Pruebas saber':
                            $variaciones = ['Preparación Pruebas Saber', 'Preparación Pruebas saber'];
                            break;
                    }
                    
                    foreach ($variaciones as $variacion) {
                        $itemsVariacion = PresupuestoItem::where('rubro', $variacion)->get();
                        if ($itemsVariacion->count() > 0) {
                            $totalPresupuesto += $itemsVariacion->sum('presupuesto') ?? 0;
                            $totalEjecutado += $itemsVariacion->sum('valor') ?? 0;
                        }
                    }
                }
            }
            
            $totalPorEjecutar = $totalPresupuesto - $totalEjecutado;
            
            $resumen[$conceptoResumen] = [
                'presupuesto' => $totalPresupuesto,
                'ejecutado' => $totalEjecutado,
                'por_ejecutar' => $totalPorEjecutar
            ];
        }
        
        return $resumen;
    }

    /**
     * Load more data for spreadsheet via AJAX
     */
    public function loadMoreData(Request $request)
    {
        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 500);
        $sheet = $request->get('sheet', 'Detallado secciones1');
        
        if ($sheet !== 'Detallado secciones1') {
            return response()->json(['data' => [], 'hasMore' => false]);
        }
        
        $presupuestoItems = PresupuestoItem::orderBy('seccion')
            ->orderByRaw("CASE WHEN rubro = 'TOTAL' THEN 1 ELSE 0 END")
            ->orderBy('rubro')
            ->orderBy('fecha')
            ->orderBy('cuenta')
            ->skip($offset)
            ->take($limit)
            ->get();
            
        $data = [];
        $row = $offset + 2; // +2 porque empezamos desde fila 2 (fila 1 son encabezados)
        
        foreach ($presupuestoItems as $item) {
            $fecha = '';
            if ($item->fecha) {
                try {
                    $fecha = $item->fecha->format('Y/m/d');
                } catch (Exception $e) {
                    $fecha = $item->fecha;
                }
            }
            
            $valor = $item->valor_formatted ?? '$ 0,00 ';
            
            $rowData = [];
            $rowData[] = [$row, 1, $item->fuente ?? ''];
            $rowData[] = [$row, 2, $item->documento ?? ''];
            $rowData[] = [$row, 3, $fecha];
            $rowData[] = [$row, 4, $item->cuenta ?? ''];
            $rowData[] = [$row, 5, $item->seccion ?? ''];
            $rowData[] = [$row, 6, $item->rubro ?? ''];
            $rowData[] = [$row, 7, trim($item->descripcion ?? '')];
            $rowData[] = [$row, 8, $valor];
            $rowData[] = [$row, 9, $item->valor_moneda ?? '$ 0,00'];
            $rowData[] = [$row, 10, $item->cliente_proveedor ?? ''];
            $rowData[] = [$row, 11, trim($item->nombre_cliente_proveedor ?? '')];
            $rowData[] = [$row, 12, $item->tercero ?? ''];
            $rowData[] = [$row, 13, trim($item->nombre_tercero ?? '')];
            $rowData[] = [$row, 14, $item->auxiliar ?? ''];
            $rowData[] = [$row, 15, $item->centro_costo ?? ''];
            
            $data = array_merge($data, $rowData);
            $row++;
        }
        
        $totalCount = PresupuestoItem::count();
        $hasMore = ($offset + $limit) < $totalCount;
        
        return response()->json([
            'data' => $data,
            'hasMore' => $hasMore,
            'totalCount' => $totalCount,
            'loadedCount' => $offset + $presupuestoItems->count()
        ]);
    }

    /**
     * Display the processed budget items.
     *
     * @return \Illuminate\Http\Response
     */
    public function items(Request $request)
    {
        // Verificación adicional de autorización
        if (!auth()->user()->can('admin')) {
            abort(403, 'No tiene permisos para acceder a esta sección.');
        }

        // Filtros
        $seccion = $request->get('seccion');
        $rubro = $request->get('rubro');
        $fecha_inicio = $request->get('fecha_inicio');
        $fecha_fin = $request->get('fecha_fin');

        // Query base
        $query = PresupuestoItem::query();

        // Aplicar filtros
        if ($seccion) {
            $query->porSeccion($seccion);
        }

        if ($rubro) {
            $query->porRubro($rubro);
        }

        if ($fecha_inicio && $fecha_fin) {
            $query->porFechas($fecha_inicio, $fecha_fin);
        }

        // Obtener datos paginados
        $items = $query->orderBy('seccion')
                      ->orderBy('created_at')
                      ->paginate(100);
        
        // Obtener estadísticas
        $estadisticas = $this->getEstadisticas();
        
        // Obtener filtros únicos para los dropdowns
        $secciones = PresupuestoItem::select('seccion')
                                   ->distinct()
                                   ->whereNotNull('seccion')
                                   ->pluck('seccion');
        
        $rubros = PresupuestoItem::select('rubro')
                                ->distinct()
                                ->whereNotNull('rubro')
                                ->pluck('rubro');

        return view('presupuesto.items', compact('items', 'estadisticas', 'secciones', 'rubros'));
    }

    /**
     * Upload and process Excel file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        // Verificación de autorización
        if (!auth()->user()->can('admin')) {
            abort(403, 'No tiene permisos para realizar esta acción.');
        }

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
        ], [
            'excel_file.required' => 'Debe seleccionar un archivo Excel.',
            'excel_file.mimes' => 'El archivo debe ser de formato Excel (.xlsx o .xls).',
            'excel_file.max' => 'El archivo no puede ser mayor a 10MB.',
        ]);

        try {
            $file = $request->file('excel_file');
            $filePath = $file->store('temp');
            
            // Procesar archivo
            $result = $this->processorService->processExcelData(storage_path('app/' . $filePath));
            
            // Limpiar datos existentes si se solicita
            if ($request->boolean('replace_existing')) {
                PresupuestoItem::truncate();
            }
            
            // Guardar datos procesados
            $savedCount = 0;
            foreach ($result['data'] as $item) {
                if (!empty($item['descripcion']) || $item['es_total'] || !empty($item['valor'])) {
                    PresupuestoItem::create($item);
                    $savedCount++;
                }
            }
            
            // Limpiar archivo temporal
            Storage::delete($filePath);
            
            $message = sprintf(
                'Archivo procesado correctamente. %d registros importados. Total procesado: $%s',
                $savedCount,
                number_format($result['statistics']['total_value'], 0)
            );
            
            return redirect()->route('presupuesto.items')
                           ->with('success', $message);
            
        } catch (Exception $e) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error al procesar archivo: ' . $e->getMessage());
        }
    }

    /**
     * Export processed data to Excel.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportItems()
    {
        // Verificación de autorización
        if (!auth()->user()->can('admin')) {
            abort(403, 'No tiene permisos para realizar esta acción.');
        }

        try {
            return Excel::download(new PresupuestoExport, 'presupuesto_procesado.xlsx');
        } catch (Exception $e) {
            return redirect()->back()
                           ->with('error', 'Error al exportar datos: ' . $e->getMessage());
        }
    }

    /**
     * Delete all processed items.
     *
     * @return \Illuminate\Http\Response
     */
    public function clearData()
    {
        // Verificación de autorización
        if (!auth()->user()->can('admin')) {
            abort(403, 'No tiene permisos para realizar esta acción.');
        }

        try {
            $count = PresupuestoItem::count();
            PresupuestoItem::truncate();
            
            return redirect()->back()
                           ->with('success', "Se eliminaron {$count} registros correctamente.");
                           
        } catch (Exception $e) {
            return redirect()->back()
                           ->with('error', 'Error al limpiar datos: ' . $e->getMessage());
        }
    }

    /**
     * Get statistics for dashboard.
     *
     * @return array
     */
    private function getEstadisticas()
    {
        return [
            'total_registros' => PresupuestoItem::sinTotales()->count(),
            'total_valor' => PresupuestoItem::sinTotales()->sum('valor'),
            'por_seccion' => PresupuestoItem::sinTotales()
                                          ->selectRaw('seccion, COUNT(*) as cantidad, SUM(valor) as total')
                                          ->groupBy('seccion')
                                          ->orderBy('total', 'desc')
                                          ->get(),
            'por_rubro' => PresupuestoItem::sinTotales()
                                        ->selectRaw('rubro, COUNT(*) as cantidad, SUM(valor) as total')
                                        ->groupBy('rubro')
                                        ->orderBy('total', 'desc')
                                        ->limit(10)
                                        ->get(),
            'ultimas_cargas' => PresupuestoItem::latest()
                                             ->limit(5)
                                             ->get(),
        ];
    }

    /**
     * Update the total value for a specific section.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateTotal(Request $request)
    {
        // Verificación de autorización
        if (!auth()->user()->can('admin')) {
            abort(403, 'No tiene permisos para realizar esta acción.');
        }

        $request->validate([
            'section' => 'required|string',
            'total' => 'required|numeric'
        ]);

        try {
            $section = $request->input('section');
            $newTotal = $request->input('total');

            // Buscar la fila TOTAL para esta sección
            $totalRow = PresupuestoItem::where('seccion', $section)
                                      ->where('rubro', 'TOTAL')
                                      ->first();

            if ($totalRow) {
                // Actualizar el valor total
                $totalRow->valor = $newTotal;
                $totalRow->save();

                return response()->json([
                    'success' => true,
                    'message' => "Total actualizado para la sección {$section}",
                    'section' => $section,
                    'new_total' => $newTotal,
                    'formatted_total' => $totalRow->valor_formatted
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "No se encontró la fila TOTAL para la sección {$section}"
                ], 404);
            }

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el total: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a specific cell value in the spreadsheet.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateCell(Request $request)
    {
        // Verificación de autorización
        if (!auth()->user()->can('admin')) {
            abort(403, 'No tiene permisos para realizar esta acción.');
        }

        $request->validate([
            'row' => 'required|integer',
            'column' => 'required|string',
            'value' => 'required',
            'item_id' => 'nullable|integer'
        ]);

        try {
            $row = $request->input('row');
            $column = $request->input('column');
            $value = $request->input('value');
            $itemId = $request->input('item_id');

            // Determinar qué campo actualizar basándose en la columna
            $fieldMap = [
                '1' => 'fuente',
                '2' => 'documento',
                '3' => 'fecha',
                '4' => 'cuenta',
                '5' => 'seccion',
                '6' => 'rubro',
                '7' => 'descripcion',
                '8' => 'valor',
                '9' => 'valor_moneda',
                '10' => 'cliente_proveedor',
                '11' => 'nombre_cliente_proveedor',
                '12' => 'tercero',
                '13' => 'nombre_tercero',
                '14' => 'auxiliar',
                '15' => 'centro_costo'
            ];

            if (!isset($fieldMap[$column])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Columna no válida'
                ], 400);
            }

            $fieldName = $fieldMap[$column];

            // Si no se proporciona item_id, intentar encontrar el item por posición
            if (!$itemId) {
                // Para encontrar el item correcto, necesitamos replicar la lógica del controlador
                $presupuestoItems = PresupuestoItem::orderBy('seccion')
                    ->orderByRaw("CASE WHEN rubro = 'TOTAL' THEN 1 ELSE 0 END")
                    ->orderBy('rubro')
                    ->orderBy('fecha')
                    ->orderBy('cuenta')
                    ->get();

                // La fila 1 son encabezados, así que restamos 2 para obtener el índice del array
                $itemIndex = $row - 2;
                
                if ($itemIndex >= 0 && $itemIndex < $presupuestoItems->count()) {
                    $item = $presupuestoItems[$itemIndex];
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Registro no encontrado'
                    ], 404);
                }
            } else {
                $item = PresupuestoItem::find($itemId);
                if (!$item) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Registro no encontrado'
                    ], 404);
                }
            }

            // Actualizar el campo
            if ($fieldName === 'fecha' && $value) {
                // Manejar formato de fecha
                try {
                    $item->fecha = \Carbon\Carbon::createFromFormat('Y/m/d', $value);
                } catch (Exception $e) {
                    $item->fecha = $value;
                }
            } elseif ($fieldName === 'valor') {
                // Manejar formato de valor monetario
                $numericValue = $this->parseMonetaryValue($value);
                $item->valor = $numericValue;
            } else {
                $item->$fieldName = $value;
            }

            $item->save();

            return response()->json([
                'success' => true,
                'message' => 'Celda actualizada correctamente',
                'item_id' => $item->id,
                'field' => $fieldName,
                'new_value' => $value
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la celda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse monetary value from formatted string to numeric value.
     *
     * @param  string  $value
     * @return float
     */
    private function parseMonetaryValue($value)
    {
        if (!$value || $value === '') return 0;
        
        // Remover símbolo de peso, espacios y puntos de miles
        $cleanValue = preg_replace('/[\$\s\.]/', '', $value);
        
        // Reemplazar coma decimal por punto
        $cleanValue = str_replace(',', '.', $cleanValue);
        
        // Convertir a número
        $numericValue = floatval($cleanValue);
        
        return $numericValue;
    }

    /**
     * Cargar datos guardados del spreadsheet desde la base de datos
     */
    private function loadSpreadsheetData()
    {
        // Obtener todos los datos guardados organizados por tabla
        $allData = PresupuestoSpreadsheet::orderBy('tabla_nombre')
                                        ->orderBy('fila_orden')
                                        ->orderBy('columna_orden')
                                        ->get();

        // Organizar datos por tabla -> concepto -> columna
        $organizedData = [];
        
        foreach ($allData as $registro) {
            $tabla = $registro->tabla_nombre;
            $concepto = $registro->concepto;
            $columna = $registro->columna;
            
            if (!isset($organizedData[$tabla])) {
                $organizedData[$tabla] = [];
            }
            
            if (!isset($organizedData[$tabla][$concepto])) {
                $organizedData[$tabla][$concepto] = [];
            }
            
            $organizedData[$tabla][$concepto][$columna] = [
                'valor' => $registro->valor,
                'es_total' => $registro->es_total,
                'tipo_dato' => $registro->tipo_dato,
                'id' => $registro->id
            ];
        }

        return $organizedData;
    }

    /**
     * Get available months from PresupuestoItem dates
     */
    private function getAvailableMonths()
    {
        $months = PresupuestoItem::whereNotNull('fecha')
            ->selectRaw('DISTINCT DATE_FORMAT(fecha, "%Y-%m") as mes_anio')
            ->selectRaw('DATE_FORMAT(fecha, "%M") as mes_nombre_en')
            ->selectRaw('MONTH(fecha) as mes_numero')
            ->orderBy('mes_anio')
            ->get();

        // Mapear nombres de meses al español
        $mesesEspanol = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        return $months->map(function ($month) use ($mesesEspanol) {
            return [
                'value' => $month->mes_anio,
                'label' => $mesesEspanol[$month->mes_numero] . ' ' . substr($month->mes_anio, 0, 4),
                'numero' => $month->mes_numero
            ];
        })->toArray();
    }

    /**
     * Filter section data by month via AJAX
     */
    public function filterSectionsByMonth(Request $request)
    {
        $month = $request->input('month');
        
        // Obtener datos filtrados por mes
        $seccionesData = $this->getSectionData($month);
        
        // Devolver solo las secciones principales que se muestran en la vista
        $sectionsToReturn = [
            'PREESCOLAR Y PRIMARIA' => $seccionesData['PREESCOLAR Y PRIMARIA'] ?? [],
            'ESCUELA MEDIA' => $seccionesData['ESCUELA MEDIA'] ?? [],
            'ALTA' => $seccionesData['ALTA'] ?? []
        ];
        
        return response()->json([
            'success' => true,
            'data' => $sectionsToReturn,
            'month' => $month
        ]);
    }

    /**
     * Mostrar formulario para configurar presupuesto de secciones
     */
    public function configurarSecciones()
    {
        // Verificación de autorización
        if (!auth()->user()->can('admin')) {
            abort(403, 'No tiene permisos para realizar esta acción.');
        }

        $year = date('Y');
        $secciones = [
            'PREESCOLAR Y PRIMARIA', 
            'ESCUELA MEDIA', 
            'ALTA',
            'PAI',
            'PEP',
            'DEPORTES',
            'BIBLIOTECA',
            'PSICOLOGÍA INSTITUCIONAL',
            'CAS',
            'CONSEJERÍA UNIVERSITARIA',
            'DEPARTAMENTO DE APOYO'
        ];
        
        // Obtener presupuestos actuales
        $presupuestosActuales = PresupuestoSeccion::obtenerTodosPresupuestos($year);
        
        return view('presupuesto.configurar-secciones', compact('secciones', 'presupuestosActuales', 'year'));
    }

    /**
     * Guardar configuración de presupuesto de secciones
     */
    public function guardarPresupuestoSecciones(Request $request)
    {
        // Verificación de autorización
        if (!auth()->user()->can('admin')) {
            abort(403, 'No tiene permisos para realizar esta acción.');
        }

        $request->validate([
            'presupuestos' => 'required|array',
            'presupuestos.*' => 'required|numeric|min:0',
            'year' => 'required|integer|min:2020|max:2030'
        ]);

        try {
            $year = $request->input('year');
            $presupuestos = $request->input('presupuestos');

            foreach ($presupuestos as $seccion => $monto) {
                PresupuestoSeccion::updateOrCreate(
                    [
                        'seccion' => $seccion,
                        'year' => $year
                    ],
                    [
                        'presupuesto_total' => $monto,
                        'activo' => true
                    ]
                );
            }

            return redirect()->route('presupuesto.configurar-secciones')
                           ->with('success', 'Presupuestos de secciones actualizados correctamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error al guardar presupuestos: ' . $e->getMessage());
        }
    }
}
