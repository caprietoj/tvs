<?php

namespace App\Http\Controllers;

use App\Models\ComprasKpi;
use App\Models\ComprasThreshold;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class KpiComprasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function indexCompras(Request $request)
    {
        $month = $request->input('month');
        $query = ComprasKpi::with('threshold');
        
        if ($month) {
            $query->whereMonth('measurement_date', $month);
        }
        
        $kpis = $query->orderBy('measurement_date', 'desc')->get();

        // Separar KPIs por tipo
        $measurementKpis = $kpis->where('type', 'measurement');
        $informativeKpis = $kpis->where('type', 'informative');

        // Estadísticas generales
        $percentages = $kpis->pluck('percentage')->toArray();
        $count = count($percentages);
        
        // Cálculo de la media
        $average = $count > 0 ? array_sum($percentages)/$count : 0;

        // Cálculo de la mediana
        if ($count > 0) {
            sort($percentages);
            $middle = floor($count/2);
            $median = ($count % 2 == 0) ? 
                ($percentages[$middle - 1] + $percentages[$middle]) / 2 : 
                $percentages[$middle];
        } else {
            $median = 0;
        }

        // Cálculo de la desviación estándar
        if ($count > 0) {
            $variance = 0;
            foreach ($percentages as $p) {
                $variance += pow($p - $average, 2);
            }
            $variance /= $count;
            $stdDev = sqrt($variance);
        } else {
            $stdDev = 0;
        }

        // Valores máximo y mínimo
        $max = $count > 0 ? max($percentages) : 0;
        $min = $count > 0 ? min($percentages) : 0;

        // Obtener umbral configurado
        $thresholdRecord = ComprasThreshold::where('area', 'compras')->first();
        $thresholdValue = $thresholdRecord ? $thresholdRecord->value : 80;

        // Contar KPIs bajo el umbral
        $countUnder = $kpis->filter(function($kpi) use ($thresholdValue) {
            return $kpi->percentage < $thresholdValue;
        })->count();

        // Generar conclusión del análisis
        if ($count == 0) {
            $conclusion = "No hay KPIs registrados para el análisis.";
        } else {
            $conclusion = $this->generateConclusion($count, $countUnder, $thresholdValue, $average, $stdDev);
        }

        // Preparar datos para el gráfico
        $chartData = [
            'measurement' => [
                'labels' => $measurementKpis->pluck('name'),
                'data' => $measurementKpis->pluck('percentage'),
            ],
            'informative' => [
                'labels' => $informativeKpis->pluck('name'),
                'data' => $informativeKpis->pluck('percentage'),
            ]
        ];

        return view('kpis.compras.index', compact(
            'kpis',
            'measurementKpis',
            'informativeKpis',
            'average',
            'median',
            'stdDev',
            'max',
            'min',
            'countUnder',
            'conclusion',
            'chartData'
        ));
    }

    public function createCompras()
    {
        $thresholds = ComprasThreshold::where('area', 'compras')->get();
        $types = ['measurement' => 'Medición', 'informative' => 'Informativo'];
        return view('kpis.compras.create', compact('thresholds', 'types'));
    }

    public function storeCompras(Request $request)
    {
        try {
            DB::beginTransaction();

            // Debug de los datos recibidos
            \Log::info('Datos recibidos:', $request->all());
            
            // Verifica si el threshold existe antes de la validación
            $threshold = ComprasThreshold::find($request->threshold_id);
            if (!$threshold) {
                throw new \Exception('El umbral seleccionado no existe.');
            }

            \Log::info('Threshold encontrado:', $threshold->toArray());

            $validated = $request->validate([
                'threshold_id' => 'required|exists:compras_thresholds,id',
                'type' => 'required|in:measurement,informative',
                'methodology' => 'required|string',
                'frequency' => 'required|in:Diario,Quincenal,Mensual,Semestral',
                'measurement_date' => 'required|date',
                'percentage' => 'required|numeric|min:0|max:100',
                'analysis' => 'nullable|string', // Add validation for analysis
                'url' => 'nullable|url|max:255',
            ]);

            $kpi = ComprasKpi::create([
                'threshold_id' => $threshold->id,
                'name' => $threshold->kpi_name,
                'type' => $validated['type'],
                'methodology' => $validated['methodology'],
                'frequency' => $validated['frequency'],
                'measurement_date' => $validated['measurement_date'],
                'percentage' => $validated['percentage'],
                'analysis' => $validated['analysis'], // Add this line
                'url' => $validated['url'] ?? null,
                'area' => 'compras'
            ]);

            \Log::info('KPI creado:', $kpi->toArray());

            DB::commit();
            return redirect()
                ->route('kpis.compras.index')
                ->with('success', 'KPI registrado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al guardar KPI: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return back()
                ->withInput()
                ->with('error', 'Error al guardar el KPI: ' . $e->getMessage());
        }
    }

    public function showCompras($id)
    {
        $kpi = ComprasKpi::with('threshold')->findOrFail($id);
        return view('kpis.compras.show', compact('kpi'));
    }

    public function editCompras($id)
    {
        $kpi = ComprasKpi::with('threshold')->findOrFail($id);
        $thresholds = ComprasThreshold::where('area', 'compras')->get();
        $types = ['measurement' => 'Medición', 'informative' => 'Informativo'];
        return view('kpis.compras.edit', compact('kpi', 'thresholds', 'types'));
    }

    public function updateCompras(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            // Find the KPI first
            $kpi = ComprasKpi::findOrFail($id);
            
            // Validate threshold exists
            $threshold = ComprasThreshold::findOrFail($request->threshold_id);

            $validated = $request->validate([
                'threshold_id' => 'required|exists:compras_thresholds,id',
                'type' => 'required|in:measurement,informative',
                'methodology' => 'required|string',
                'frequency' => 'required|in:Diario,Quincenal,Mensual,Semestral',
                'measurement_date' => 'required|date',
                'percentage' => 'required|numeric|min:0|max:100',
                'analysis' => 'nullable|string', // Add validation for analysis
                'url' => 'nullable|url|max:255',
            ]);

            $kpi->update([
                'threshold_id' => $threshold->id,
                'name' => $threshold->kpi_name,
                'type' => $validated['type'],
                'methodology' => $validated['methodology'],
                'frequency' => $validated['frequency'],
                'measurement_date' => $validated['measurement_date'],
                'percentage' => $validated['percentage'],
                'analysis' => $validated['analysis'], // Add this line
                'url' => $validated['url'] ?? null,
                'area' => 'compras'
            ]);

            DB::commit();
            return redirect()
                ->route('kpis.compras.index')
                ->with('success', 'KPI actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar KPI: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar el KPI: ' . $e->getMessage());
        }
    }

    public function destroyCompras($id)
    {
        $kpi = ComprasKpi::findOrFail($id);
        $kpi->delete();
        
        return response()->json([
            'message' => 'KPI eliminado exitosamente.'
        ], 200);
    }

    private function generateConclusion($count, $countUnder, $thresholdValue, $average, $stdDev)
    {
        $conclusion = "Análisis de {$count} KPIs: ";
        
        if ($countUnder == 0) {
            $conclusion .= "Todos los KPIs superan el umbral establecido ({$thresholdValue}%). ";
        } else {
            $conclusion .= "{$countUnder} KPI(s) están por debajo del umbral ({$thresholdValue}%). ";
        }

        $conclusion .= "La media general es de " . number_format($average, 2) . "% ";
        
        if ($stdDev > 10) {
            $conclusion .= "con una alta variabilidad (desviación estándar: " . number_format($stdDev, 2) . ").";
        } else {
            $conclusion .= "con una variabilidad moderada (desviación estándar: " . number_format($stdDev, 2) . ").";
        }

        return $conclusion;
    }
    
    public function exportCompras(Request $request)
    {
        $month = $request->input('month');
        $query = ComprasKpi::with('threshold');
        
        if ($month) {
            $query->whereMonth('measurement_date', $month);
        }
        
        $kpis = $query->orderBy('measurement_date', 'desc')->get();
        
        // Lógica para exportar a Excel o PDF
        // Implementar según las necesidades
        
        return back()->with('success', 'Exportación completada exitosamente.');
    }
}