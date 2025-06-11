<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kpi;
use App\Models\ComprasKpi;
use App\Models\RecursosHumanosKpi;
use App\Models\SistemasKpi;
use App\Models\Threshold;
use App\Models\ComprasThreshold;
use App\Models\RecursosHumanosThreshold;
use App\Models\SistemasThreshold;

class KPIReportController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month');
        
        $query = function($q) use ($month) {
            if ($month) {
                $q->whereMonth('created_at', '=', $this->getMonthNumber($month));
            }
        };

        // Obtener KPIs con filtro de mes
        $kpis = Kpi::where($query)->get();
        $comprasKpis = ComprasKpi::where($query)->get();
        $recursosKpi = RecursosHumanosKpi::where($query)->get();
        $sistemasKpi = SistemasKpi::where($query)->get();

        // Fetch thresholds with error handling
        $enfermeriaThresholds = Threshold::all() ?? collect([]);
        $comprasThresholds = ComprasThreshold::all() ?? collect([]);
        $rrhhThresholds = RecursosHumanosThreshold::all() ?? collect([]);
        $sistemasThresholds = SistemasThreshold::all() ?? collect([]);

        // Compute analysis with safe calculations
        $enfermeriaAnalysis = [
            'avg_percentage' => $kpis->avg('percentage') ?? 0,
            'avg_threshold' => $enfermeriaThresholds->avg('value') ?? 0,
            'difference' => ($kpis->avg('percentage') ?? 0) - ($enfermeriaThresholds->avg('value') ?? 0),
            'status' => ($kpis->avg('percentage') ?? 0) < ($enfermeriaThresholds->avg('value') ?? 0)
                ? 'No se alcanzó el umbral esperado'
                : 'Umbral alcanzado'
        ];

        $comprasAnalysis = [
            'avg_percentage' => $comprasKpis->avg('percentage') ?? 0,
            'avg_threshold' => $comprasThresholds->avg('value') ?? 0,
            'difference' => ($comprasKpis->avg('percentage') ?? 0) - ($comprasThresholds->avg('value') ?? 0),
            'status' => ($comprasKpis->avg('percentage') ?? 0) < ($comprasThresholds->avg('value') ?? 0)
                ? 'No se alcanzó el umbral esperado'
                : 'Umbral alcanzado'
        ];

        $rrhhAnalysis = [
            'avg_percentage' => $recursosKpi->avg('percentage') ?? 0,
            'avg_threshold' => $rrhhThresholds->avg('value') ?? 0,
            'difference' => ($recursosKpi->avg('percentage') ?? 0) - ($rrhhThresholds->avg('value') ?? 0),
            'status' => ($recursosKpi->avg('percentage') ?? 0) < ($rrhhThresholds->avg('value') ?? 0)
                ? 'No se alcanzó el umbral esperado'
                : 'Umbral alcanzado'
        ];

        $sistemasAnalysis = [
            'avg_percentage' => $sistemasKpi->avg('percentage') ?? 0,
            'avg_threshold' => $sistemasThresholds->avg('value') ?? 0,
            'difference' => ($sistemasKpi->avg('percentage') ?? 0) - ($sistemasThresholds->avg('value') ?? 0),
            'status' => ($sistemasKpi->avg('percentage') ?? 0) < ($sistemasThresholds->avg('value') ?? 0)
                ? 'No se alcanzó el umbral esperado'
                : 'Umbral alcanzado'
        ];

        if ($request->ajax()) {
            return response()->json([
                'kpis' => $kpis,
                'comprasKpis' => $comprasKpis,
                'recursosKpi' => $recursosKpi,
                'sistemasKpi' => $sistemasKpi,
                'enfermeriaAnalysis' => $enfermeriaAnalysis,
                'comprasAnalysis' => $comprasAnalysis,
                'rrhhAnalysis' => $rrhhAnalysis,
                'sistemasAnalysis' => $sistemasAnalysis
            ]);
        }

        return view('reports.kpi_report', compact(
            'kpis', 'comprasKpis', 'recursosKpi', 'sistemasKpi',
            'enfermeriaAnalysis', 'comprasAnalysis', 'rrhhAnalysis', 'sistemasAnalysis',
            'enfermeriaThresholds', 'comprasThresholds', 'rrhhThresholds', 'sistemasThresholds'
        ));
    }

    private function getMonthNumber($monthName)
    {
        $months = [
            'Enero' => 1, 'Febrero' => 2, 'Marzo' => 3, 'Abril' => 4,
            'Mayo' => 5, 'Junio' => 6, 'Julio' => 7, 'Agosto' => 8,
            'Septiembre' => 9, 'Octubre' => 10, 'Noviembre' => 11, 'Diciembre' => 12
        ];
        
        return $months[$monthName] ?? null;
    }
}