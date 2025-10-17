<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PersonasController extends Controller
{
    /**
     * Constructor con middleware de permisos
     */
    public function __construct()
    {
        $this->middleware('permission:admin.personas');
    }

    /**
     * Mostrar listado de personas
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $personas = Persona::query()->orderBy('created_at', 'desc');

            return DataTables::of($personas)
                ->addColumn('nombre_completo', function ($persona) {
                    return $persona->nombre_completo;
                })
                ->addColumn('tipo_badge', function ($persona) {
                    return $persona->tipo_badge;
                })
                ->addColumn('estado_badge', function ($persona) {
                    return $persona->estado_badge;
                })
                ->addColumn('actions', function ($persona) {
                    return '
                        <div class="btn-group" role="group">
                            <a href="' . route('porteria.personas.edit', $persona->id) . '" 
                               class="btn btn-sm btn-info" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" 
                                    class="btn btn-sm btn-danger btn-delete" 
                                    data-id="' . $persona->id . '"
                                    data-nombre="' . $persona->nombre_completo . '"
                                    title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['tipo_badge', 'estado_badge', 'actions'])
                ->make(true);
        }

        $estadisticas = [
            'total' => Persona::count(),
            'empleados' => Persona::empleados()->count(),
            'estudiantes' => Persona::estudiantes()->count(),
            'activos' => Persona::activas()->count(),
        ];

        return view('porteria.personas.index', compact('estadisticas'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('porteria.personas.create');
    }

    /**
     * Guardar nueva persona
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'documento' => 'required|string|max:50|unique:personas,documento',
            'nombre' => 'required|string|max:100',
            'tipo_persona' => 'required|in:empleado,estudiante',
            'email' => 'nullable|email|max:150',
            'telefono' => 'nullable|string|max:20',
            'grado' => 'nullable|string|max:50',
            'observaciones' => 'nullable|string',
            'activo' => 'boolean',
        ], [
            'documento.required' => 'El documento es obligatorio',
            'documento.unique' => 'Este documento ya está registrado',
            'nombre.required' => 'El nombre completo es obligatorio',
            'tipo_persona.required' => 'Debe seleccionar el tipo de persona',
            'email.email' => 'Debe ingresar un email válido',
        ]);

        try {
            Persona::create($validated);

            return redirect()->route('porteria.personas.index')
                ->with('success', 'Persona registrada exitosamente');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error al registrar: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Persona $persona)
    {
        return view('porteria.personas.edit', compact('persona'));
    }

    /**
     * Actualizar persona
     */
    public function update(Request $request, Persona $persona)
    {
        $validated = $request->validate([
            'documento' => 'required|string|max:50|unique:personas,documento,' . $persona->id,
            'nombre' => 'required|string|max:100',
            'tipo_persona' => 'required|in:empleado,estudiante',
            'email' => 'nullable|email|max:150',
            'telefono' => 'nullable|string|max:20',
            'grado' => 'nullable|string|max:50',
            'observaciones' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        try {
            $persona->update($validated);

            return redirect()->route('porteria.personas.index')
                ->with('success', 'Persona actualizada exitosamente');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar persona
     */
    public function destroy(Persona $persona)
    {
        try {
            $persona->delete();

            return response()->json([
                'success' => true,
                'message' => 'Persona eliminada exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar formulario de importación
     */
    public function importForm()
    {
        return view('porteria.personas.import');
    }

    /**
     * Determinar tipo de persona según el cargo
     */
    private function determinarTipoPersona($cargo)
    {
        // Lista de tipos de empleados
        $tiposEmpleado = [
            'Administracion',
            'Docentes Bachillerato',
            'Docentes Preescolar y Primaria',
            'EMC',
            'Depto de Apoyo',
            'Mantenimiento',
            'Servicios Generales',
            'PRACTICANTE',
            'Coordinacion',
            'Rectoria',
            'Secretaria',
            'Biblioteca',
            'Enfermeria',
            'Sistemas',
            'Contabilidad',
            'Pastoral'
        ];

        // Verificar si es empleado
        foreach ($tiposEmpleado as $tipo) {
            if (stripos($cargo, $tipo) !== false) {
                return 'empleado';
            }
        }

        // Si no es empleado, es estudiante (incluye grados como "Primero 1A", "Sexto 6B", etc.)
        return 'estudiante';
    }

    /**
     * Procesar importación desde Excel (copiar/pegar)
     */
    public function import(Request $request)
    {
        $request->validate([
            'data' => 'required|string',
        ], [
            'data.required' => 'Debe pegar los datos de Excel',
        ]);

        try {
            DB::beginTransaction();

            $lineas = explode("\n", trim($request->data));
            $importados = 0;
            $actualizados = 0;
            $errores = [];

            foreach ($lineas as $index => $linea) {
                $linea = trim($linea);
                if (empty($linea)) continue;

                // Detectar si hay tabuladores (formato correcto)
                if (strpos($linea, "\t") === false) {
                    $errores[] = "Línea " . ($index + 1) . ": No se detectaron tabuladores. Use copiar/pegar desde Excel";
                    continue;
                }

                // Separar por tabulador (formato Excel)
                $columnas = explode("\t", $linea);
                
                // Limpiar espacios en blanco de cada columna
                $columnas = array_map('trim', $columnas);

                if (count($columnas) < 3) {
                    $errores[] = "Línea " . ($index + 1) . ": Se requieren al menos 3 columnas (Documento, Nombre, Tipo/Cargo)";
                    continue;
                }

                // Formato esperado: Documento | Nombre | Tipo/Cargo
                // La columna 3 (Tipo/Cargo) ahora va a tipo_persona
                $documento = substr($columnas[0] ?? '', 0, 50); // Max 50
                $nombre = substr($columnas[1] ?? '', 0, 100);   // Max 100
                $tipoCargo = trim($columnas[2] ?? '');          // Tipo de persona o cargo
                
                // Validar datos mínimos
                if (empty($documento) || empty($nombre)) {
                    $errores[] = "Línea " . ($index + 1) . ": Faltan datos obligatorios (Documento: '{$documento}', Nombre: '{$nombre}')";
                    continue;
                }

                // Validar documento numérico
                if (!is_numeric($documento)) {
                    $errores[] = "Línea " . ($index + 1) . ": El documento debe ser numérico (recibido: '{$documento}')";
                    continue;
                }

                // Validar longitud del tipo/cargo
                if (strlen($tipoCargo) > 50) {
                    $tipoCargo = substr($tipoCargo, 0, 50);
                }

                // Determinar tipo de persona automáticamente según el cargo
                $tipoPersona = $this->determinarTipoPersona($tipoCargo);

                // Preparar datos para insertar/actualizar
                $datos = [
                    'nombre' => $nombre,
                    'tipo_persona' => $tipoPersona, // 'empleado' o 'estudiante'
                    'grado' => $tipoCargo, // Guardar el cargo completo en grado
                    'activo' => true,
                ];

                // Buscar si ya existe la persona
                $persona = Persona::where('documento', $documento)->first();

                if ($persona) {
                    // Actualizar registro existente
                    $persona->update($datos);
                    $actualizados++;
                } else {
                    // Crear nuevo registro
                    Persona::create(array_merge(['documento' => $documento], $datos));
                    $importados++;
                }
            }

            DB::commit();

            // Preparar mensaje de resultado
            if ($importados === 0 && $actualizados === 0 && count($errores) === 0) {
                return back()->withInput()
                    ->with('error', '❌ No se procesó ningún registro. Verifique el formato de los datos.');
            }

            $mensaje = "✓ Importación completada: {$importados} personas creadas, {$actualizados} actualizadas";
            
            
            if (count($errores) > 0) {
                $listaErrores = implode('<br>• ', array_slice($errores, 0, 5)); // Mostrar máximo 5 errores
                $totalErrores = count($errores);
                $mensaje .= "<br><br><strong>Errores encontrados ({$totalErrores}):</strong><br>• {$listaErrores}";
                if ($totalErrores > 5) {
                    $mensaje .= "<br>... y " . ($totalErrores - 5) . " errores más";
                }
            }

            // Si hay más errores que importados, mostrar como advertencia
            if (count($errores) > $importados && $importados > 0) {
                return redirect()->route('porteria.personas.index')
                    ->with('warning', $mensaje);
            }

            return redirect()->route('porteria.personas.index')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Mensaje de error más detallado
            $errorMsg = $e->getMessage();
            
            // Detectar errores comunes y dar soluciones
            if (str_contains($errorMsg, 'Data too long')) {
                $errorMsg = "❌ Los datos son demasiado largos para algún campo. Verifique que:<br>" .
                           "• El documento tenga máximo 50 caracteres<br>" .
                           "• Nombre y apellido máximo 100 caracteres cada uno<br>" .
                           "• Teléfono máximo 20 caracteres<br>" .
                           "• Email máximo 150 caracteres<br>" .
                           "• Grado máximo 50 caracteres<br><br>" .
                           "<strong>Asegúrese de copiar los datos correctamente desde Excel con tabuladores entre columnas.</strong>";
            } elseif (str_contains($errorMsg, 'Duplicate entry')) {
                $errorMsg = "❌ Ya existe una persona con ese documento en la base de datos.";
            }
            
            return back()->withInput()
                ->with('error', $errorMsg);
        }
    }
}
