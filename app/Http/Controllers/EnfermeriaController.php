<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\MotivoEnfermeria;
use App\Models\IngresoEstudiante;
use App\Models\IngresoColaborador;
use App\Models\Empleado;
use App\Exports\EnfermeriaEstudiantesExport;
use App\Mail\EnfermeriaReporteSent;
use App\Mail\EstudianteSinRutaNotification;
use App\Mail\EstudianteSinRutaBasico;
use Maatwebsite\Excel\Facades\Excel;

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
            'reporte_direccion_educacion' => 'nullable|string|max:10',
        ]);

        // Agregar el usuario que registra
        $validatedData['user_id'] = Auth::id();

        // Crear el registro en la base de datos
        $ingreso = IngresoEstudiante::create($validatedData);
        
        // DEBUG: Log para ver qué valor llega
        \Log::info('DEBUG - Valor de derivacion_estudiante recibido', [
            'derivacion' => $validatedData['derivacion_estudiante'],
            'tipo' => gettype($validatedData['derivacion_estudiante']),
            'isset' => isset($validatedData['derivacion_estudiante']),
            'empty' => empty($validatedData['derivacion_estudiante'])
        ]);
        
        // Verificar si el estudiante no tomará ruta (Salida a Casa o Salida al medico)
        if (isset($validatedData['derivacion_estudiante']) && 
            in_array($validatedData['derivacion_estudiante'], ['Salida a Casa', 'Salida al medico'])) {
            
            \Log::info('DEBUG - Condición cumplida, preparando envío de correo');
            
            try {
                $emailTestService = new \App\Services\EmailTestModeService();
                
                // Destinatarios para la notificación completa (asistentes)
                $asistenteBachillerato = $emailTestService->interceptEmail('asistentebachillerato@tvs.edu.co');
                $asistentePyP = $emailTestService->interceptEmail('asistentepyp@tvs.edu.co');
                
                // Destinatario para la notificación básica (transporte)
                $transporte = $emailTestService->interceptEmail('transporte@tvs.edu.co');
                
                \Log::info('Enviando notificación de estudiante sin ruta', [
                    'estudiante' => $ingreso->estudiante,
                    'derivacion' => $validatedData['derivacion_estudiante'],
                    'destinatarios_completos' => [$asistenteBachillerato, $asistentePyP],
                    'destinatario_basico' => $transporte
                ]);
                
                // Enviar notificación COMPLETA a los asistentes
                Mail::to($asistenteBachillerato)->send(
                    new EstudianteSinRutaNotification($ingreso, $validatedData['derivacion_estudiante'])
                );
                
                Mail::to($asistentePyP)->send(
                    new EstudianteSinRutaNotification($ingreso, $validatedData['derivacion_estudiante'])
                );
                
                // Enviar notificación BÁSICA a transporte
                Mail::to($transporte)->send(
                    new EstudianteSinRutaBasico($ingreso, $validatedData['derivacion_estudiante'])
                );
                
                \Log::info('✅ Notificaciones de ruta enviadas EXITOSAMENTE', [
                    'asistentes' => [$asistenteBachillerato, $asistentePyP],
                    'transporte' => $transporte
                ]);
                
            } catch (\Swift_TransportException $e) {
                \Log::error('❌ Error de transporte SMTP al enviar notificación de ruta', [
                    'error' => $e->getMessage(),
                    'estudiante' => $ingreso->estudiante,
                    'tipo_error' => 'SMTP Transport Error'
                ]);
            } catch (\Exception $e) {
                \Log::error('❌ Error general enviando notificación de ruta', [
                    'error' => $e->getMessage(),
                    'estudiante' => $ingreso->estudiante,
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
            }
        } else {
            \Log::info('DEBUG - Condición NO cumplida para envío de correo', [
                'derivacion_recibida' => $validatedData['derivacion_estudiante'] ?? 'NULL'
            ]);
        }
        
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
            'reporte_direccion_educacion' => 'nullable|string|max:10',
        ]);

        // Guardar el valor anterior de derivación para comparar
        $derivacionAnterior = $ingreso->derivacion_estudiante;

        // Actualizar el registro
        $ingreso->update($validatedData);
        
        // DEBUG: Log para ver qué valor llega
        \Log::info('DEBUG - Actualización - Valor de derivacion_estudiante', [
            'derivacion_anterior' => $derivacionAnterior,
            'derivacion_nueva' => $validatedData['derivacion_estudiante'],
            'cambiaron' => ($validatedData['derivacion_estudiante'] != $derivacionAnterior)
        ]);
        
        // Verificar si cambió la derivación y ahora es Salida a Casa o Salida al medico
        if ($validatedData['derivacion_estudiante'] != $derivacionAnterior && 
            isset($validatedData['derivacion_estudiante']) &&
            in_array($validatedData['derivacion_estudiante'], ['Salida a Casa', 'Salida al medico'])) {
            
            \Log::info('DEBUG - Condición de actualización cumplida, preparando envío de correo');
            
            try {
                $emailTestService = new \App\Services\EmailTestModeService();
                
                // Destinatarios para la notificación completa (asistentes)
                $asistenteBachillerato = $emailTestService->interceptEmail('asistentebachillerato@tvs.edu.co');
                $asistentePyP = $emailTestService->interceptEmail('asistentepyp@tvs.edu.co');
                
                // Destinatario para la notificación básica (transporte)
                $transporte = $emailTestService->interceptEmail('transporte@tvs.edu.co');
                
                \Log::info('Enviando notificación de estudiante sin ruta (actualización)', [
                    'estudiante' => $ingreso->estudiante,
                    'derivacion_anterior' => $derivacionAnterior,
                    'derivacion_nueva' => $validatedData['derivacion_estudiante'],
                    'destinatarios_completos' => [$asistenteBachillerato, $asistentePyP],
                    'destinatario_basico' => $transporte
                ]);
                
                // Enviar notificación COMPLETA a los asistentes
                Mail::to($asistenteBachillerato)->send(
                    new EstudianteSinRutaNotification($ingreso->fresh(), $validatedData['derivacion_estudiante'])
                );
                
                Mail::to($asistentePyP)->send(
                    new EstudianteSinRutaNotification($ingreso->fresh(), $validatedData['derivacion_estudiante'])
                );
                
                // Enviar notificación BÁSICA a transporte
                Mail::to($transporte)->send(
                    new EstudianteSinRutaBasico($ingreso->fresh(), $validatedData['derivacion_estudiante'])
                );
                
                \Log::info('✅ Notificaciones de ruta enviadas EXITOSAMENTE', [
                    'asistentes' => [$asistenteBachillerato, $asistentePyP],
                    'transporte' => $transporte
                ]);
                
            } catch (\Swift_TransportException $e) {
                \Log::error('❌ Error de transporte SMTP al enviar notificación de ruta (actualización)', [
                    'error' => $e->getMessage(),
                    'estudiante' => $ingreso->estudiante,
                    'tipo_error' => 'SMTP Transport Error'
                ]);
            } catch (\Exception $e) {
                \Log::error('❌ Error general enviando notificación de ruta (actualización)', [
                    'error' => $e->getMessage(),
                    'estudiante' => $ingreso->estudiante,
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
            }
        } else {
            \Log::info('DEBUG - Condición de actualización NO cumplida para envío de correo', [
                'derivacion_anterior' => $derivacionAnterior,
                'derivacion_nueva' => $validatedData['derivacion_estudiante'] ?? 'NULL'
            ]);
        }
        
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

    /**
     * Send nursing report via email
     */
    public function enviarReporteEstudiantes(Request $request)
    {
        $validated = $request->validate([
            'destinatario_email' => 'required|email',
            'destinatario_nombre' => 'required|string',
            'filtros' => 'nullable|array',
        ]);

        try {
            // Obtener los datos del reporte con los mismos filtros que la vista
            $query = IngresoEstudiante::selectRaw('
                DATE(fecha) as fecha,
                COUNT(CASE WHEN curso LIKE "%PREESCOLAR%" THEN 1 END) as preescolar,
                COUNT(CASE WHEN curso LIKE "%PRIMARIA%" OR curso LIKE "%PRIMERO%" OR curso LIKE "%SEGUNDO%" OR curso LIKE "%TERCERO%" OR curso LIKE "%CUARTO%" OR curso LIKE "%QUINTO%" THEN 1 END) as primaria,
                COUNT(CASE WHEN curso LIKE "%BACHILLERATO%" OR curso LIKE "%SEXTO%" OR curso LIKE "%SEPTIMO%" OR curso LIKE "%OCTAVO%" OR curso LIKE "%NOVENO%" OR curso LIKE "%DECIMO%" OR curso LIKE "%ONCE%" THEN 1 END) as bachillerato,
                COUNT(CASE WHEN curso LIKE "%DEPORTIV%" OR curso LIKE "%DEPORT%" THEN 1 END) as deportivas,
                COUNT(CASE WHEN curso LIKE "%ESPECIAL%" OR curso LIKE "%CASOS%" THEN 1 END) as casos_especiales,
                COUNT(CASE WHEN derivacion_estudiante = "Salida al medico" OR derivacion_estudiante = "Salida a Casa" THEN 1 END) as salidas,
                GROUP_CONCAT(DISTINCT CASE WHEN seguimiento IS NOT NULL AND seguimiento != "" THEN seguimiento END SEPARATOR " | ") as observaciones,
                GROUP_CONCAT(DISTINCT CASE WHEN motivo LIKE "%Emergencia%" OR motivo LIKE "%Accidente%" THEN CONCAT(motivo, ": ", descripcion_evento) END SEPARATOR " | ") as novedades
            ');

            // Aplicar filtros si existen
            $filtros = $request->input('filtros', []);
            
            if (!empty($filtros['fecha_desde'])) {
                $query->whereDate('fecha', '>=', $filtros['fecha_desde']);
            }
            
            if (!empty($filtros['fecha_hasta'])) {
                $query->whereDate('fecha', '<=', $filtros['fecha_hasta']);
            }

            $reporteData = $query->groupBy('fecha')
                                ->orderBy('fecha', 'desc')
                                ->get();

            // Generar el archivo Excel
            $fileName = 'Reporte_Enfermeria_Estudiantes_' . date('Y-m-d_His') . '.xlsx';
            $filePath = storage_path('app/temp/' . $fileName);

            // Asegurar que existe el directorio temp
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            Excel::store(new EnfermeriaEstudiantesExport($reporteData, $filtros), 'temp/' . $fileName);

            // Preparar información para el correo
            $dateRange = 'Todos los registros';
            if (!empty($filtros['fecha_desde']) && !empty($filtros['fecha_hasta'])) {
                $dateRange = date('d/m/Y', strtotime($filtros['fecha_desde'])) . ' - ' . date('d/m/Y', strtotime($filtros['fecha_hasta']));
            } elseif (!empty($filtros['fecha_desde'])) {
                $dateRange = 'Desde ' . date('d/m/Y', strtotime($filtros['fecha_desde']));
            } elseif (!empty($filtros['fecha_hasta'])) {
                $dateRange = 'Hasta ' . date('d/m/Y', strtotime($filtros['fecha_hasta']));
            }

            $totalRecords = $reporteData->count();

            // Enviar el correo
            Mail::to($validated['destinatario_email'])
                ->send(new EnfermeriaReporteSent(
                    $validated['destinatario_nombre'],
                    'Ingresos de Estudiantes',
                    $dateRange,
                    $totalRecords,
                    $filePath
                ));

            // Eliminar el archivo temporal después de enviarlo
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            return response()->json([
                'success' => true,
                'message' => 'Reporte enviado exitosamente a ' . $validated['destinatario_email']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el reporte: ' . $e->getMessage()
            ], 500);
        }
    }
}