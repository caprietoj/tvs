<?php

namespace App\Http\Controllers;

use App\Models\Ausentismo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AusentismoController extends Controller
{
    public function showUploadForm()
    {
        return view('ausentismos.upload');
    }

    public function store(Request $request)
    {
        $request->validate([
            'mes' => 'required',
            'datos' => 'required'
        ]);

        $rows = explode("\n", $request->datos);
        
        foreach($rows as $row) {
            $columns = explode("\t", $row);
            if(count($columns) == 9) {
                try {
                    // Formatear fechas
                    $fechaCreacion = Carbon::createFromFormat('d/m/y H:i', trim($columns[2]))->format('Y-m-d');
                    $fechaDesde = Carbon::createFromFormat('n/j/y H:i', trim($columns[4]))->format('Y-m-d');
                    $fechaHasta = Carbon::createFromFormat('n/j/y H:i', trim($columns[5]))->format('Y-m-d');

                    Ausentismo::create([
                        'persona' => trim($columns[1]),
                        'fecha_de_creacion' => $fechaCreacion,
                        'dependencia' => trim($columns[3]),
                        'fecha_ausencia_desde' => $fechaDesde,
                        'fecha_hasta' => $fechaHasta,
                        'asistencia' => trim($columns[6]),
                        'duracion_ausencia' => trim($columns[7]),
                        'motivo_de_ausencia' => trim($columns[8]),
                        'mes' => $request->mes
                    ]);
                } catch (\Exception $e) {
                    // Log el error y continuar con la siguiente fila
                    \Log::error("Error procesando fila: " . $row);
                    \Log::error($e->getMessage());
                    continue;
                }
            }
        }

        return redirect()->back()->with('success', 'Datos cargados correctamente');
    }

    public function dashboard(Request $request)
    {
        $query = Ausentismo::query();
        
        if ($request->mes) {
            $query->where('mes', $request->mes);
        }

        $totalAusencias = $query->count();
        
        $motivoComun = DB::table('ausentismos')
            ->when($request->mes, function($query) use ($request) {
                return $query->where('mes', $request->mes);
            })
            ->select('motivo_de_ausencia', DB::raw('count(*) as total'))
            ->groupBy('motivo_de_ausencia')
            ->orderByDesc('total')
            ->first();

        $dependenciaAfectada = DB::table('ausentismos')
            ->when($request->mes, function($query) use ($request) {
                return $query->where('mes', $request->mes);
            })
            ->select('dependencia', DB::raw('count(*) as total'))
            ->groupBy('dependencia')
            ->orderByDesc('total')
            ->first();

        $motivosPorcentaje = DB::table('ausentismos')
            ->when($request->mes, function($query) use ($request) {
                return $query->where('mes', $request->mes);
            })
            ->select('motivo_de_ausencia', DB::raw('count(*) as total'))
            ->groupBy('motivo_de_ausencia')
            ->orderBy('total', 'desc')
            ->get();

        $dependenciasPorcentaje = DB::table('ausentismos')
            ->when($request->mes, function($query) use ($request) {
                return $query->where('mes', $request->mes);
            })
            ->select('dependencia', DB::raw('count(*) as total'))
            ->groupBy('dependencia')
            ->orderBy('total', 'desc')
            ->get();

        if ($request->ajax()) {
            return response()->json(compact(
                'totalAusencias',
                'motivoComun',
                'dependenciaAfectada',
                'motivosPorcentaje',
                'dependenciasPorcentaje'
            ));
        }

        return view('ausentismos.dashboard', compact(
            'totalAusencias',
            'motivoComun',
            'dependenciaAfectada',
            'motivosPorcentaje',
            'dependenciasPorcentaje'
        ));
    }

    public function getData(Request $request)
    {
        $query = Ausentismo::query();

        if ($request->mes) {
            $query->where('mes', $request->mes);
        }

        if ($request->dependencia) {
            $query->where('dependencia', $request->dependencia);
        }

        // Filtro de duración mejorado
        if ($request->duracion) {
            $query->where(function($q) use ($request) {
                if ($request->duracion === 'corta') {
                    $q->where(function($sq) {
                        $sq->where('duracion_ausencia', 'LIKE', '%hora%')
                           ->orWhere('duracion_ausencia', 'LIKE', '%minuto%')
                           ->orWhere('duracion_ausencia', 'LIKE', '%1 día%')
                           ->orWhere('duracion_ausencia', 'LIKE', '%2 día%')
                           ->orWhere('duracion_ausencia', 'LIKE', '%3 día%');
                    });
                } else {
                    $q->where(function($sq) {
                        $sq->where('duracion_ausencia', 'LIKE', '%4 día%')
                           ->orWhere('duracion_ausencia', 'LIKE', '%5 día%')
                           ->orWhere('duracion_ausencia', 'LIKE', '%6 día%')
                           ->orWhere('duracion_ausencia', 'LIKE', '%7 día%')
                           ->orWhere('duracion_ausencia', 'LIKE', '%8 día%')
                           ->orWhere('duracion_ausencia', 'LIKE', '%9 día%')
                           ->orWhere('duracion_ausencia', 'REGEXP', '1[0-9] día')
                           ->orWhere('duracion_ausencia', 'REGEXP', '[2-9][0-9] día');
                    })
                    ->where('duracion_ausencia', 'NOT LIKE', '%hora%')
                    ->where('duracion_ausencia', 'NOT LIKE', '%minuto%');
                }
            });
        }

        // Si es una solicitud del dashboard, devolver datos agregados
        if ($request->dashboard) {
            $motivoComun = DB::table('ausentismos')
                ->select('motivo_de_ausencia', DB::raw('count(*) as total'))
                ->when($request->mes, function($q) use ($request) {
                    return $q->where('mes', $request->mes);
                })
                ->groupBy('motivo_de_ausencia')
                ->orderByRaw('count(*) DESC')
                ->first();

            $dependenciaAfectada = DB::table('ausentismos')
                ->select('dependencia', DB::raw('count(*) as total'))
                ->when($request->mes, function($q) use ($request) {
                    return $q->where('mes', $request->mes);
                })
                ->groupBy('dependencia')
                ->orderByRaw('count(*) DESC')
                ->first();

            $data = $query->get();
            
            return response()->json([
                'totalAusencias' => $data->count(),
                'motivoComun' => $motivoComun ? $motivoComun->motivo_de_ausencia : 'N/A',
                'dependenciaAfectada' => $dependenciaAfectada ? $dependenciaAfectada->dependencia : 'N/A',
                'motivosPorcentaje' => $data->groupBy('motivo_de_ausencia')
                    ->map(function ($group) use ($data) {
                        return [
                            'motivo_de_ausencia' => $group->first()->motivo_de_ausencia,
                            'total' => $group->count()
                        ];
                    })->values(),
                'dependenciasPorcentaje' => $data->groupBy('dependencia')
                    ->map(function ($group) use ($data) {
                        return [
                            'dependencia' => $group->first()->dependencia,
                            'total' => $group->count()
                        ];
                    })->values(),
            ]);
        }

        // Para la tabla, mostrar la duración exactamente como está en la BD
        return datatables()->of($query)
            ->make(true);
    }
}
