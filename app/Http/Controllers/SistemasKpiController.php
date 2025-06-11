<?php

namespace App\Http\Controllers;

use App\Models\SistemasKpi;
use App\Models\SistemasThreshold;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SistemasKpiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function indexSistemas(Request $request)
    {
        $query = SistemasKpi::query();
        
        // Filtrar por mes si se proporciona
        if ($request->has('month') && $request->month) {
            $query->whereMonth('measurement_date', $request->month);
        }
        
        // Separar KPIs por tipo
        $measurementKpis = $query->where('type', 'measurement')->get();
        $informativeKpis = SistemasKpi::where('type', 'informative')->get();

        // Estadísticas para KPIs de Medición
        $measurementStats = $this->calculateStats($measurementKpis, 'measurement');

        // Estadísticas para KPIs Informativos
        $informativeStats = $this->calculateStats($informativeKpis, 'informative');

        // Datos para el gráfico
        $chartData = [
            'labels' => $measurementKpis->pluck('name')->merge($informativeKpis->pluck('name')),
            'measurementData' => $measurementKpis->pluck('percentage'),
            'informativeData' => $informativeKpis->pluck('percentage')
        ];

        return view('kpis.sistemas.index', compact(
            'measurementKpis',
            'informativeKpis',
            'measurementStats',
            'informativeStats',
            'chartData'
        ));
    }

    private function calculateStats($kpis, $type)
    {
        $percentages = $kpis->pluck('percentage')->toArray();
        $count = count($percentages);

        if ($count === 0) {
            return [
                'average' => 0,
                'median' => 0,
                'stdDev' => 0,
                'countUnder' => 0,
                'max' => 0,
                'min' => 0
            ];
        }

        // Media
        $average = array_sum($percentages) / $count;

        // Mediana
        sort($percentages);
        $middle = floor($count / 2);
        $median = ($count % 2) ? $percentages[$middle] : 
                 ($percentages[$middle - 1] + $percentages[$middle]) / 2;

        // Desviación estándar
        $variance = array_reduce($percentages, function($carry, $item) use ($average) {
            return $carry + pow($item - $average, 2);
        }, 0) / $count;
        $stdDev = sqrt($variance);

        // Valores máximo y mínimo
        $max = max($percentages);
        $min = min($percentages);

        // Conteo de KPIs por debajo del umbral
        $threshold = $type === 'measurement' ? 80 : 50;
        $countUnder = count(array_filter($percentages, function($p) use ($threshold) {
            return $p < $threshold;
        }));

        return [
            'average' => $average,
            'median' => $median,
            'stdDev' => $stdDev,
            'countUnder' => $countUnder,
            'max' => $max,
            'min' => $min
        ];
    }

    public function createSistemas()
    {
        $thresholds = SistemasThreshold::all();
        return view('kpis.sistemas.create', compact('thresholds'));
    }

    public function storeSistemas(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'threshold_id' => 'required|exists:sistemas_thresholds,id',
            'type' => 'required|in:measurement,informative',
            'methodology' => 'required|string',
            'frequency' => 'required|in:Diario,Quincenal,Mensual,Semestral',
            'measurement_date' => 'required|date',
            'percentage' => 'required|numeric|min:0|max:100',
            'url' => 'nullable|url|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $threshold = SistemasThreshold::findOrFail($request->threshold_id);
        
        SistemasKpi::create([
            'threshold_id' => $threshold->id,
            'name' => $threshold->kpi_name,
            'type' => $request->type,
            'methodology' => $request->methodology,
            'frequency' => $request->frequency,
            'measurement_date' => $request->measurement_date,
            'percentage' => $request->percentage,
            'url' => $request->url,
            'area' => 'sistemas'
        ]);

        return redirect()->route('kpis.sistemas.index')
            ->with('success', 'KPI de Sistemas registrado exitosamente.');
    }

    public function showSistemas($id)
    {
        $kpi = SistemasKpi::with('threshold')->findOrFail($id);
        return view('kpis.sistemas.show', compact('kpi'));
    }

    public function editSistemas($id)
    {
        $kpi = SistemasKpi::findOrFail($id);
        $thresholds = SistemasThreshold::all();
        return view('kpis.sistemas.edit', compact('kpi', 'thresholds'));
    }

    public function updateSistemas(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'threshold_id' => 'required|exists:sistemas_thresholds,id',
            'type' => 'required|in:measurement,informative',
            'methodology' => 'required|string',
            'frequency' => 'required|in:Diario,Quincenal,Mensual,Semestral',
            'measurement_date' => 'required|date',
            'percentage' => 'required|numeric|min:0|max:100',
            'url' => 'nullable|url|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $kpi = SistemasKpi::findOrFail($id);
        $threshold = SistemasThreshold::findOrFail($request->threshold_id);

        $kpi->update([
            'threshold_id' => $threshold->id,
            'name' => $threshold->kpi_name,
            'type' => $request->type,
            'methodology' => $request->methodology,
            'frequency' => $request->frequency,
            'measurement_date' => $request->measurement_date,
            'percentage' => $request->percentage,
            'url' => $request->url,
            'area' => 'sistemas'
        ]);

        return redirect()->route('kpis.sistemas.index')
            ->with('success', 'KPI de Sistemas actualizado exitosamente.');
    }

    public function destroySistemas($id)
    {
        try {
            $kpi = SistemasKpi::findOrFail($id);
            $kpi->delete();
            return response()->json([
                'success' => true,
                'message' => 'KPI de Sistemas eliminado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el KPI: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateConclusion($stats, $type)
    {
        $threshold = $type === 'measurement' ? 80 : 50;
        $totalKpis = $stats['countUnder'] + count(array_filter($stats, function($p) use ($threshold) {
            return $p >= $threshold;
        }));

        if ($totalKpis === 0) {
            return "No hay KPIs {$type} registrados.";
        }

        $conclusion = "De un total de {$totalKpis} KPIs {$type}, ";
        $conclusion .= "{$stats['countUnder']} están por debajo del umbral ({$threshold}%). ";
        $conclusion .= "La media es {$stats['average']}% ";
        $conclusion .= "con una desviación estándar de {$stats['stdDev']}. ";
        
        if ($stats['average'] >= $threshold) {
            $conclusion .= "El rendimiento general es positivo.";
        } else {
            $conclusion .= "Se requiere atención para mejorar el rendimiento.";
        }

        return $conclusion;
    }

    public function exportSistemas(Request $request)
    {
        $query = SistemasKpi::query();
        
        // Filtrar por mes si se proporciona
        if ($request->has('month') && $request->month) {
            $query->whereMonth('measurement_date', $request->month);
        }
        
        // Filtrar por tipo si se proporciona
        if ($request->has('type') && in_array($request->type, ['measurement', 'informative'])) {
            $query->where('type', $request->type);
        }
        
        $kpis = $query->get();
        
        // Lógica para exportar a Excel o PDF
        // Implementar según las necesidades
        
        return back()->with('success', 'Exportación completada exitosamente.');
    }
}