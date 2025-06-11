<?php

namespace App\Http\Controllers;

use App\Models\Kpi;
use App\Models\Threshold;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KpiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Muestra el listado de KPIs para el área de Enfermería junto con el análisis estadístico.
     */
    public function indexEnfermeria(Request $request)
    {
      // Filtrado opcional por mes
        $month = $request->input('month');
        $query = Kpi::with('threshold')->where('area', 'enfermeria'); // Agregamos with('threshold')
        
        if ($month) {
            $query->whereMonth('measurement_date', $month);
        }
        
        $kpis = $query->orderBy('measurement_date', 'desc')->get();

        // Calcular estadísticas generales
        $count = $kpis->count();
        $percentages = $kpis->pluck('percentage')->toArray();

        // Calcular estadísticas por tipo de KPI
        $measurementKpis = $kpis->where('type', 'measurement');
        $informativeKpis = $kpis->where('type', 'informative');

        $measurementStats = [
            'total' => $measurementKpis->count(),
            'average' => $measurementKpis->avg('percentage') ?? 0,
            'countUnder' => $measurementKpis->filter(function($kpi) {
                return $kpi->status === 'No Alcanzado';
            })->count()
        ];

        $informativeStats = [
            'total' => $informativeKpis->count(),
            'average' => $informativeKpis->avg('percentage') ?? 0,
            'countUnder' => $informativeKpis->filter(function($kpi) {
                return $kpi->status === 'No Alcanzado';
            })->count()
        ];

        // Calcular estadísticas generales
        $average = $count > 0 ? array_sum($percentages) / $count : 0;
        
        // Calcular mediana
        if ($count > 0) {
            sort($percentages);
            $middle = floor($count / 2);
            $median = ($count % 2 == 0) 
                ? ($percentages[$middle - 1] + $percentages[$middle]) / 2 
                : $percentages[$middle];
        } else {
            $median = 0;
        }

        // Calcular desviación estándar y coeficiente de variación
        if ($count > 0) {
            $variance = array_reduce($percentages, function($carry, $item) use ($average) {
                return $carry + pow($item - $average, 2);
            }, 0) / $count;
            $stdDev = sqrt($variance);
            $coefficientOfVariation = ($average > 0) ? ($stdDev / $average) * 100 : 0;
        } else {
            $stdDev = 0;
            $coefficientOfVariation = 0;
        }

        // Obtener valores máximo y mínimo
        $max = $count > 0 ? max($percentages) : 0;
        $min = $count > 0 ? min($percentages) : 0;
        $range = $max - $min;

        // Obtener el umbral configurado
        $thresholdRecord = Threshold::where('area', 'enfermeria')->first();
        $thresholdValue = $thresholdRecord ? $thresholdRecord->value : 80;

        // Contar KPIs bajo el umbral
        $countUnder = $kpis->filter(function($kpi) {
            return $kpi->status === 'No Alcanzado';
        })->count();
        
        $percentageUnder = $count > 0 ? ($countUnder / $count) * 100 : 0;

        // Generar conclusión estadística profesional
        if ($count == 0) {
            $conclusion = "No hay KPIs registrados para realizar un análisis estadístico.";
        } else {
            $conclusion = "Análisis Estadístico: De un total de {$count} KPIs analizados, " .
                "se observa una media general de " . number_format($average, 2) . "% con una " .
                "desviación estándar de " . number_format($stdDev, 2) . ". " .
                "Los KPIs de Medición muestran un promedio de " . number_format($measurementStats['average'], 2) . "%, " .
                "mientras que los KPIs Informativos promedian " . number_format($informativeStats['average'], 2) . "%. " .
                "El coeficiente de variación es de " . number_format($coefficientOfVariation, 2) . "%, " .
                "lo que indica " . ($coefficientOfVariation < 15 ? "una baja" : ($coefficientOfVariation < 30 ? "una moderada" : "una alta")) . 
                " dispersión en los datos. " .
                "Un " . number_format($percentageUnder, 2) . "% de los KPIs está por debajo del umbral establecido (" . $thresholdValue . "%).";
        }

        return view('kpis.enfermeria.index', compact(
            'kpis',
            'average',
            'median',
            'stdDev',
            'coefficientOfVariation',
            'max',
            'min',
            'range',
            'countUnder',
            'percentageUnder',
            'conclusion',
            'measurementStats',
            'informativeStats'
        ));
    }

    /**
     * Muestra el formulario para crear un nuevo KPI.
     */
    public function createEnfermeria()
    {
        $thresholds = Threshold::where('area', 'enfermeria')->get();
        $types = [
            'measurement' => 'Medición',
            'informative' => 'Informativo'
        ];
        return view('kpis.enfermeria.create', compact('thresholds', 'types'));
    }

    /**
     * Almacena un nuevo KPI.
     */
    public function storeEnfermeria(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:measurement,informative',
            'threshold_id' => 'required|exists:thresholds,id',
            'methodology' => 'required|string',
            'frequency' => 'required|string|in:Diario,Quincenal,Mensual,Semestral',
            'measurement_date' => 'required|date',
            'percentage' => 'required|numeric|min:0|max:100',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        $threshold = Threshold::findOrFail($request->threshold_id);
        $data = $request->all();
        $data['name'] = $threshold->kpi_name;
        $data['area'] = 'enfermeria';
    
        Kpi::create($data);
    
        return redirect()->route('kpis.enfermeria.index')
            ->with('success', 'KPI registrado exitosamente.');
    }

    /**
     * Muestra los detalles de un KPI específico.
     */
    public function showEnfermeria($id)
    {
        $kpi = Kpi::with('threshold')->findOrFail($id);
        return view('kpis.enfermeria.show', compact('kpi'));
    }

    /**
     * Muestra el formulario para editar un KPI.
     */
    public function editEnfermeria($id)
    {
        $kpi = Kpi::findOrFail($id);
        $thresholds = Threshold::where('area', 'enfermeria')->get();
        return view('kpis.enfermeria.edit', compact('kpi', 'thresholds'));
    }

    /**
     * Actualiza un KPI específico.
     */
    public function updateEnfermeria(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'threshold_id' => 'required|exists:thresholds,id',
            'methodology' => 'required|string',
            'frequency' => 'required|string|in:Diario,Quincenal,Mensual,Semestral',
            'measurement_date' => 'required|date',
            'percentage' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:measurement,informative'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $kpi = Kpi::findOrFail($id);
        $threshold = Threshold::findOrFail($request->threshold_id);
        
        $data = $request->all();
        $data['name'] = $threshold->kpi_name;
        $data['area'] = 'enfermeria';

        $kpi->update($data);

        return redirect()->route('kpis.enfermeria.index')
            ->with('success', 'KPI actualizado exitosamente.');
    }

    /**
     * Elimina un KPI específico.
     */
    public function destroyEnfermeria($id)
    {
        $kpi = Kpi::findOrFail($id);
        $kpi->delete();
        
        return response()->json([
            'message' => 'KPI eliminado exitosamente.'
        ], 200);
    }

    public function index()
    {
        // Example response for API
        return response()->json([
            'status' => 'success',
            'data' => [
                'kpi1' => 85,
                'kpi2' => 90,
                'kpi3' => 78
            ]
        ]);
    }
}