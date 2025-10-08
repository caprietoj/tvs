<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class EmpleadosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['buscarEmpleados']);
        $this->middleware('can:view.enfermeria')->except(['buscarEmpleados']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $empleados = Empleado::activos()->ordenadosPorNombre()->paginate(20);
        
        $estadisticas = [
            'total' => Empleado::activos()->count(),
        ];
        
        return view('parametrizacion.empleados.index', compact('empleados', 'estadisticas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('parametrizacion.empleados.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'documento' => 'required|string|max:50|unique:empleados,documento',
            'email' => 'nullable|email|max:255',
            'area' => 'nullable|in:DOCENTE,ADMINISTRATIVO,SERV. GENS. Y MTO.,TRANSPORTE,OTRO',
            'eps' => 'nullable|string|max:255',
            'sexo' => 'nullable|in:M,F,Masculino,Femenino',
            'tipo_sangre' => 'nullable|string|max:10',
        ]);

        $data = $request->all();
        if (!empty($data['sexo'])) {
            $data['sexo'] = Empleado::normalizarSexo($data['sexo']);
        }
        if (!empty($data['tipo_sangre'])) {
            $data['tipo_sangre'] = Empleado::validarTipoSangre($data['tipo_sangre']);
        }

        Empleado::create($data);

        return redirect()
            ->route('empleados.index')
            ->with('success', 'Empleado registrado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Empleado $empleado): View
    {
        return view('parametrizacion.empleados.edit', compact('empleado'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Empleado $empleado): RedirectResponse
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'documento' => 'required|string|max:50|unique:empleados,documento,' . $empleado->id,
            'email' => 'nullable|email|max:255',
            'area' => 'nullable|in:DOCENTE,ADMINISTRATIVO,SERV. GENS. Y MTO.,TRANSPORTE,OTRO',
            'eps' => 'nullable|string|max:255',
            'sexo' => 'nullable|in:M,F,Masculino,Femenino',
            'tipo_sangre' => 'nullable|string|max:10',
            'activo' => 'boolean'
        ]);

        $data = $request->all();
        if (!empty($data['sexo'])) {
            $data['sexo'] = Empleado::normalizarSexo($data['sexo']);
        }
        if (!empty($data['tipo_sangre'])) {
            $data['tipo_sangre'] = Empleado::validarTipoSangre($data['tipo_sangre']);
        }

        $empleado->update($data);

        return redirect()
            ->route('empleados.index')
            ->with('success', 'Empleado actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Empleado $empleado): RedirectResponse
    {
        $empleado->update(['activo' => false]);

        return redirect()
            ->route('empleados.index')
            ->with('success', 'Empleado desactivado exitosamente.');
    }

    /**
     * Store multiple employees from Excel paste
     */
    public function storeMultiple(Request $request): RedirectResponse
    {
        $request->validate([
            'datos' => 'required|string',
            'area_masiva' => 'required|in:DOCENTE,ADMINISTRATIVO,SERV. GENS. Y MTO.,TRANSPORTE,OTRO'
        ]);

        $lineas = explode("\n", trim($request->datos));
        $registrados = 0;
        $errores = [];

        DB::beginTransaction();
        try {
            foreach ($lineas as $index => $linea) {
                $linea = trim($linea);
                if (empty($linea)) continue;

                // Separar por tabulador (Excel copy-paste)
                $columnas = preg_split('/\t/', $linea);
                
                if (count($columnas) < 2) {
                    $errores[] = "Línea " . ($index + 1) . ": Formato inválido (se esperan al menos 2 columnas)";
                    continue;
                }

                // Orden esperado: nombre_completo, documento, email, sexo, tipo_sangre
                $nombreCompleto = trim($columnas[0] ?? '');
                $documento = trim($columnas[1] ?? '');
                $email = trim($columnas[2] ?? '');
                $sexo = trim($columnas[3] ?? '');
                $tipoSangre = trim($columnas[4] ?? '');

                if (empty($nombreCompleto) || empty($documento)) {
                    $errores[] = "Línea " . ($index + 1) . ": Nombre y documento son obligatorios";
                    continue;
                }

                // Verificar si ya existe
                if (Empleado::where('documento', $documento)->exists()) {
                    $errores[] = "Línea " . ($index + 1) . ": El documento {$documento} ya existe";
                    continue;
                }

                $sexoNormalizado = !empty($sexo) ? Empleado::normalizarSexo($sexo) : null;
                $tipoSangreValidado = !empty($tipoSangre) ? Empleado::validarTipoSangre($tipoSangre) : null;

                Empleado::create([
                    'nombre_completo' => $nombreCompleto,
                    'documento' => $documento,
                    'email' => !empty($email) ? $email : null,
                    'area' => $request->area_masiva, // Área viene del select obligatorio
                    'eps' => null, // EPS se quedará como null
                    'sexo' => $sexoNormalizado,
                    'tipo_sangre' => $tipoSangreValidado,
                    'activo' => true
                ]);

                $registrados++;
            }

            DB::commit();

            $mensaje = "Se registraron {$registrados} empleado(s) exitosamente en el área {$request->area_masiva}.";
            if (count($errores) > 0) {
                $mensaje .= " Errores: " . implode(", ", $errores);
            }

            return redirect()
                ->route('empleados.index')
                ->with(count($errores) > 0 ? 'warning' : 'success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al registrar empleados: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint para buscar empleados (usado en autocompletado)
     */
    public function buscarEmpleados(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $empleados = Empleado::activos()
            ->where(function($q) use ($query) {
                $q->where('nombre_completo', 'LIKE', "%{$query}%")
                  ->orWhere('documento', 'LIKE', "%{$query}%");
            })
            ->orderBy('nombre_completo')
            ->limit(10)
            ->get()
            ->map(function($empleado) {
                return [
                    'id' => $empleado->id,
                    'nombre_completo' => $empleado->nombre_completo,
                    'documento' => $empleado->documento,
                    'email' => $empleado->email,
                    'area' => $empleado->area,
                    'eps' => $empleado->eps,
                    'sexo' => $empleado->sexo,
                    'tipo_sangre' => $empleado->tipo_sangre,
                    'display' => $empleado->nombre_completo . ' (' . $empleado->documento . ')'
                ];
            });

        return response()->json($empleados);
    }
}
