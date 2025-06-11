<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseRequest;
use App\Exports\PhotocopiesExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class PhotocopiesDashboardController extends Controller
{
    /**
     * Constructor para aplicar middleware de autenticación
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar el dashboard de fotocopias
     */
    public function index(Request $request)
    {
        // Obtener filtros de la solicitud
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $section = $request->get('section');
        $teacher = $request->get('teacher');
        $course = $request->get('course');        // Consulta base - filtrar solo solicitudes de fotocopias
        $query = PurchaseRequest::where('type', 'materials')
            ->whereNotNull('copy_items')
            ->whereNull('material_items')
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);        // Aplicar filtros adicionales
        if ($section) {
            $query->where('section', 'like', '%' . $section . '%');
        }
        if ($teacher) {
            $query->where('requester', 'like', '%' . $teacher . '%');
        }
        if ($course) {
            $query->where('grade', 'like', '%' . $course . '%');
        }

        // Obtener los datos
        $copiesRequests = $query->orderBy('created_at', 'desc')->get();

        // Calcular estadísticas
        $statistics = $this->calculateStatistics($copiesRequests);

        // Obtener datos para filtros
        $filters = $this->getFiltersData();

        return view('photocopies.dashboard', compact(
            'copiesRequests',
            'statistics',
            'filters',
            'startDate',
            'endDate',
            'section',
            'teacher',
            'course'
        ));
    }    /**
     * Calcular estadísticas del servicio de fotocopias
     */
    private function calculateStatistics($copiesRequests)
    {
        $stats = [
            'total' => 0,
            'blancoNegro' => 0,
            'color' => 0,
            'dobleCarta' => 0,
            'impresion' => 0, // Nuevo campo
            'totalCopias' => 0, // Nuevo campo para total de páginas
            'satisfaccion' => ['si' => 0, 'no' => 0],
            'docentes' => [],
            'secciones' => [],
            'cursos' => [],
            'meses' => []
        ];

        foreach ($copiesRequests as $request) {
            $stats['total']++;
            
            // Procesar copy_items para obtener totales de impresiones
            $blancoNegroTotal = 0;
            $colorTotal = 0;
            $dobleCartaTotal = 0;
            $impresionTotal = 0;
            $totalCopiasTotal = 0;
            
            if (is_array($request->copy_items)) {
                foreach ($request->copy_items as $item) {
                    // Campos existentes (ahora convertidos a boolean)
                    if (isset($item['black_white']) && $item['black_white']) {
                        $blancoNegroTotal++;
                    }
                    if (isset($item['color']) && $item['color']) {
                        $colorTotal++;
                    }
                    if (isset($item['double_letter_color']) && $item['double_letter_color']) {
                        $dobleCartaTotal++;
                    }
                    
                    // Nuevo campo IMPRESIÓN
                    if (isset($item['impresion']) && $item['impresion']) {
                        $impresionTotal++;
                    }
                    
                    // Nuevo campo TOTAL (páginas totales)
                    $totalCopiasTotal += (int)($item['total'] ?? 0);
                }
            }
            
            $stats['blancoNegro'] += $blancoNegroTotal;
            $stats['color'] += $colorTotal;
            $stats['dobleCarta'] += $dobleCartaTotal;
            $stats['impresion'] += $impresionTotal;
            $stats['totalCopias'] += $totalCopiasTotal;
            
            // Satisfacción - asumir satisfecho si la solicitud está aprobada o completada
            $satisfecho = in_array($request->status, ['approved', 'completed']);
            
            if ($satisfecho) {
                $stats['satisfaccion']['si']++;
            } else {
                $stats['satisfaccion']['no']++;
            }
            
            // Contadores por categoría (usar total de páginas como medida principal)
            $totalImpresiones = $totalCopiasTotal > 0 ? $totalCopiasTotal : ($blancoNegroTotal + $colorTotal + $dobleCartaTotal + $impresionTotal);
            
            // Por docente
            $docente = $request->requester ?? 'Sin especificar';
            if (!isset($stats['docentes'][$docente])) {
                $stats['docentes'][$docente] = ['total' => 0, 'solicitudes' => 0, 'satisfecho' => 0, 'totalCopias' => 0];
            }
            $stats['docentes'][$docente]['total'] += $totalImpresiones;
            $stats['docentes'][$docente]['totalCopias'] += $totalCopiasTotal;
            $stats['docentes'][$docente]['solicitudes']++;
            if ($satisfecho) $stats['docentes'][$docente]['satisfecho']++;
            
            // Por sección
            $seccion = $request->section ?? 'Sin especificar';
            if (!isset($stats['secciones'][$seccion])) {
                $stats['secciones'][$seccion] = ['total' => 0, 'solicitudes' => 0, 'satisfecho' => 0, 'totalCopias' => 0];
            }
            $stats['secciones'][$seccion]['total'] += $totalImpresiones;
            $stats['secciones'][$seccion]['totalCopias'] += $totalCopiasTotal;
            $stats['secciones'][$seccion]['solicitudes']++;
            if ($satisfecho) $stats['secciones'][$seccion]['satisfecho']++;
            
            // Por curso
            $curso = $request->grade ?? 'Sin especificar';
            if (!isset($stats['cursos'][$curso])) {
                $stats['cursos'][$curso] = ['total' => 0, 'solicitudes' => 0, 'satisfecho' => 0, 'totalCopias' => 0];
            }
            $stats['cursos'][$curso]['total'] += $totalImpresiones;
            $stats['cursos'][$curso]['totalCopias'] += $totalCopiasTotal;
            $stats['cursos'][$curso]['solicitudes']++;
            if ($satisfecho) $stats['cursos'][$curso]['satisfecho']++;
            
            // Por mes
            $mes = Carbon::parse($request->created_at)->format('M Y');
            if (!isset($stats['meses'][$mes])) {
                $stats['meses'][$mes] = ['total' => 0, 'solicitudes' => 0, 'satisfecho' => 0, 'totalCopias' => 0];
            }
            $stats['meses'][$mes]['total'] += $totalImpresiones;
            $stats['meses'][$mes]['totalCopias'] += $totalCopiasTotal;
            $stats['meses'][$mes]['solicitudes']++;
            if ($satisfecho) $stats['meses'][$mes]['satisfecho']++;
        }

        // Calcular porcentajes
        $totalImpresiones = $stats['blancoNegro'] + $stats['color'] + $stats['dobleCarta'] + $stats['impresion'];
        $porcentajeSatisfaccion = $stats['total'] > 0 ? 
            round(($stats['satisfaccion']['si'] / $stats['total']) * 100, 1) : 0;
        
        // Calcular KPI
        $stats['kpi'] = $this->calculateKPI($stats);
        $stats['porcentajeSatisfaccion'] = $porcentajeSatisfaccion;
        $stats['totalImpresiones'] = $totalImpresiones;
        
        // Top performers (ordenar por total de copias cuando esté disponible)
        $stats['topDocentes'] = collect($stats['docentes'])
            ->sortByDesc(function($item) {
                return $item['totalCopias'] > 0 ? $item['totalCopias'] : $item['total'];
            })
            ->take(5)
            ->toArray();
            
        $stats['topSecciones'] = collect($stats['secciones'])
            ->sortByDesc(function($item) {
                return $item['totalCopias'] > 0 ? $item['totalCopias'] : $item['total'];
            })
            ->take(5)
            ->toArray();
            
        $stats['topCursos'] = collect($stats['cursos'])
            ->sortByDesc(function($item) {
                return $item['totalCopias'] > 0 ? $item['totalCopias'] : $item['total'];
            })
            ->take(5)
            ->toArray();

        return $stats;
    }

    /**
     * Calcular KPI con criterio laxo
     */
    private function calculateKPI($stats)
    {
        if ($stats['total'] == 0) return 0;

        // Factor 1: Satisfacción (70%)
        $factorSatisfaccion = ($stats['satisfaccion']['si'] / $stats['total']) * 70;
        
        // Factor 2: Cobertura (20%)
        $porcentajeAtencion = ($stats['satisfaccion']['si'] / $stats['total']) * 100;
        $factorCobertura = 10; // Mínimo
        if ($porcentajeAtencion >= 95) $factorCobertura = 20;
        elseif ($porcentajeAtencion >= 85) $factorCobertura = 18;
        elseif ($porcentajeAtencion >= 75) $factorCobertura = 16;
        elseif ($porcentajeAtencion >= 65) $factorCobertura = 14;
        elseif ($porcentajeAtencion >= 50) $factorCobertura = 12;
        
        // Factor 3: Actividad (10%)
        $factorActividad = 5; // Mínimo
        if ($stats['total'] >= 50) $factorActividad = 10;
        elseif ($stats['total'] >= 30) $factorActividad = 9;
        elseif ($stats['total'] >= 20) $factorActividad = 8;
        elseif ($stats['total'] >= 10) $factorActividad = 7;
        elseif ($stats['total'] >= 5) $factorActividad = 6;
        
        return round($factorSatisfaccion + $factorCobertura + $factorActividad, 1);
    }    /**
     * Obtener datos para filtros
     */
    private function getFiltersData()
    {
        $baseQuery = PurchaseRequest::where('type', 'materials')
            ->whereNotNull('copy_items')
            ->whereNull('material_items');
            
        return [
            'sections' => $baseQuery->distinct()->pluck('section')->filter()->sort()->values(),
            'teachers' => $baseQuery->distinct()->pluck('requester')->filter()->sort()->values(),
            'courses' => $baseQuery->distinct()->pluck('grade')->filter()->sort()->values(),
        ];
    }    /**
     * Exportar datos para análisis
     */
    public function exportData(Request $request)
    {
        // Aplicar los mismos filtros que en index
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $section = $request->get('section');
        $teacher = $request->get('teacher');
        $course = $request->get('course');

        $query = PurchaseRequest::where('type', 'materials')
            ->whereNotNull('copy_items')
            ->whereNull('material_items')
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);

        if ($section) $query->where('section', 'like', '%' . $section . '%');
        if ($teacher) $query->where('requester', 'like', '%' . $teacher . '%');
        if ($course) $query->where('grade', 'like', '%' . $course . '%');

        $copiesRequests = $query->orderBy('created_at', 'desc')->get();

        $filename = 'datos_fotocopias_' . date('Y-m-d') . '.xlsx';
        
        return Excel::download(new PhotocopiesExport($copiesRequests), $filename);
    }
}
