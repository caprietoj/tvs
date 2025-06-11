<?php

namespace App\Http\Controllers;

use App\Models\ContabilidadKpi;
use App\Models\ContabilidadThreshold;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContabilidadKpiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function indexContabilidad(Request $request)
    {
        // Separar KPIs por tipo
        $measurementKpis = ContabilidadKpi::where('type', 'measurement')->get();
        $informativeKpis = ContabilidadKpi::where('type', 'informative')->get();

        // Estadísticas para KPIs de Medición
        $measurementStats = $this->calculateStats($measurementKpis);
        
        // Estadísticas para KPIs Informativos
        $informativeStats = $this->calculateStats($informativeKpis);

        // Datos para el gráfico
        $chartData = [
            'labels' => $measurementKpis->pluck('name')->merge($informativeKpis->pluck('name')),
            'measurementData' => $measurementKpis->pluck('percentage'),
            'informativeData' => $informativeKpis->pluck('percentage')
        ];

        return view('kpis.contabilidad.index', compact(
            'measurementKpis',
            'informativeKpis',
            'measurementStats',
            'informativeStats',
            'chartData'
        ));
    }

    private function calculateStats($kpis)
    {
        $percentages = $kpis->pluck('percentage')->toArray();
        $count = count($percentages);

        if ($count === 0) {
            return [
                'average' => 0,
                'median' => 0,
                'stdDev' => 0,
                'max' => 0,
                'min' => 0,
                'countUnder' => 0,
                'conclusion' => "No hay KPIs registrados."
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

        // KPIs por debajo del umbral
        $threshold = $kpis->first()->type === 'measurement' ? 80 : 50;
        $countUnder = count(array_filter($percentages, function($p) use ($threshold) {
            return $p < $threshold;
        }));

        // Generar conclusión
        $conclusion = $this->generateConclusion([
            'count' => $count,
            'countUnder' => $countUnder,
            'average' => $average,
            'threshold' => $threshold,
            'type' => $kpis->first()->type
        ]);

        return compact('average', 'median', 'stdDev', 'max', 'min', 'countUnder', 'conclusion');
    }

    private function generateConclusion($data)
    {
        $typeLabel = $data['type'] === 'measurement' ? 'de Medición' : 'Informativos';
        
        if ($data['count'] === 0) {
            return "No hay KPIs {$typeLabel} registrados.";
        }

        $conclusion = "De un total de {$data['count']} KPIs {$typeLabel}, ";
        $conclusion .= "{$data['countUnder']} están por debajo del umbral ({$data['threshold']}%). ";
        $conclusion .= "La media es " . number_format($data['average'], 2) . "%. ";

        if ($data['average'] >= $data['threshold']) {
            $conclusion .= "El rendimiento general es positivo.";
        } else {
            $conclusion .= "Se requiere atención para mejorar el rendimiento.";
        }

        return $conclusion;
    }

    public function createContabilidad()
    {
        $thresholds = ContabilidadThreshold::all();
        return view('kpis.contabilidad.create', compact('thresholds'));
    }

    public function storeContabilidad(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'threshold_id' => 'required|exists:contabilidad_thresholds,id',
            'type' => 'required|in:measurement,informative',
            'methodology' => 'required|string',
            'frequency' => 'required|in:Diario,Quincenal,Mensual,Semestral',
            'measurement_date' => 'required|date',
            'percentage' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $threshold = ContabilidadThreshold::findOrFail($request->threshold_id);
        
        ContabilidadKpi::create([
            'threshold_id' => $threshold->id,
            'name' => $threshold->kpi_name,
            'type' => $request->type,
            'methodology' => $request->methodology,
            'frequency' => $request->frequency,
            'measurement_date' => $request->measurement_date,
            'percentage' => $request->percentage,
            'area' => 'contabilidad'
        ]);

        return redirect()->route('kpis.contabilidad.index')
            ->with('success', 'KPI de Contabilidad registrado exitosamente.');
    }

    public function showContabilidad($id)
    {
        $kpi = ContabilidadKpi::with('threshold')->findOrFail($id);
        return view('kpis.contabilidad.show', compact('kpi'));
    }

    public function editContabilidad($id)
    {
        $kpi = ContabilidadKpi::findOrFail($id);
        $thresholds = ContabilidadThreshold::all();
        return view('kpis.contabilidad.edit', compact('kpi', 'thresholds'));
    }

    public function updateContabilidad(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'threshold_id' => 'required|exists:contabilidad_thresholds,id',
            'type' => 'required|in:measurement,informative',
            'methodology' => 'required|string',
            'frequency' => 'required|in:Diario,Quincenal,Mensual,Semestral',
            'measurement_date' => 'required|date',
            'percentage' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $kpi = ContabilidadKpi::findOrFail($id);
        $threshold = ContabilidadThreshold::findOrFail($request->threshold_id);

        $kpi->update([
            'threshold_id' => $threshold->id,
            'name' => $threshold->kpi_name,
            'type' => $request->type,
            'methodology' => $request->methodology,
            'frequency' => $request->frequency,
            'measurement_date' => $request->measurement_date,
            'percentage' => $request->percentage,
            'area' => 'contabilidad'
        ]);

        return redirect()->route('kpis.contabilidad.index')
            ->with('success', 'KPI de Contabilidad actualizado exitosamente.');
    }

    public function destroyContabilidad($id)
    {
        try {
            $kpi = ContabilidadKpi::findOrFail($id);
            $kpi->delete();
            return response()->json([
                'success' => true,
                'message' => 'KPI de Contabilidad eliminado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el KPI: ' . $e->getMessage()
            ], 500);
        }
    }
}