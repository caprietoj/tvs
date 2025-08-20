<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\BudgetExecution;
use Illuminate\Support\Facades\DB;

class PresupuestoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
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
}
