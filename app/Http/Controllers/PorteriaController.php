<?php

namespace App\Http\Controllers;

use App\Models\RegistroPorteria;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class PorteriaController extends Controller
{
    /**
     * Constructor con middleware de permisos
     */
    public function __construct()
    {
        $this->middleware('permission:porteria.registro');
    }

    /**
     * Mostrar el registro de portería
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Obtener la fecha del filtro o usar hoy como predeterminado
            $fecha = $request->input('fecha', Carbon::today()->format('Y-m-d'));
            
            $registros = RegistroPorteria::with('usuario')
                ->whereDate('fecha', $fecha)
                ->orderBy('created_at', 'desc')
                ->get();

            return DataTables::of($registros)
                ->addColumn('nombre_completo', function ($registro) {
                    // El nombre ya contiene el nombre completo
                    return trim($registro->nombre);
                })
                ->addColumn('estatus_badge', function ($registro) {
                    $badges = [
                        'empleado' => '<span class="badge badge-primary"><i class="fas fa-user-tie"></i> Empleado</span>',
                        'estudiante' => '<span class="badge badge-success"><i class="fas fa-graduation-cap"></i> Estudiante</span>',
                        'externo' => '<span class="badge badge-warning"><i class="fas fa-user"></i> Visitante</span>',
                    ];
                    return $badges[$registro->tipo_persona] ?? '<span class="badge badge-secondary">Sin Tipo</span>';
                })
                ->addColumn('tipo_registro_badge', function ($registro) {
                    if ($registro->tipo_registro === 'salida') {
                        return '<span class="badge badge-warning"><i class="fas fa-sign-out-alt"></i> Salida</span>';
                    }
                    return '<span class="badge badge-success"><i class="fas fa-sign-in-alt"></i> Entrada</span>';
                })
                ->addColumn('hora_entrada_formatted', function ($registro) {
                    return $registro->hora_entrada
                        ? Carbon::parse($registro->hora_entrada)->format('h:i A')
                        : '<span class="text-muted">-</span>';
                })
                ->addColumn('hora_salida_formatted', function ($registro) {
                    return $registro->hora_salida 
                        ? Carbon::parse($registro->hora_salida)->format('h:i A')
                        : '<span class="text-muted">-</span>';
                })
                ->addColumn('fecha_formatted', function ($registro) {
                    return Carbon::parse($registro->fecha)->format('d/m/Y');
                })
                ->addColumn('observaciones_formatted', function ($registro) {
                    if ($registro->observaciones) {
                        $observaciones = htmlspecialchars($registro->observaciones);
                        if (strlen($observaciones) > 50) {
                            return '<span title="' . $observaciones . '">' . substr($observaciones, 0, 50) . '...</span>';
                        }
                        return $observaciones;
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('acciones', function ($registro) {
                    // Solo mostrar botones si el usuario es admin
                    if (auth()->user()->hasRole('admin')) {
                        return '
                            <button class="btn btn-sm btn-info btn-editar" data-id="'.$registro->id.'" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-eliminar" data-id="'.$registro->id.'" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        ';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->rawColumns(['estatus_badge', 'tipo_registro_badge', 'hora_entrada_formatted', 'hora_salida_formatted', 'observaciones_formatted', 'acciones'])
                ->make(true);
        }

        return view('porteria.registro.index');
    }

    /**
     * Verificar si una persona es visitante
     */
    public function verificar(Request $request)
    {
        $request->validate([
            'documento' => 'required|string|max:50',
        ]);

        $documento = trim($request->documento);
        
        // Log para debug
        \Log::info('Verificando documento: ' . $documento);
        
        // Verificar si ya existe una entrada abierta de hoy (para salida)
        $registroExistente = RegistroPorteria::registroAbiertoHoy($documento);
        
        if ($registroExistente) {
            // Ya tiene entrada, es para registrar salida, no es visitante
            \Log::info('Registro existente encontrado para salida: ' . $documento);
            return response()->json([
                'es_visitante' => false,
                'tiene_entrada' => true,
                'accion_sugerida' => 'salida',
            ]);
        }

        // Buscar en tabla de personas
        $persona = \App\Models\Persona::buscarPorDocumento($documento)
            ->activas()
            ->first();

        if ($persona) {
            // Se encontró en personas, no es visitante
            $tipoPersona = $this->determinarTipoPersona($persona);
            \Log::info('Persona encontrada en BD: ' . $documento . ' - Tipo: ' . $tipoPersona . ' - Nombre: ' . $persona->nombre);
            return response()->json([
                'es_visitante' => false,
                'tipo' => 'persona_registrada',
                'nombre' => $persona->nombre,
                'tipo_persona' => $tipoPersona,
                'accion_sugerida' => RegistroPorteria::determinarAccion($documento, null, $tipoPersona),
            ]);
        }

        // No se encontró, es visitante
        \Log::info('Persona NO encontrada en BD (visitante): ' . $documento);
        return response()->json([
            'es_visitante' => true,
            'tipo' => 'externo',
            'accion_sugerida' => 'entrada',
        ]);
    }

    /**
     * Registrar entrada o salida
     */
    public function store(Request $request)
    {
        $request->validate([
            'documento' => 'required|string|max:50',
            'accion' => 'nullable|in:auto,entrada,salida',
        ]);

        $documento = trim($request->documento);
        $hoy = Carbon::today();
        $momento = Carbon::now();
        $horaActual = $momento->format('H:i:s');
        $accionSolicitada = $request->input('accion', 'auto');

        // Registro abierto del día (entrada sin salida)
        $registroAbierto = RegistroPorteria::registroAbiertoHoy($documento, $momento);

        // Si existe una entrada abierta, el escaneo corresponde a una salida.
        // Se resuelve aquí, antes de buscar a la persona, para que los visitantes
        // (que no están en la tabla de personas) también puedan registrar su salida.
        if ($registroAbierto && $accionSolicitada !== 'entrada') {
            try {
                DB::beginTransaction();

                $registroAbierto->update([
                    'hora_salida' => $horaActual,
                ]);

                DB::commit();

                \Log::info('Salida registrada para registro abierto ID: ' . $registroAbierto->id);

                return response()->json([
                    'success' => true,
                    'action' => 'salida',
                    'message' => "Salida registrada para {$registroAbierto->nombre_completo}",
                    'data' => $registroAbierto,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Error al registrar la salida: ' . $e->getMessage(),
                ], 500);
            }
        }

        // A partir de aquí se creará un registro nuevo: obtener nombre y tipo
        if ($request->filled('nombre')) {
            \Log::info('Visitante detectado en store() - Nombre proporcionado');
            $nombre = trim($request->nombre);
            $tipo = 'externo';
        } else {
            \Log::info('Persona registrada en store() - Buscando en BD: ' . $documento);
            $persona = $this->buscarPersona($documento);

            if ($persona['nombre'] === null) {
                // Un visitante ya registrado hoy puede no estar en la tabla de personas.
                // Se reutilizan los datos de su último registro del día si existe.
                $registroPrevio = RegistroPorteria::registroHoy($documento);

                if (!$registroPrevio) {
                    \Log::error('Persona NO encontrada en BD durante store(): ' . $documento);
                    return response()->json([
                        'success' => false,
                        'message' => 'Persona no encontrada. Por favor registre los datos del visitante.',
                    ], 404);
                }

                \Log::info('Reutilizando datos de registro previo para: ' . $documento);
                $nombre = $registroPrevio->nombre;
                $tipo = $registroPrevio->tipo_persona;
            } else {
                \Log::info('Persona encontrada: ' . $persona['nombre'] . ' - Tipo: ' . $persona['tipo']);
                $nombre = $persona['nombre'];
                $tipo = $persona['tipo'];
            }
        }

        try {
            DB::beginTransaction();

            // Determinar si el escaneo es entrada o salida
            $accion = $accionSolicitada === 'auto'
                ? RegistroPorteria::determinarAccion($documento, $momento, $tipo)
                : $accionSolicitada;

            \Log::info("Acción determinada para {$documento}: {$accion} (solicitada: {$accionSolicitada})");

            if ($accion === 'salida') {
                // Salida sin entrada previa (por ejemplo, empleado que solo marca su salida)
                $registro = RegistroPorteria::create([
                    'documento' => $documento,
                    'nombre' => $nombre,
                    'tipo_persona' => $tipo,
                    'fecha' => $hoy,
                    'hora_entrada' => null,
                    'hora_salida' => $horaActual,
                    'observaciones' => $request->input('observaciones'),
                    'user_id' => auth()->id(),
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'action' => 'salida',
                    'message' => "Salida registrada para {$registro->nombre_completo} ({$tipo})",
                    'data' => $registro,
                ]);
            }

            // Crear nuevo registro de entrada
            $registro = RegistroPorteria::create([
                'documento' => $documento,
                'nombre' => $nombre,
                'tipo_persona' => $tipo,
                'fecha' => $hoy,
                'hora_entrada' => $horaActual,
                'observaciones' => $request->input('observaciones'),
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'action' => 'entrada',
                'message' => "Entrada registrada para {$registro->nombre_completo} ({$tipo})",
                'data' => $registro,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Buscar información de la persona en la base de datos
     */
    private function buscarPersona(string $documento): array
    {
        // Buscar en tabla de personas
        $persona = \App\Models\Persona::buscarPorDocumento($documento)
            ->activas()
            ->first();

        if ($persona) {
            // El nombre completo está en el campo 'nombre'
            // DETERMINAR EL TIPO AUTOMÁTICAMENTE basado en el grado
            $tipo = $this->determinarTipoPersona($persona);
            
            return [
                'nombre' => trim($persona->nombre),
                'tipo' => $tipo,
                'grado' => $persona->grado,
            ];
        }

        // Si no se encuentra, retornar null para indicar que es visitante
        return [
            'nombre' => null,
            'tipo' => 'externo',
            'grado' => null,
        ];
    }

    /**
     * Determinar el tipo de persona basándose en el grado
     */
    private function determinarTipoPersona($persona): string
    {
        $grado = trim($persona->grado ?? '');
        
        // Lista de cargos/áreas que indican que es empleado
        $cargosEmpleado = [
            'Administracion', 'Docente', 'Coordinacion', 'Asistente',
            'Servicios', 'Biblioteca', 'Enfermeria', 'Mantenimiento',
            'Sistemas', 'Contabilidad', 'Rectoria', 'Rectoría',
            'Secretaria', 'Pastoral', 'Psicologia', 'Dirección',
            'Direccion', 'Tesoreria', 'Almacen', 'Bodega',
        ];
        
        // Si el grado coincide con algún cargo administrativo, es empleado
        foreach ($cargosEmpleado as $cargo) {
            if (stripos($grado, $cargo) !== false) {
                return 'empleado';
            }
        }
        
        // Si el grado está vacío, usar el tipo_persona de la BD
        if (empty($grado)) {
            return $persona->tipo_persona;
        }
        
        // Si el grado parece ser un grado escolar (contiene números), es estudiante
        if (preg_match('/\d+/', $grado)) {
            return 'estudiante';
        }
        
        // Por defecto, usar el tipo_persona de la BD
        return $persona->tipo_persona;
    }

    /**
     * Obtener un registro específico para editar (solo admin)
     */
    public function edit($id)
    {
        // Verificar que el usuario sea admin
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción.',
            ], 403);
        }

        try {
            $registro = RegistroPorteria::find($id);
            
            if (!$registro) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registro no encontrado.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $registro->id,
                    'documento' => $registro->documento,
                    'nombre' => $registro->nombre,
                    'tipo_persona' => $registro->tipo_persona,
                    'hora_entrada' => $registro->hora_entrada ? Carbon::parse($registro->hora_entrada)->format('H:i') : '',
                    'hora_salida' => $registro->hora_salida ? Carbon::parse($registro->hora_salida)->format('H:i') : '',
                    'observaciones' => $registro->observaciones ?? '',
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el registro: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar un registro de portería (solo admin)
     */
    public function update(Request $request, $id)
    {
        // Verificar que el usuario sea admin
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción.',
            ], 403);
        }

        $request->validate([
            'documento' => 'required|string|max:50',
            'nombre' => 'required|string|max:100',
            'tipo_persona' => 'required|in:empleado,estudiante,externo',
            'hora_entrada' => 'nullable|date_format:H:i',
            'hora_salida' => 'nullable|date_format:H:i',
            'observaciones' => 'nullable|string',
        ]);

        try {
            $registro = RegistroPorteria::findOrFail($id);
            
            $registro->update([
                'documento' => $request->documento,
                'nombre' => $request->nombre,
                'tipo_persona' => $request->tipo_persona,
                'hora_entrada' => $request->hora_entrada,
                'hora_salida' => $request->hora_salida,
                'observaciones' => $request->observaciones,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registro actualizado correctamente.',
                'data' => $registro,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el registro: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar un registro de portería (solo admin)
     */
    public function destroy($id)
    {
        // Verificar que el usuario sea admin
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción.',
            ], 403);
        }

        try {
            \Log::info('Intentando eliminar registro ID: ' . $id);
            
            $registro = RegistroPorteria::find($id);
            
            if (!$registro) {
                \Log::error('Registro no encontrado - ID: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'Registro no encontrado.',
                ], 404);
            }
            
            $nombreCompleto = $registro->nombre_completo;
            \Log::info('Eliminando registro: ' . $nombreCompleto . ' (ID: ' . $id . ')');
            
            $registro->delete();

            return response()->json([
                'success' => true,
                'message' => "Registro de {$nombreCompleto} eliminado correctamente.",
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar registro: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el registro: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener todos los registros del día
     */
    public function getRegistrosHoy()
    {
        $registros = RegistroPorteria::whereDate('fecha', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($registros);
    }
}
