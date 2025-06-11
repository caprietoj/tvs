<?php

namespace App\Http\Controllers;

use App\Models\BudgetExecution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BudgetExecutionController extends Controller
{
    private $months = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];

    public function index(Request $request)
    {
        $query = BudgetExecution::query();
        
        if ($request->month) {
            $query->where('month', $request->month);
        }
        
        $budgets = $query->get();

        // Calcular el presupuesto único (valor fijo) agrupando por departamento
        $presupuestoUnico = BudgetExecution::select('department')
            ->selectRaw('MAX(budget_amount) as max_budget')
            ->groupBy('department')
            ->get()
            ->sum('max_budget');
        
        if ($budgets->isEmpty()) {
            // Si no hay datos, devolver valores por defecto
            return view('contabilidad.budget.index', [
                'budgets' => collect([]),
                'stats' => [
                    'promedio_ejecucion' => 0,
                    'desviacion_estandar' => 0,
                    'total_presupuesto' => 0,
                    'total_presupuesto_unico' => 0,
                    'total_ejecutado' => 0,
                    'max_ejecucion' => null,
                    'min_ejecucion' => null,
                    'departamentos_riesgo' => 0,
                    'departamentos_alto' => 0,
                    'departamentos_normal' => 0,
                    'departamentos_bajo' => 0,
                ],
                'chartData' => [
                    'labels' => [],
                    'execution' => [],
                    'budget' => [],
                    'executed' => [],
                ],
                'months' => $this->months
            ]);
        }

        // Si hay datos, calcular estadísticas
        $mean = $budgets->avg('execution_percentage');
        $variance = $budgets->map(function($item) use ($mean) {
            return pow($item->execution_percentage - $mean, 2);
        })->avg();
        
        $stats = [
            'promedio_ejecucion' => $mean,
            'desviacion_estandar' => sqrt($variance),
            'total_presupuesto' => $budgets->sum('budget_amount'),
            'total_presupuesto_unico' => $presupuestoUnico,
            'total_ejecutado' => $budgets->sum('executed_amount'),
            'max_ejecucion' => $budgets->sortByDesc('execution_percentage')->first() ?? null,
            'min_ejecucion' => $budgets->sortBy('execution_percentage')->first() ?? null,
            'departamentos_riesgo' => $budgets->where('execution_percentage', '>', 95)->count(),
            'departamentos_alto' => $budgets->whereBetween('execution_percentage', [85, 95])->count(),
            'departamentos_normal' => $budgets->whereBetween('execution_percentage', [50, 85])->count(),
            'departamentos_bajo' => $budgets->where('execution_percentage', '<', 50)->count(),
        ];

        $chartData = [
            'labels' => $budgets->pluck('department'),
            'execution' => $budgets->pluck('execution_percentage'),
            'budget' => $budgets->pluck('budget_amount'),
            'executed' => $budgets->pluck('executed_amount'),
        ];
        
        return view('contabilidad.budget.index', compact('budgets', 'chartData', 'stats'))
            ->with('months', $this->months);
    }

    public function create()
    {
        $departments = BudgetExecution::getDepartmentOptions();
        return view('contabilidad.budget.create', [
            'departments' => $departments,
            'months' => $this->months
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department' => 'required|string',
            'month' => 'required|string',
            'budget_amount' => 'required|numeric|min:0',
            'executed_amount' => 'required|numeric|min:0',
        ]);

        BudgetExecution::create($validated);

        return redirect()->route('budget.index')
            ->with('success', 'Presupuesto registrado exitosamente.');
    }
}