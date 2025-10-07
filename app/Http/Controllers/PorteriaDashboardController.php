<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\RegistroPorteria;
use Illuminate\Support\Facades\Response;
use App\Exports\PorteriaExport;
use Maatwebsite\Excel\Facades\Excel;

class PorteriaDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Obtener el mes seleccionado o el actual por defecto
        $mes = $request->get('mes', 'actual');
        
        // Configurar fechas según el mes seleccionado
        if ($mes === 'actual') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
            $mesTexto = 'Mes Actual (' . Carbon::now()->format('F Y') . ')';
        } else {
            $year = Carbon::now()->year;
            $monthNumber = $this->getMonthNumber($mes);
            $startDate = Carbon::create($year, $monthNumber)->startOfMonth();
            $endDate = Carbon::create($year, $monthNumber)->endOfMonth();
            $mesTexto = $mes . ' ' . $year;
        }

        // Obtener estadísticas de la tabla registro_porteria
        $estadisticas = $this->getPorteriaStatistics($startDate, $endDate);
        
        // Obtener datos para gráficos
        $datosDiarios = $this->getDailyData($startDate, $endDate);
        $datosHorarios = $this->getHourlyData($startDate, $endDate);
        $datosTipoPersona = $this->getPersonTypeData($startDate, $endDate);
        
        // Solo mantener análisis comparativo y patrones de comportamiento
        $analisisComparativo = $this->getAnalisisComparativo($startDate, $endDate);
        $patronesComportamiento = $this->getPatronesComportamiento($startDate, $endDate);
        
        // Obtener registros para la tabla (con paginación)
        $registros = RegistroPorteria::whereBetween('fecha', [$startDate, $endDate])
            ->orderBy('fecha', 'desc')
            ->orderBy('hora_entrada', 'desc')
            ->paginate(10);
        
        return view('porteria.dashboard', compact(
            'estadisticas',
            'datosDiarios',
            'datosHorarios',
            'datosTipoPersona',
            'analisisComparativo',
            'patronesComportamiento',
            'registros',
            'mes',
            'mesTexto'
        ));
    }

    private function getPorteriaStatistics($startDate, $endDate)
    {
        try {
            // Usar el modelo RegistroPorteria para obtener estadísticas
            $totalRegistros = RegistroPorteria::whereBetween('fecha', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->count();

            $totalEntradas = RegistroPorteria::whereBetween('fecha', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereNotNull('hora_entrada')
                ->count();

            $totalSalidas = RegistroPorteria::whereBetween('fecha', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereNotNull('hora_salida')
                ->count();

            // Personas únicas por documento
            $personasUnicas = RegistroPorteria::whereBetween('fecha', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->distinct('documento')
                ->count('documento');

            // Personas únicas por tipo
            $empleadosRegistrados = RegistroPorteria::whereBetween('fecha', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('tipo_persona', 'Empleado')
                ->distinct('documento')
                ->count('documento');
                
            $estudiantesRegistrados = RegistroPorteria::whereBetween('fecha', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('tipo_persona', 'Estudiante')
                ->distinct('documento')
                ->count('documento');
                
            $visitantesRegistrados = RegistroPorteria::whereBetween('fecha', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('tipo_persona', 'Visitante')
                ->distinct('documento')
                ->count('documento');

            // Personas actualmente en el edificio (tienen entrada pero no salida)
            $personasAdentro = RegistroPorteria::whereBetween('fecha', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereNotNull('hora_entrada')
                ->whereNull('hora_salida')
                ->count();

            // Promedio diario
            $diasDelMes = $startDate->diffInDays($endDate) + 1;
            $promedioDiario = $diasDelMes > 0 ? round($totalRegistros / $diasDelMes, 1) : 0;

            return [
                'total_registros' => $totalRegistros,
                'total_entradas' => $totalEntradas,
                'total_salidas' => $totalSalidas,
                'personas_unicas' => $personasUnicas,
                'empleados_registrados' => $empleadosRegistrados,
                'estudiantes_registrados' => $estudiantesRegistrados,
                'visitantes_registrados' => $visitantesRegistrados,
                'personas_adentro' => $personasAdentro,
                'promedio_diario' => $promedioDiario,
                'periodo' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y')
            ];

        } catch (\Exception $e) {
            // Si hay error, retornar datos vacíos con mensaje de error
            return [
                'total_registros' => 0,
                'total_entradas' => 0,
                'total_salidas' => 0,
                'personas_unicas' => 0,
                'personas_adentro' => 0,
                'promedio_diario' => 0,
                'periodo' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
                'error' => 'Error al obtener datos: ' . $e->getMessage()
            ];
        }
    }

    private function getDailyData($startDate, $endDate)
    {
        try {
            $dailyData = RegistroPorteria::select(
                    'fecha',
                    DB::raw('COUNT(*) as total_registros'),
                    DB::raw('COUNT(DISTINCT documento) as personas_unicas'),
                    DB::raw('SUM(CASE WHEN hora_entrada IS NOT NULL THEN 1 ELSE 0 END) as entradas'),
                    DB::raw('SUM(CASE WHEN hora_salida IS NOT NULL THEN 1 ELSE 0 END) as salidas')
                )
                ->whereBetween('fecha', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->get();

            return $dailyData->map(function ($item) {
                return [
                    'fecha' => Carbon::parse($item->fecha)->format('d/m'),
                    'total_registros' => $item->total_registros,
                    'personas_unicas' => $item->personas_unicas,
                    'entradas' => $item->entradas,
                    'salidas' => $item->salidas
                ];
            });

        } catch (\Exception $e) {
            return collect([]);
        }
    }

    private function getHourlyData($startDate, $endDate)
    {
        try {
            // Obtener datos de entradas por hora
            $hourlyEntradas = RegistroPorteria::select(
                    DB::raw('HOUR(hora_entrada) as hora'),
                    DB::raw('COUNT(*) as total_entradas')
                )
                ->whereBetween('fecha', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereNotNull('hora_entrada')
                ->groupBy(DB::raw('HOUR(hora_entrada)'))
                ->pluck('total_entradas', 'hora');

            // Obtener datos de salidas por hora
            $hourlySalidas = RegistroPorteria::select(
                    DB::raw('HOUR(hora_salida) as hora'),
                    DB::raw('COUNT(*) as total_salidas')
                )
                ->whereBetween('fecha', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereNotNull('hora_salida')
                ->groupBy(DB::raw('HOUR(hora_salida)'))
                ->pluck('total_salidas', 'hora');

            // Completar horas faltantes con 0
            $hoursData = collect(range(6, 22))->map(function ($hour) use ($hourlyEntradas, $hourlySalidas) {
                return [
                    'hora' => sprintf('%02d:00', $hour),
                    'entradas' => $hourlyEntradas->get($hour, 0),
                    'salidas' => $hourlySalidas->get($hour, 0)
                ];
            });

            return $hoursData;

        } catch (\Exception $e) {
            return collect(range(6, 22))->map(function ($hour) {
                return [
                    'hora' => sprintf('%02d:00', $hour),
                    'entradas' => 0,
                    'salidas' => 0
                ];
            });
        }
    }

    private function getPersonTypeData($startDate, $endDate)
    {
        try {
            // Obtener datos por tipo de persona
            $typeData = RegistroPorteria::select(
                    'tipo_persona',
                    DB::raw('COUNT(*) as total_registros'),
                    DB::raw('COUNT(DISTINCT documento) as personas_unicas')
                )
                ->whereBetween('fecha', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->groupBy('tipo_persona')
                ->get();

            return $typeData->map(function ($item) {
                $tipoTexto = $this->getTipoPersonaTexto($item->tipo_persona);
                return [
                    'tipo' => $tipoTexto,
                    'total' => $item->total_registros,
                    'personas_unicas' => $item->personas_unicas
                ];
            });

        } catch (\Exception $e) {
            return collect([
                ['tipo' => 'Sin datos', 'total' => 0, 'personas_unicas' => 0]
            ]);
        }
    }

    private function getTipoPersonaTexto($tipo)
    {
        $tipos = [
            'empleado' => 'Empleados',
            'estudiante' => 'Estudiantes', 
            'externo' => 'Visitantes Externos',
            'contractor' => 'Contratistas',
            'provider' => 'Proveedores'
        ];
        
        return $tipos[$tipo] ?? ucfirst($tipo ?: 'Sin clasificar');
    }

    private function getMonthNumber($monthName)
    {
        $months = [
            'Enero' => 1, 'Febrero' => 2, 'Marzo' => 3, 'Abril' => 4,
            'Mayo' => 5, 'Junio' => 6, 'Julio' => 7, 'Agosto' => 8,
            'Septiembre' => 9, 'Octubre' => 10, 'Noviembre' => 11, 'Diciembre' => 12
        ];

        return $months[$monthName] ?? Carbon::now()->month;
    }

    public function exportToExcel(Request $request)
    {
        $mes = $request->get('mes', 'actual');
        
        // Configurar fechas
        if ($mes === 'actual') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        } else {
            $year = Carbon::now()->year;
            $monthNumber = $this->getMonthNumber($mes);
            $startDate = Carbon::create($year, $monthNumber)->startOfMonth();
            $endDate = Carbon::create($year, $monthNumber)->endOfMonth();
        }

        // Obtener todos los registros del período
        $registros = RegistroPorteria::whereBetween('fecha', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('fecha', 'desc')
            ->orderBy('hora_entrada', 'desc')
            ->get();

        $filename = 'reporte_porteria_' . $startDate->format('Y_m') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($registros) {
            $file = fopen('php://output', 'w');
            
            // Encabezados CSV
            fputcsv($file, [
                'Fecha',
                'Documento',
                'Nombre Completo',
                'Tipo Persona',
                'Hora Entrada',
                'Hora Salida',
                'Observaciones'
            ]);

            // Datos
            foreach ($registros as $registro) {
                fputcsv($file, [
                    $registro->fecha->format('d/m/Y'),
                    $registro->documento,
                    $registro->nombre . ' ' . $registro->apellido,
                    $this->getTipoPersonaTexto($registro->tipo_persona),
                    $registro->hora_entrada ? Carbon::parse($registro->hora_entrada)->format('H:i:s') : '',
                    $registro->hora_salida ? Carbon::parse($registro->hora_salida)->format('H:i:s') : '',
                    $registro->observaciones
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function export(Request $request)
    {
        $mes = $request->get('mes', 'actual');
        
        // Configurar fechas según el mes seleccionado
        if ($mes === 'actual') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
            $mesTexto = 'Mes_Actual_' . Carbon::now()->format('F_Y');
        } else {
            $year = Carbon::now()->year;
            $monthNumber = $this->getMonthNumber($mes);
            $startDate = Carbon::create($year, $monthNumber)->startOfMonth();
            $endDate = Carbon::create($year, $monthNumber)->endOfMonth();
            $mesTexto = $mes . '_' . $year;
        }

        $filename = 'Reporte_Porteria_' . $mesTexto . '_' . date('Y-m-d') . '.xlsx';

        return Excel::download(new PorteriaExport($startDate, $endDate, $mesTexto), $filename);
    }

    public function exportHtml(Request $request)
    {
        $mes = $request->get('mes', 'actual');
        
        // Configurar fechas según el mes seleccionado
        if ($mes === 'actual') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
            $mesTexto = 'Mes Actual - ' . Carbon::now()->format('F Y');
        } else {
            $year = Carbon::now()->year;
            $monthNumber = $this->getMonthNumber($mes);
            $startDate = Carbon::create($year, $monthNumber)->startOfMonth();
            $endDate = Carbon::create($year, $monthNumber)->endOfMonth();
            $mesTexto = ucfirst($mes) . ' ' . $year;
        }

        // Obtener datos
        $porteriaStats = $this->getPorteriaStatistics($startDate, $endDate);
        $registros = RegistroPorteria::whereBetween('fecha', [$startDate, $endDate])
            ->orderBy('fecha', 'desc')
            ->orderBy('hora_entrada', 'desc')
            ->get();

        $filename = 'Reporte_Porteria_' . str_replace(' ', '_', $mesTexto) . '_' . date('Y-m-d') . '.html';

        // Generar HTML
        $html = view('porteria.export-html', [
            'porteriaStats' => $porteriaStats,
            'registros' => $registros,
            'mesTexto' => $mesTexto,
            'startDate' => $startDate,
            'endDate' => $endDate
        ])->render();

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    // ================== MÉTODOS ANALÍTICOS AVANZADOS ==================

    /**
     * Obtener datos para heatmap de ocupación por día y hora
     */
    private function getHeatmapData($startDate, $endDate)
    {
        $data = [];
        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        
        for ($hora = 6; $hora <= 22; $hora++) {
            for ($dia = 0; $dia < 7; $dia++) {
                $registros = RegistroPorteria::whereBetween('fecha', [$startDate, $endDate])
                    ->whereRaw('DAYOFWEEK(fecha) - 1 = ?', [$dia])
                    ->whereRaw('HOUR(hora_entrada) = ?', [$hora])
                    ->count();
                
                $data[] = [
                    'x' => $diasSemana[$dia],
                    'y' => $hora . ':00',
                    'v' => $registros
                ];
            }
        }
        
        return $data;
    }

    /**
     * Análisis de flujo de entrada/salida
     */
    private function getFlujoAnalysis($startDate, $endDate)
    {
        $flujo = [];
        
        // Análisis por intervalos de 2 horas
        for ($hora = 6; $hora <= 20; $hora += 2) {
            $intervalo = sprintf('%02d:00-%02d:00', $hora, $hora + 2);
            
            $entradas = RegistroPorteria::whereBetween('fecha', [$startDate, $endDate])
                ->whereRaw('HOUR(hora_entrada) BETWEEN ? AND ?', [$hora, $hora + 1])
                ->count();
                
            $salidas = RegistroPorteria::whereBetween('fecha', [$startDate, $endDate])
                ->whereRaw('HOUR(hora_salida) BETWEEN ? AND ?', [$hora, $hora + 1])
                ->whereNotNull('hora_salida')
                ->count();
            
            $flujo[] = [
                'intervalo' => $intervalo,
                'entradas' => $entradas,
                'salidas' => $salidas,
                'flujo_neto' => $entradas - $salidas
            ];
        }
        
        return $flujo;
    }

    /**
     * Análisis de patrones de comportamiento
     */
    private function getPatronesComportamiento($startDate, $endDate)
    {
        // Tiempo promedio de permanencia por tipo de persona
        $tiemposPermanencia = RegistroPorteria::whereBetween('fecha', [$startDate, $endDate])
            ->whereNotNull('hora_salida')
            ->selectRaw('
                tipo_persona,
                AVG(TIMESTAMPDIFF(MINUTE, 
                    CONCAT(fecha, " ", hora_entrada), 
                    CONCAT(fecha, " ", hora_salida)
                )) as tiempo_promedio_minutos,
                COUNT(*) as total_visitas
            ')
            ->groupBy('tipo_persona')
            ->get();

        // Frecuencia de visitas por persona
        $frecuenciaVisitas = RegistroPorteria::whereBetween('fecha', [$startDate, $endDate])
            ->selectRaw('
                documento,
                nombre,
                apellido,
                tipo_persona,
                COUNT(*) as total_visitas,
                AVG(TIMESTAMPDIFF(MINUTE, 
                    CONCAT(fecha, " ", hora_entrada), 
                    CONCAT(fecha, " ", hora_salida)
                )) as tiempo_promedio
            ')
            ->groupBy('documento', 'nombre', 'apellido', 'tipo_persona')
            ->orderBy('total_visitas', 'desc')
            ->take(10)
            ->get();

        return [
            'tiempos_permanencia' => $tiemposPermanencia,
            'frecuencia_visitas' => $frecuenciaVisitas
        ];
    }

    /**
     * Métricas de ocupación y eficiencia
     */
    private function getMetricsOcupacion($startDate, $endDate)
    {
        $totalDias = $startDate->diffInDays($endDate) + 1;
        
        // Ocupación máxima por día
        $ocupacionMaxima = [];
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            $maxOcupacion = 0;
            
            // Revisar cada hora del día
            for ($hora = 6; $hora <= 22; $hora++) {
                $ocupacionHora = RegistroPorteria::where('fecha', $current->format('Y-m-d'))
                    ->where(function($query) use ($hora) {
                        $query->whereRaw('HOUR(hora_entrada) <= ?', [$hora])
                              ->where(function($q) use ($hora) {
                                  $q->whereRaw('HOUR(hora_salida) > ?', [$hora])
                                    ->orWhereNull('hora_salida');
                              });
                    })
                    ->count();
                
                $maxOcupacion = max($maxOcupacion, $ocupacionHora);
            }
            
            $ocupacionMaxima[] = [
                'fecha' => $current->format('Y-m-d'),
                'dia_semana' => $current->locale('es')->dayName,
                'ocupacion_maxima' => $maxOcupacion
            ];
            
            $current->addDay();
        }

        return [
            'ocupacion_por_dia' => $ocupacionMaxima,
            'promedio_ocupacion' => collect($ocupacionMaxima)->avg('ocupacion_maxima'),
            'max_ocupacion_periodo' => collect($ocupacionMaxima)->max('ocupacion_maxima')
        ];
    }

    /**
     * Análisis comparativo con períodos anteriores
     */
    private function getAnalisisComparativo($startDate, $endDate)
    {
        $diasPeriodo = $startDate->diffInDays($endDate) + 1;
        $periodoAnteriorStart = $startDate->copy()->subDays($diasPeriodo);
        $periodoAnteriorEnd = $endDate->copy()->subDays($diasPeriodo);

        // Estadísticas período actual
        $actual = [
            'total_registros' => RegistroPorteria::whereBetween('fecha', [$startDate, $endDate])->count(),
            'personas_unicas' => RegistroPorteria::whereBetween('fecha', [$startDate, $endDate])
                ->distinct('documento')->count(),
            'promedio_diario' => RegistroPorteria::whereBetween('fecha', [$startDate, $endDate])
                ->count() / $diasPeriodo
        ];

        // Estadísticas período anterior
        $anterior = [
            'total_registros' => RegistroPorteria::whereBetween('fecha', [$periodoAnteriorStart, $periodoAnteriorEnd])->count(),
            'personas_unicas' => RegistroPorteria::whereBetween('fecha', [$periodoAnteriorStart, $periodoAnteriorEnd])
                ->distinct('documento')->count(),
            'promedio_diario' => RegistroPorteria::whereBetween('fecha', [$periodoAnteriorStart, $periodoAnteriorEnd])
                ->count() / $diasPeriodo
        ];

        return [
            'actual' => $actual,
            'anterior' => $anterior,
            'variaciones' => [
                'total_registros' => $anterior['total_registros'] > 0 ? 
                    round((($actual['total_registros'] - $anterior['total_registros']) / $anterior['total_registros']) * 100, 2) : 0,
                'personas_unicas' => $anterior['personas_unicas'] > 0 ? 
                    round((($actual['personas_unicas'] - $anterior['personas_unicas']) / $anterior['personas_unicas']) * 100, 2) : 0,
                'promedio_diario' => $anterior['promedio_diario'] > 0 ? 
                    round((($actual['promedio_diario'] - $anterior['promedio_diario']) / $anterior['promedio_diario']) * 100, 2) : 0
            ]
        ];
    }
}