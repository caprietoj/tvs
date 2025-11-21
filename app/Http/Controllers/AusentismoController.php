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
        try {
            $request->validate([
                'mes' => 'required',
            ]);

            $registrosProcesados = 0;
            $registrosError = 0;

            // Si viene un archivo
            if ($request->hasFile('archivo')) {
                $request->validate([
                    'archivo' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB máximo
                ]);

                $file = $request->file('archivo');
                $extension = $file->getClientOriginalExtension();

                // Leer el contenido del archivo
                if (in_array($extension, ['xlsx', 'xls'])) {
                    // Para archivos Excel, necesitamos la librería PhpSpreadsheet
                    try {
                        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
                        $worksheet = $spreadsheet->getActiveSheet();
                        $rows = $worksheet->toArray();
                        
                        // Saltar la primera fila si es encabezado
                        $firstRow = true;
                        foreach ($rows as $row) {
                            if ($firstRow) {
                                $firstRow = false;
                                continue;
                            }
                            
                            if ($this->processRow($row, $request->mes)) {
                                $registrosProcesados++;
                            } else {
                                $registrosError++;
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error leyendo archivo Excel: ' . $e->getMessage());
                        return redirect()->back()->with('error', 'Error al procesar el archivo Excel. Por favor, verifique el formato.');
                    }
                } elseif ($extension === 'csv') {
                    // Para archivos CSV
                    $csvData = array_map('str_getcsv', file($file->getPathname()));
                    $firstRow = true;
                    foreach ($csvData as $row) {
                        if ($firstRow) {
                            $firstRow = false;
                            continue;
                        }
                        
                        if ($this->processRow($row, $request->mes)) {
                            $registrosProcesados++;
                        } else {
                            $registrosError++;
                        }
                    }
                }
            } 
            // Si vienen datos pegados
            elseif ($request->has('datos') && !empty($request->datos)) {
                $request->validate([
                    'datos' => 'required',
                ]);

                $rows = explode("\n", $request->datos);
                
                foreach($rows as $row) {
                    if (empty(trim($row))) continue;
                    
                    $columns = explode("\t", $row);
                    
                    if ($this->processRow($columns, $request->mes)) {
                        $registrosProcesados++;
                    } else {
                        $registrosError++;
                    }
                }
            } else {
                return redirect()->back()->with('error', 'Debe proporcionar un archivo o datos pegados.');
            }

            $mensaje = "Se procesaron $registrosProcesados registros correctamente.";
            if ($registrosError > 0) {
                $mensaje .= " $registrosError registros tuvieron errores y no se cargaron.";
            }

            return redirect()->back()->with('success', $mensaje);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Error general en store: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Error al procesar los datos: ' . $e->getMessage());
        }
    }

    /**
     * Procesar una fila individual de datos
     */
    private function processRow($columns, $mes)
    {
        try {
            // Verificar que tengamos suficientes columnas
            if (count($columns) < 9) {
                \Log::warning("Fila con columnas insuficientes: " . count($columns));
                return false;
            }

            // Limpiar datos
            $persona = trim($columns[1]);
            $fechaCreacionStr = trim($columns[2]);
            $dependencia = trim($columns[3]);
            $fechaDesdeStr = trim($columns[4]);
            $fechaHastaStr = trim($columns[5]);
            $asistencia = trim($columns[6]);
            $duracion = trim($columns[7]);
            $motivo = trim($columns[8]);

            // Validar que los campos principales no estén vacíos
            if (empty($persona) || empty($fechaCreacionStr) || empty($fechaDesdeStr)) {
                \Log::warning("Fila con campos vacíos");
                return false;
            }

            // Formatear fechas - intentar diferentes formatos
            try {
                $fechaCreacion = $this->parseDate($fechaCreacionStr);
                $fechaDesde = $this->parseDate($fechaDesdeStr);
                $fechaHasta = $this->parseDate($fechaHastaStr);
            } catch (\Exception $e) {
                \Log::error("Error parseando fechas: " . $e->getMessage());
                \Log::error("Fechas: creación=$fechaCreacionStr, desde=$fechaDesdeStr, hasta=$fechaHastaStr");
                return false;
            }

            // Crear el registro
            Ausentismo::create([
                'persona' => $persona,
                'fecha_de_creacion' => $fechaCreacion,
                'dependencia' => $dependencia,
                'fecha_ausencia_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
                'asistencia' => $asistencia,
                'duracion_ausencia' => $duracion,
                'motivo_de_ausencia' => $motivo,
                'mes' => $mes
            ]);

            return true;

        } catch (\Exception $e) {
            \Log::error("Error procesando fila: " . json_encode($columns));
            \Log::error($e->getMessage());
            return false;
        }
    }

    /**
     * Parsear diferentes formatos de fecha
     */
    private function parseDate($dateString)
    {
        $dateString = trim($dateString);
        
        if (empty($dateString)) {
            return null;
        }

        // Intentar diferentes formatos de fecha
        $formats = [
            'd/m/y H:i',
            'n/j/y H:i',
            'd/m/Y H:i',
            'n/j/Y H:i',
            'd-m-y H:i',
            'd-m-Y H:i',
            'Y-m-d H:i:s',
            'Y-m-d',
            'd/m/Y',
            'd/m/y',
            'n/j/Y',
            'n/j/y',
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $dateString);
                if ($date) {
                    return $date->format('Y-m-d');
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Si ningún formato funcionó, intentar con strtotime
        try {
            $timestamp = strtotime($dateString);
            if ($timestamp !== false) {
                return date('Y-m-d', $timestamp);
            }
        } catch (\Exception $e) {
            // Continuar
        }

        throw new \Exception("No se pudo parsear la fecha: $dateString");
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
