<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EstudiantesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['buscarEstudiantes']);
        $this->middleware('can:view.enfermeria')->except(['buscarEstudiantes']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $estudiantes = Estudiante::activos()->ordenadosPorNombre()->paginate(20);
        
        $estadisticas = [
            'total' => Estudiante::activos()->count(),
            'por_curso' => Estudiante::activos()
                ->select('curso', DB::raw('count(*) as total'))
                ->groupBy('curso')
                ->orderBy('curso')
                ->get()
                ->pluck('total', 'curso')
                ->toArray()
        ];
        
        return view('parametrizacion.estudiantes.index', compact('estudiantes', 'estadisticas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('parametrizacion.estudiantes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'curso' => 'required|string|max:50',
            'nombre' => 'required|string|max:255',
            'apellido_1' => 'required|string|max:255',
            'apellido_2' => 'nullable|string|max:255',
            'codigo' => 'required|string|max:50|unique:estudiantes,codigo',
            'documento' => 'required|string|max:50|unique:estudiantes,documento',
            'eps' => 'nullable|string|max:255',
            'sexo' => 'required|in:M,F,Masculino,Femenino',
            'tipo_sangre' => 'nullable|string|max:10',
        ]);

        $data = $request->all();
        $data['sexo'] = Estudiante::normalizarSexo($data['sexo']);
        $data['tipo_sangre'] = Estudiante::validarTipoSangre($data['tipo_sangre'] ?? '');

        Estudiante::create($data);

        return redirect()
            ->route('estudiantes.index')
            ->with('success', 'Estudiante registrado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Estudiante $estudiante): View
    {
        return view('parametrizacion.estudiantes.edit', compact('estudiante'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Estudiante $estudiante): RedirectResponse
    {
        $request->validate([
            'curso' => 'required|string|max:50',
            'nombre' => 'required|string|max:255',
            'apellido_1' => 'required|string|max:255',
            'apellido_2' => 'nullable|string|max:255',
            'codigo' => 'required|string|max:50|unique:estudiantes,codigo,' . $estudiante->id,
            'documento' => 'required|string|max:50|unique:estudiantes,documento,' . $estudiante->id,
            'eps' => 'nullable|string|max:255',
            'sexo' => 'required|in:M,F,Masculino,Femenino',
            'tipo_sangre' => 'nullable|string|max:10',
            'activo' => 'boolean'
        ]);

        $data = $request->all();
        $data['sexo'] = Estudiante::normalizarSexo($data['sexo']);
        $data['tipo_sangre'] = Estudiante::validarTipoSangre($data['tipo_sangre'] ?? '');

        $estudiante->update($data);

        return redirect()
            ->route('estudiantes.index')
            ->with('success', 'Estudiante actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Estudiante $estudiante): RedirectResponse
    {
        $estudiante->update(['activo' => false]);

        return redirect()
            ->route('estudiantes.index')
            ->with('success', 'Estudiante desactivado exitosamente.');
    }

    /**
     * Importar estudiantes desde Excel/texto
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'datos_excel' => 'required|string',
        ]);

        try {
            $lineas = explode("\n", $request->datos_excel);
            $encabezados = null;
            $procesados = 0;
            $errores = [];

            foreach ($lineas as $numeroLinea => $linea) {
                $linea = trim($linea);
                
                if (empty($linea)) continue;

                $columnas = preg_split('/\t/', $linea);
                
                // Primera línea son los encabezados
                if ($encabezados === null) {
                    $encabezados = $columnas;
                    continue;
                }

                try {
                    // Validar que tenemos suficientes columnas
                    if (count($columnas) < 6) {
                        $errores[] = "Línea " . ($numeroLinea + 1) . ": Faltan columnas requeridas";
                        continue;
                    }

                    $datos = [
                        'curso' => trim($columnas[0] ?? ''),
                        'nombre' => trim($columnas[1] ?? ''),
                        'apellido_1' => trim($columnas[2] ?? ''),
                        'apellido_2' => trim($columnas[3] ?? ''),
                        'codigo' => trim($columnas[4] ?? ''),
                        'documento' => trim($columnas[5] ?? ''),
                        'eps' => trim($columnas[6] ?? ''),
                        'sexo' => Estudiante::normalizarSexo(trim($columnas[7] ?? '')),
                        'tipo_sangre' => Estudiante::validarTipoSangre(trim($columnas[8] ?? '')),
                    ];

                    // Validaciones básicas
                    if (empty($datos['curso']) || empty($datos['nombre']) || empty($datos['apellido_1']) || 
                        empty($datos['codigo']) || empty($datos['documento'])) {
                        $errores[] = "Línea " . ($numeroLinea + 1) . ": Faltan campos obligatorios";
                        continue;
                    }

                    // Verificar si ya existe
                    $existe = Estudiante::where('codigo', $datos['codigo'])
                                      ->orWhere('documento', $datos['documento'])
                                      ->first();

                    if ($existe) {
                        $errores[] = "Línea " . ($numeroLinea + 1) . ": Estudiante con código {$datos['codigo']} o documento {$datos['documento']} ya existe";
                        continue;
                    }

                    Estudiante::create($datos);
                    $procesados++;

                } catch (\Exception $e) {
                    $errores[] = "Línea " . ($numeroLinea + 1) . ": " . $e->getMessage();
                }
            }

            $mensaje = "Procesados: {$procesados} estudiantes.";
            if (!empty($errores)) {
                $mensaje .= " Errores: " . implode(', ', array_slice($errores, 0, 3));
                if (count($errores) > 3) {
                    $mensaje .= " y " . (count($errores) - 3) . " más.";
                }
            }

            return redirect()
                ->route('estudiantes.index')
                ->with($procesados > 0 ? 'success' : 'warning', $mensaje);

        } catch (\Exception $e) {
            Log::error('Error en importación de estudiantes: ' . $e->getMessage());
            
            return redirect()
                ->route('estudiantes.index')
                ->with('error', 'Error al procesar los datos: ' . $e->getMessage());
        }
    }

    /**
     * Toggle active status
     */
    public function toggleActive(Estudiante $estudiante): RedirectResponse
    {
        $estudiante->update(['activo' => !$estudiante->activo]);
        
        $estado = $estudiante->activo ? 'activado' : 'desactivado';
        
        return redirect()
            ->route('estudiantes.index')
            ->with('success', "Estudiante {$estado} exitosamente.");
    }

    /**
     * Buscar estudiantes para autocompletado
     */
    public function buscarEstudiantes(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $estudiantes = Estudiante::activos()
            ->where(function($q) use ($query) {
                $q->where('nombre', 'LIKE', "%{$query}%")
                  ->orWhere('apellido_1', 'LIKE', "%{$query}%")
                  ->orWhere('apellido_2', 'LIKE', "%{$query}%")
                  ->orWhere('codigo', 'LIKE', "%{$query}%")
                  ->orWhere('documento', 'LIKE', "%{$query}%")
                  ->orWhereRaw("CONCAT(nombre, ' ', apellido_1, ' ', COALESCE(apellido_2, '')) LIKE ?", ["%{$query}%"]);
            })
            ->orderBy('apellido_1')
            ->orderBy('apellido_2')
            ->orderBy('nombre')
            ->limit(10)
            ->get()
            ->map(function($estudiante) {
                // Extraer solo el nombre (después de la coma) del campo nombre que contiene "APELLIDOS, NOMBRE"
                $nombreSolo = $estudiante->nombre;
                if (strpos($estudiante->nombre, ',') !== false) {
                    $partes = explode(',', $estudiante->nombre, 2);
                    $nombreSolo = isset($partes[1]) ? trim($partes[1]) : $estudiante->nombre;
                }
                
                return [
                    'id' => $estudiante->id,
                    'nombre' => $nombreSolo, // Solo el nombre, sin apellidos
                    'nombre_db' => $estudiante->nombre, // Nombre completo como está en BD
                    'apellido_1' => $estudiante->apellido_1,
                    'apellido_2' => $estudiante->apellido_2,
                    'nombre_completo' => $estudiante->nombre_completo,
                    'apellidos_completos' => trim($estudiante->apellido_1 . ' ' . ($estudiante->apellido_2 ?? '')),
                    'codigo' => $estudiante->codigo,
                    'documento' => $estudiante->documento,
                    'curso' => $estudiante->curso,
                    'eps' => $estudiante->eps,
                    'sexo' => $estudiante->sexo,
                    'tipo_sangre' => $estudiante->tipo_sangre,
                    'display' => $estudiante->nombre_completo . ' (' . $estudiante->codigo . ' - ' . $estudiante->documento . ')'
                ];
            });

        return response()->json($estudiantes);
    }
}
