<?php

namespace App\Http\Controllers;

use App\Models\EvaluacionProveedor;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluacionProveedorController extends Controller
{
    public function index()
    {
        $evaluaciones = EvaluacionProveedor::with('proveedor')->get();
        return view('evaluaciones.index', compact('evaluaciones'));
    }

    public function create()
    {
        $proveedores = Proveedor::all();
        $evaluador = Auth::user()->name;
        return view('evaluaciones.create', compact('proveedores', 'evaluador'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'proveedor_id' => 'required|exists:proveedors,id',
            'numero_contrato' => 'required|string',
            'fecha_evaluacion' => 'required|date',
            'lugar_evaluacion' => 'required|string',
            'cumplimiento_entrega' => 'required|numeric|between:0,5',
            'calidad_especificaciones' => 'required|numeric|between:0,5',
            'documentacion_garantias' => 'required|numeric|between:0,5',
            'servicio_postventa' => 'required|numeric|between:0,5',
            'precio' => 'required|numeric|between:0,5',
            'capacidad_instalada' => 'required|numeric|between:0,5',
            'soporte_tecnico' => 'required|numeric|between:0,5',
            'observaciones' => 'nullable|string',
            'evaluado_por' => 'required|string',
        ]);

        $evaluacion = new EvaluacionProveedor($request->all());
        $evaluacion->evaluado_por = Auth::user()->name;
        $evaluacion->puntaje_total = $evaluacion->calcularPuntajeTotal();
        $evaluacion->save();

        return redirect()->route('evaluaciones.index')
            ->with('success', 'Evaluación registrada exitosamente.');
    }

    public function show($id)
    {
        $evaluacion = EvaluacionProveedor::with('proveedor')->findOrFail($id);
        return view('evaluaciones.show', compact('evaluacion'));
    }

    public function edit($id)
    {
        $evaluacion = EvaluacionProveedor::with('proveedor')->findOrFail($id);
        $proveedores = Proveedor::all();
        return view('evaluaciones.edit', compact('evaluacion', 'proveedores'));
    }

    public function update(Request $request, $id)
    {
        $evaluacion = EvaluacionProveedor::findOrFail($id);
        
        $request->validate([
            'proveedor_id' => 'required|exists:proveedors,id',
            'numero_contrato' => 'required|string',
            'fecha_evaluacion' => 'required|date',
            'lugar_evaluacion' => 'required|string',
            'cumplimiento_entrega' => 'required|numeric|between:0,5',
            'calidad_especificaciones' => 'required|numeric|between:0,5',
            'documentacion_garantias' => 'required|numeric|between:0,5',
            'servicio_postventa' => 'required|numeric|between:0,5',
            'precio' => 'required|numeric|between:0,5',
            'capacidad_instalada' => 'required|numeric|between:0,5',
            'soporte_tecnico' => 'required|numeric|between:0,5',
            'observaciones' => 'nullable|string',
            'evaluado_por' => 'required|string',
        ]);

        $evaluacion->fill($request->all());
        $evaluacion->puntaje_total = $evaluacion->calcularPuntajeTotal();
        $evaluacion->save();

        return redirect()->route('evaluaciones.index')
            ->with('success', 'Evaluación actualizada exitosamente.');
    }

    /**
     * Provide summary data for supplier evaluations via API
     */
    public function getEvaluacionesData()
    {
        try {
            // Get all evaluations with their providers
            $evaluaciones = EvaluacionProveedor::with('proveedor')->get();
            
            // Process evaluations data
            $categories = [];
            $suppliers = [];
            
            foreach ($evaluaciones as $eval) {
                $category = $eval->proveedor->categoria ?? 'Sin categoría';
                
                // Initialize category if not exists
                if (!isset($categories[$category])) {
                    $categories[$category] = [
                        'count' => 0,
                        'totalScore' => 0
                    ];
                }
                
                // Calculate weighted score
                $score = ($eval->cumplimiento_entrega * 0.2) +
                        ($eval->calidad_especificaciones * 0.2) +
                        ($eval->documentacion_garantias * 0.15) +
                        ($eval->servicio_postventa * 0.15) +
                        ($eval->precio * 0.1) +
                        ($eval->capacidad_instalada * 0.1) +
                        ($eval->soporte_tecnico * 0.1);
                
                // Update category stats
                $categories[$category]['count']++;
                $categories[$category]['totalScore'] += $score;
                
                // Track supplier scores
                $supplierName = $eval->proveedor->nombre ?? 'Proveedor sin nombre';
                if (!isset($suppliers[$supplierName])) {
                    $suppliers[$supplierName] = [
                        'name' => $supplierName,
                        'category' => $category,
                        'score' => $score,
                        'count' => 1
                    ];
                } else {
                    $suppliers[$supplierName]['score'] += $score;
                    $suppliers[$supplierName]['count']++;
                }
            }
            
            // Calculate averages and format data
            $formattedCategories = [];
            $totalScore = 0;
            $totalCount = 0;
            
            foreach ($categories as $name => $stats) {
                $avgScore = $stats['count'] > 0 ? $stats['totalScore'] / $stats['count'] : 0;
                $formattedCategories[] = [
                    'name' => $name,
                    'count' => $stats['count'],
                    'avgScore' => $avgScore
                ];
                $totalScore += $stats['totalScore'];
                $totalCount += $stats['count'];
            }
            
            // Sort and get top suppliers
            $topSuppliers = collect($suppliers)->map(function($supplier) {
                $supplier['score'] = $supplier['count'] > 0 ? 
                    $supplier['score'] / $supplier['count'] : 0;
                return $supplier;
            })->sortByDesc('score')
              ->take(5)
              ->values()
              ->all();
            
            // Calculate overall statistics
            $overallAvgScore = $totalCount > 0 ? $totalScore / $totalCount : 0;
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'categories' => $formattedCategories,
                    'overall' => [
                        'totalSuppliers' => count($suppliers),
                        'totalEvaluations' => $totalCount,
                        'avgScore' => $overallAvgScore,
                        'overallPercentage' => ($overallAvgScore / 5) * 100
                    ],
                    'topSuppliers' => $topSuppliers
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error processing evaluation data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ...otros métodos del controlador...
}
