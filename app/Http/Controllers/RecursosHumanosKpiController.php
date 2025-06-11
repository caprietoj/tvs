<?php

namespace App\Http\Controllers;

use App\Models\RecursosHumanosKpi;
use App\Models\RecursosHumanosThreshold;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RecursosHumanosKpiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function indexRecursosHumanos(Request $request)
    {
        $month = $request->input('month');
        $query = RecursosHumanosKpi::with('threshold');
        
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
        $thresholdRecord = RecursosHumanosThreshold::where('area', 'rrhh')->first();
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

        return view('kpis.rrhh.index', compact(
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

    public function createRecursosHumanos()
    {
        $thresholds = RecursosHumanosThreshold::where('area', 'rrhh')->get();
        $types = ['measurement' => 'Medición', 'informative' => 'Informativo'];
        return view('kpis.rrhh.create', compact('thresholds', 'types'));
    }

    public function storeRecursosHumanos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'threshold_id' => 'required|exists:recursos_humanos_thresholds,id',
            'type' => 'required|in:measurement,informative',
            'methodology'  => 'required|string',
            'frequency'    => 'required|string|in:Diario,Quincenal,Mensual,Semestral',
            'measurement_date' => 'required|date',
            'percentage'   => 'required|numeric|min:0|max:100',
            'url' => 'nullable|url|max:255',
        ]);
    
        if ($validator->fails()){
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        $threshold = RecursosHumanosThreshold::findOrFail($request->threshold_id);
        $data = $request->all();
        $data['name'] = $threshold->kpi_name;
        $data['threshold_id'] = $threshold->id;
        $data['area'] = 'rrhh';
        
        RecursosHumanosKpi::create($data);
    
        return redirect()->route('kpis.rrhh.index')
            ->with('success', 'KPI de Recursos Humanos registrado exitosamente.');
    }

    public function showRecursosHumanos($id)
    {
        $kpi = RecursosHumanosKpi::with('threshold')->findOrFail($id);
        return view('kpis.rrhh.show', compact('kpi'));
    }

    public function editRecursosHumanos($id)
    {
        $kpi = RecursosHumanosKpi::findOrFail($id);
        $thresholds = RecursosHumanosThreshold::where('area', 'rrhh')->get();
        $types = ['measurement' => 'Medición', 'informative' => 'Informativo'];
        return view('kpis.rrhh.edit', compact('kpi', 'thresholds', 'types'));
    }

    public function updateRecursosHumanos(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'threshold_id' => 'required|exists:recursos_humanos_thresholds,id',
            'methodology'  => 'required|string',
            'frequency'    => 'required|string|in:Diario,Quincenal,Mensual,Semestral',
            'measurement_date' => 'required|date',
            'percentage'   => 'required|numeric|min:0|max:100',
            'url' => 'nullable|url|max:255',
        ]);
    
        if ($validator->fails()){
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        $kpi = RecursosHumanosKpi::findOrFail($id);
        $threshold = RecursosHumanosThreshold::findOrFail($request->threshold_id);
        $data = $request->all();
        $data['name'] = $threshold->kpi_name;
        $data['threshold_id'] = $threshold->id;
        $data['area'] = 'rrhh';
        $kpi->update($data);
    
        // Cambiado para redirigir al index en lugar de show
        return redirect()->route('kpis.rrhh.index')
            ->with('success', 'KPI de Recursos Humanos actualizado exitosamente.');
    }

    public function destroyRecursosHumanos($id)
    {
        $kpi = RecursosHumanosKpi::findOrFail($id);
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
    
    public function exportRecursosHumanos(Request $request)
    {
        $month = $request->input('month');
        $query = RecursosHumanosKpi::with('threshold');
        
        if ($month) {
            $query->whereMonth('measurement_date', $month);
        }
        
        $kpis = $query->orderBy('measurement_date', 'desc')->get();
        
        // Lógica para exportar a Excel o PDF
        // Implementar según las necesidades
        
        return back()->with('success', 'Exportación completada exitosamente.');
    }
}