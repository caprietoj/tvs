<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MotivoEnfermeria;
use App\Models\IngresoEstudiante;
use App\Models\IngresoColaborador;
use App\Models\Empleado;

class EnfermeriaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the main page for student admission.
     */
    public function ingresoEstudiantes()
    {
        $ingresos = IngresoEstudiante::with('user')->recientes()->paginate(15);
        
        // Estadísticas
        $totalAtenciones = IngresoEstudiante::count();
        $atencionesHoy = IngresoEstudiante::whereDate('fecha', today())->count();
        $atencionesEstaSemana = IngresoEstudiante::whereBetween('fecha', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ])->count();
        $emergencias = IngresoEstudiante::where('motivo', 'like', '%emergencia%')
                                      ->orWhere('motivo', 'like', '%Emergencia%')
                                      ->count();
        $derivaciones = IngresoEstudiante::whereNotNull('derivacion_estudiante')
                                        ->where('derivacion_estudiante', '!=', '')
                                        ->count();
        
        return view('enfermeria.ingreso-estudiantes.index', compact(
            'ingresos', 
            'totalAtenciones', 
            'atencionesHoy', 
            'atencionesEstaSemana',
            'emergencias', 
            'derivaciones'
        ));
    }

    /**
     * Show the form for creating a new student admission record.
     */
    public function createIngresoEstudiante()
    {
        $motivos = MotivoEnfermeria::paraSelect();
        
        return view('enfermeria.ingreso-estudiantes.create', compact('motivos'));
    }

    /**
     * Store a newly created student admission record in storage.
     */
    public function storeIngresoEstudiante(Request $request)
    {
        // Validación de datos
        $validatedData = $request->validate([
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'estudiante' => 'required|string|max:255',
            'codigo_estudiante' => 'nullable|string|max:50',
            'documento_estudiante' => 'nullable|string|max:50',
            'apellidos_estudiante' => 'nullable|string|max:500',
            'eps_estudiante' => 'nullable|string|max:255',
            'sexo_estudiante' => 'nullable|in:M,F',
            'tipo_sangre_estudiante' => 'nullable|string|max:10',
            'estudiante_id' => 'nullable|exists:estudiantes,id',
            'curso' => 'required|string|max:50',
            'motivo' => 'required|string|max:500',
            'descripcion_evento' => 'required|string|max:1000',
            'accion_enfermeria' => 'required|string|max:1000',
            'seguimiento' => 'nullable|string|max:1000',
            'derivacion_estudiante' => 'nullable|string|max:500',
            'encuesta' => 'nullable|string|max:500',
            'encuesta_observaciones' => 'nullable|string|max:1000',
        ]);

        // Agregar el usuario que registra
        $validatedData['user_id'] = Auth::id();

        // Crear el registro en la base de datos
        IngresoEstudiante::create($validatedData);
        
        return redirect()
            ->route('enfermeria.ingreso_estudiantes.index')
            ->with('success', 'Registro de ingreso de estudiante creado exitosamente.');
    }

    /**
     * Display the specified student admission record.
     */
    public function showIngresoEstudiante($id)
    {
        $ingreso = IngresoEstudiante::with('user')->findOrFail($id);
        
        return view('enfermeria.ingreso-estudiantes.show', compact('ingreso'));
    }

    /**
     * Show the form for editing the specified student admission record.
     */
    public function editIngresoEstudiante($id)
    {
        $ingreso = IngresoEstudiante::findOrFail($id);
        $motivos = MotivoEnfermeria::paraSelect();
        
        return view('enfermeria.ingreso-estudiantes.edit', compact('ingreso', 'motivos'));
    }

    /**
     * Update the specified student admission record in storage.
     */
    public function updateIngresoEstudiante(Request $request, $id)
    {
        $ingreso = IngresoEstudiante::findOrFail($id);
        
        // Validación de datos
        $validatedData = $request->validate([
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'estudiante' => 'required|string|max:255',
            'codigo_estudiante' => 'nullable|string|max:50',
            'documento_estudiante' => 'nullable|string|max:50',
            'apellidos_estudiante' => 'nullable|string|max:500',
            'eps_estudiante' => 'nullable|string|max:255',
            'sexo_estudiante' => 'nullable|in:M,F',
            'tipo_sangre_estudiante' => 'nullable|string|max:10',
            'estudiante_id' => 'nullable|exists:estudiantes,id',
            'curso' => 'required|string|max:50',
            'motivo' => 'required|string|max:500',
            'descripcion_evento' => 'required|string|max:1000',
            'accion_enfermeria' => 'required|string|max:1000',
            'seguimiento' => 'nullable|string|max:1000',
            'derivacion_estudiante' => 'nullable|string|max:500',
            'encuesta' => 'nullable|string|max:500',
            'encuesta_observaciones' => 'nullable|string|max:1000',
        ]);

        // Actualizar el registro
        $ingreso->update($validatedData);
        
        return redirect()
            ->route('enfermeria.ingreso_estudiantes.index')
            ->with('success', 'Registro de ingreso de estudiante actualizado exitosamente.');
    }

    /**
     * Display report for student admissions grouped by date and area.
     */
    public function reporteEstudiantes()
    {
        // Obtener todos los ingresos agrupados por fecha
        $reporteData = IngresoEstudiante::selectRaw('
                DATE(fecha) as fecha,
                COUNT(CASE WHEN curso LIKE "%PREESCOLAR%" THEN 1 END) as preescolar,
                COUNT(CASE WHEN curso LIKE "%PRIMARIA%" OR curso LIKE "%PRIMERO%" OR curso LIKE "%SEGUNDO%" OR curso LIKE "%TERCERO%" OR curso LIKE "%CUARTO%" OR curso LIKE "%QUINTO%" THEN 1 END) as primaria,
                COUNT(CASE WHEN curso LIKE "%BACHILLERATO%" OR curso LIKE "%SEXTO%" OR curso LIKE "%SEPTIMO%" OR curso LIKE "%OCTAVO%" OR curso LIKE "%NOVENO%" OR curso LIKE "%DECIMO%" OR curso LIKE "%ONCE%" THEN 1 END) as bachillerato,
                COUNT(CASE WHEN curso LIKE "%DEPORTIV%" OR curso LIKE "%DEPORT%" THEN 1 END) as deportivas,
                COUNT(CASE WHEN curso LIKE "%ESPECIAL%" OR curso LIKE "%CASOS%" THEN 1 END) as casos_especiales,
                COUNT(CASE WHEN derivacion_estudiante = "Salida al medico" OR derivacion_estudiante = "Salida a Casa" THEN 1 END) as salidas,
                GROUP_CONCAT(DISTINCT CASE WHEN seguimiento IS NOT NULL AND seguimiento != "" THEN seguimiento END SEPARATOR " | ") as observaciones,
                GROUP_CONCAT(DISTINCT CASE WHEN motivo LIKE "%Emergencia%" OR motivo LIKE "%Accidente%" THEN CONCAT(motivo, ": ", descripcion_evento) END SEPARATOR " | ") as novedades
            ')
            ->groupBy('fecha')
            ->orderBy('fecha', 'desc')
            ->get();

        return view('enfermeria.reporte-estudiantes', compact('reporteData'));
    }

    /**
     * Display report for collaborator admissions grouped by date and area.
     */
    public function reporteColaboradores()
    {
        // Obtener todos los ingresos de colaboradores agrupados por fecha
        $reporteData = IngresoColaborador::selectRaw('
                DATE(fecha) as fecha,
                COUNT(CASE WHEN area_colaborador LIKE "%Docente%" OR area_colaborador LIKE "%Profesor%" THEN 1 END) as profesores,
                COUNT(CASE WHEN area_colaborador LIKE "%Administrativo%" OR area_colaborador LIKE "%Admin%" OR area_colaborador NOT LIKE "%Docente%" AND area_colaborador NOT LIKE "%Profesor%" THEN 1 END) as administrativos,
                GROUP_CONCAT(DISTINCT CASE WHEN seguimiento IS NOT NULL AND seguimiento != "" THEN seguimiento END SEPARATOR " | ") as observaciones,
                GROUP_CONCAT(DISTINCT CASE WHEN motivo LIKE "%Emergencia%" OR motivo LIKE "%Accidente%" THEN CONCAT(motivo, ": ", descripcion_evento) END SEPARATOR " | ") as novedades
            ')
            ->groupBy('fecha')
            ->orderBy('fecha', 'desc')
            ->get();

        return view('enfermeria.reporte-colaboradores', compact('reporteData'));
    }

    /**
     * Display the main page for employee/collaborator admission.
     */
    public function ingresoColaboradores()
    {
        $ingresos = IngresoColaborador::with(['empleado', 'user'])
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->paginate(15);
        
        // Estadísticas
        $totalAtenciones = IngresoColaborador::count();
        $atencionesHoy = IngresoColaborador::whereDate('fecha', today())->count();
        $atencionesEstaSemana = IngresoColaborador::whereBetween('fecha', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ])->count();
        
        return view('enfermeria.ingreso-colaboradores.index', compact(
            'ingresos',
            'totalAtenciones',
            'atencionesHoy',
            'atencionesEstaSemana'
        ));
    }

    /**
     * Show the form for creating a new collaborator admission.
     */
    public function createIngresoColaborador()
    {
        $motivos = MotivoEnfermeria::where('activo', true)
            ->orderBy('orden')
            ->get();
        
        return view('enfermeria.ingreso-colaboradores.create', compact('motivos'));
    }

    /**
     * Store a newly created collaborator admission in storage.
     */
    public function storeIngresoColaborador(Request $request)
    {
        // Validación de datos
        $validatedData = $request->validate([
            'empleado_id' => 'nullable|exists:empleados,id',
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'nombre_completo' => 'required|string|max:255',
            'documento_colaborador' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'area_colaborador' => 'required|in:DOCENTE,ADMINISTRATIVO,SERV. GENS. Y MTO.,TRANSPORTE,OTRO',
            'eps_colaborador' => 'nullable|string|max:255',
            'sexo_colaborador' => 'nullable|in:M,F',
            'tipo_sangre_colaborador' => 'nullable|string|max:10',
            'motivo' => 'required|string|max:500',
            'descripcion_evento' => 'required|string|max:1000',
            'accion_enfermeria' => 'required|string|max:1000',
            'seguimiento' => 'nullable|string|max:1000',
            'derivacion_colaborador' => 'nullable|string|max:500',
            'encuesta' => 'nullable|string|max:500',
            'encuesta_observaciones' => 'nullable|string|max:1000',
        ]);

        // Agregar el usuario que registra
        $validatedData['user_id'] = Auth::id();

        // Crear el registro en la base de datos
        IngresoColaborador::create($validatedData);
        
        return redirect()
            ->route('enfermeria.ingreso_colaboradores.index')
            ->with('success', 'Registro de ingreso de colaborador creado exitosamente.');
    }

    /**
     * Display the specified colaborador admission record.
     */
    public function showIngresoColaborador($id)
    {
        $ingreso = IngresoColaborador::with('user')->findOrFail($id);
        
        return view('enfermeria.ingreso-colaboradores.show', compact('ingreso'));
    }

    /**
     * Show the form for editing the specified colaborador admission record.
     */
    public function editIngresoColaborador($id)
    {
        $ingreso = IngresoColaborador::findOrFail($id);
        $motivos = MotivoEnfermeria::paraSelect();
        
        return view('enfermeria.ingreso-colaboradores.edit', compact('ingreso', 'motivos'));
    }

    /**
     * Update the specified colaborador admission record in storage.
     */
    public function updateIngresoColaborador(Request $request, $id)
    {
        $ingreso = IngresoColaborador::findOrFail($id);
        
        // Validación de datos
        $validatedData = $request->validate([
            'empleado_id' => 'nullable|exists:empleados,id',
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'nombre_completo' => 'required|string|max:255',
            'documento_colaborador' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'area_colaborador' => 'required|in:DOCENTE,ADMINISTRATIVO,SERV. GENS. Y MTO.,TRANSPORTE,OTRO',
            'eps_colaborador' => 'nullable|string|max:255',
            'sexo_colaborador' => 'nullable|in:M,F',
            'tipo_sangre_colaborador' => 'nullable|string|max:10',
            'motivo' => 'required|string|max:500',
            'descripcion_evento' => 'required|string|max:1000',
            'accion_enfermeria' => 'required|string|max:1000',
            'seguimiento' => 'nullable|string|max:1000',
            'derivacion_colaborador' => 'nullable|string|max:500',
            'encuesta' => 'nullable|string|max:500',
            'encuesta_observaciones' => 'nullable|string|max:1000',
        ]);

        // Actualizar el registro
        $ingreso->update($validatedData);
        
        return redirect()
            ->route('enfermeria.ingreso_colaboradores.index')
            ->with('success', 'Registro de ingreso de colaborador actualizado exitosamente.');
    }

    /**
     * Remove the specified colaborador admission record from storage.
     */
    public function destroyIngresoColaborador($id)
    {
        $ingreso = IngresoColaborador::findOrFail($id);
        $ingreso->delete();
        
        return redirect()
            ->route('enfermeria.ingreso_colaboradores.index')
            ->with('success', 'Registro de ingreso de colaborador eliminado exitosamente.');
    }
}