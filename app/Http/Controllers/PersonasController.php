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
            'apellido' => 'required|string|max:100',
            'tipo_persona' => 'required|in:empleado,estudiante',
            'email' => 'nullable|email|max:150',
            'telefono' => 'nullable|string|max:20',
            'grado' => 'nullable|string|max:50',
            'observaciones' => 'nullable|string',
            'activo' => 'boolean',
        ], [
            'documento.required' => 'El documento es obligatorio',
            'documento.unique' => 'Este documento ya está registrado',
            'nombre.required' => 'El nombre es obligatorio',
            'apellido.required' => 'El apellido es obligatorio',
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
            'apellido' => 'required|string|max:100',
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
            $errores = [];
            $duplicados = 0;

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
                    $errores[] = "Línea " . ($index + 1) . ": Se requieren al menos 3 columnas (Documento, Nombre, Apellido)";
                    continue;
                }

                // Formato esperado: Documento | Nombre | Apellido | Tipo | Email | Teléfono | Grado
                $documento = substr($columnas[0] ?? '', 0, 50); // Max 50
                $nombre = substr($columnas[1] ?? '', 0, 100);   // Max 100
                $apellido = substr($columnas[2] ?? '', 0, 100); // Max 100
                $tipo = strtolower($columnas[3] ?? 'estudiante');
                $email = substr($columnas[4] ?? '', 0, 150);    // Max 150
                $telefono = substr($columnas[5] ?? '', 0, 20);  // Max 20
                $grado = substr($columnas[6] ?? '', 0, 50);     // Max 50

                // Validar tipo
                if (!in_array($tipo, ['empleado', 'estudiante'])) {
                    $tipo = 'estudiante';
                }

                // Validar datos mínimos
                if (empty($documento) || empty($nombre) || empty($apellido)) {
                    $errores[] = "Línea " . ($index + 1) . ": Faltan datos obligatorios (Documento: '{$documento}', Nombre: '{$nombre}', Apellido: '{$apellido}')";
                    continue;
                }

                // Validar documento numérico
                if (!is_numeric($documento)) {
                    $errores[] = "Línea " . ($index + 1) . ": El documento debe ser numérico (recibido: '{$documento}')";
                    continue;
                }

                // Verificar si ya existe
                $existe = Persona::where('documento', $documento)->exists();
                if ($existe) {
                    $duplicados++;
                    continue;
                }

                // Validar email si se proporciona
                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $email = null; // Si el email no es válido, se guarda como null
                }

                // Crear persona
                Persona::create([
                    'documento' => $documento,
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'tipo_persona' => $tipo,
                    'email' => $email ?: null,
                    'telefono' => $telefono ?: null,
                    'grado' => $grado ?: null,
                    'activo' => true,
                ]);

                $importados++;
            }

            DB::commit();

            // Preparar mensaje de resultado
            if ($importados === 0 && count($errores) === 0 && $duplicados === 0) {
                return back()->withInput()
                    ->with('error', '❌ No se importó ningún registro. Verifique el formato de los datos.');
            }

            $mensaje = "✓ Importación completada: {$importados} personas registradas";
            if ($duplicados > 0) {
                $mensaje .= ", {$duplicados} duplicados omitidos";
            }
            
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
