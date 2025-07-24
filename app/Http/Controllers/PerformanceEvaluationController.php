<?php

namespace App\Http\Controllers;

use App\Models\PerformanceEvaluation;
use App\Models\User;
use App\Exports\PerformanceEvaluationsExport;
use App\Mail\PerformanceEvaluationCreated;
use App\Services\EmailTestModeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class PerformanceEvaluationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar lista de evaluaciones
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Verificar permisos de acceso
        if (!$user->hasRole('admin') && 
            !$user->can('view-all-performance-evaluations') && 
            !$user->can('view-own-performance-evaluations') &&
            !$user->can('create-performance-evaluations') &&
            !$user->can('complete-own-performance-evaluations')) {
            abort(403, 'No tienes permisos para acceder a las evaluaciones de desempeño');
        }
        
        $query = PerformanceEvaluation::query();        // Filtrar evaluaciones según el rol del usuario
        if ($user->hasRole('admin') || $user->can('view-all-performance-evaluations')) {
            // Los admins pueden ver todas las evaluaciones
            $query->with(['user', 'evaluator']);
        } else {
            // Los empleados y supervisores pueden ver sus propias evaluaciones y las que deben evaluar
            $query->where(function($q) use ($user) {
                $q->where('evaluator_id', $user->id)
                  ->orWhere('user_id', $user->id);
            })->with(['user', 'evaluator']);
        }
        
        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtro por tipo de evaluación
        if ($request->filled('type')) {
            $query->where('evaluation_type', $request->type);
        }
        
        $evaluations = $query->orderBy('created_at', 'desc')->paginate(10);
        $evaluations->appends($request->query());
        
        return view('performance-evaluations.index', compact('evaluations'));
    }

    /**
     * Exportar evaluaciones a Excel
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        
        // Verificar permisos
        if (!$user->hasRole('admin') && !$user->can('view-all-performance-evaluations') && !$user->can('create-performance-evaluations') && !$user->can('export-performance-evaluations')) {
            abort(403, 'No tienes permisos para exportar evaluaciones');
        }
        
        $filename = 'evaluaciones_desempeño_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new PerformanceEvaluationsExport($request, $user), $filename);
    }

    /**
     * Mostrar formulario para crear nueva evaluación
     */    public function create()
    {
        $user = Auth::user();        // Solo admins y usuarios con permisos pueden crear evaluaciones
        if (!$user->hasRole('admin') && !$user->can('create-performance-evaluations')) {
            abort(403, 'No tienes permisos para crear evaluaciones');
        }
        
        // Definir todos los departamentos disponibles
        $availableDepartments = [
            'Mantenimiento',
            'Servicios Generales', 
            'Sistemas',
            'Almacen',
            'Enfermeria',
            'Docentes',
            'EMC',
            'Biblioteca',
            'Contabilidad',
            'Asistentes'
        ];
        
        // Obtener empleados organizados por departamento (solo departamentos que tienen usuarios)
        $employeesByDepartment = User::select('id', 'name', 'email', 'department')
            ->whereIn('department', $availableDepartments)
            ->orderBy('department')
            ->orderBy('name')
            ->get()
            ->groupBy('department');
        
        // Obtener todos los usuarios para selección individual
        $allUsers = User::select('id', 'name', 'email', 'department')
            ->orderBy('name')
            ->get();
        
        // Usar todos los usuarios como posibles supervisores
        $supervisors = User::orderBy('name')->get();
        
        return view('performance-evaluations.create', compact('employeesByDepartment', 'allUsers', 'supervisors', 'availableDepartments'));
    }

    /**
     * Almacenar nueva evaluación
     */    public function store(Request $request)
    {
        $user = Auth::user();        // Solo admins y usuarios con permisos pueden crear evaluaciones
        if (!$user->hasRole('admin') && !$user->can('create-performance-evaluations')) {
            abort(403, 'No tienes permisos para crear evaluaciones');
        }
        
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array|min:1',
            'evaluator_id' => 'nullable|exists:users,id',
            'evaluation_type' => 'required|in:periodo_prueba,periodica',
            'evaluation_period_start' => 'required|date',
            'evaluation_period_end' => 'required|date|after:evaluation_period_start'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }
        
        // Convertir departamentos seleccionados a IDs de usuarios
        $availableDepartments = [
            'Mantenimiento', 'Servicios Generales', 'Sistemas', 'Almacen', 
            'Enfermeria', 'Docentes', 'EMC', 'Biblioteca', 'Contabilidad', 'Asistentes'
        ];
        
        $userIds = [];
        foreach ($request->user_ids as $selection) {
            // Verificar si es un departamento o un ID de usuario
            if (in_array($selection, $availableDepartments)) {
                // Es un departamento, obtener todos los usuarios de ese departamento
                $departmentUsers = User::where('department', $selection)->pluck('id')->toArray();
                $userIds = array_merge($userIds, $departmentUsers);
            } elseif (is_numeric($selection)) {
                // Es un ID de usuario
                $userIds[] = $selection;
            }
        }
        
        // Eliminar duplicados
        $userIds = array_unique($userIds);
        
        if (empty($userIds)) {
            return redirect()->back()
                           ->withErrors(['user_ids' => 'No se encontraron usuarios válidos para evaluar'])
                           ->withInput();
        }
        
        $createdEvaluations = [];
        $duplicateEmployees = [];
        
        foreach ($userIds as $userId) {
            // Verificar si ya existe una evaluación para este empleado en el mismo período
            $existingEvaluation = PerformanceEvaluation::where('user_id', $userId)
                ->where('evaluation_type', $request->evaluation_type)
                ->where(function($query) use ($request) {
                    $query->whereBetween('evaluation_period_start', [$request->evaluation_period_start, $request->evaluation_period_end])
                          ->orWhereBetween('evaluation_period_end', [$request->evaluation_period_start, $request->evaluation_period_end])
                          ->orWhere(function($q) use ($request) {
                              $q->where('evaluation_period_start', '<=', $request->evaluation_period_start)
                                ->where('evaluation_period_end', '>=', $request->evaluation_period_end);
                          });
                })
                ->first();
            
            if ($existingEvaluation) {
                $employee = \App\Models\User::find($userId);
                $duplicateEmployees[] = $employee->name;
                continue;
            }
            
            $evaluation = PerformanceEvaluation::create([
                'user_id' => $userId,
                'evaluator_id' => $request->evaluator_id,
                'evaluation_type' => $request->evaluation_type,
                'evaluation_period_start' => $request->evaluation_period_start,
                'evaluation_period_end' => $request->evaluation_period_end,
                'status' => 'draft'
            ]);
            
            // Cargar la relación user para el correo
            $evaluation->load('user');
            
            // Enviar correo de notificación al usuario evaluado
            try {
                if ($evaluation->user && $evaluation->user->email) {
                    // Usar el servicio de interceptación de correos para modo de prueba
                    $emailToSend = EmailTestModeService::interceptEmail($evaluation->user->email);
                    Mail::to($emailToSend)->send(new PerformanceEvaluationCreated($evaluation));
                }
            } catch (\Exception $e) {
                // Log del error pero no interrumpir el proceso
                \Log::error('Error enviando correo de evaluación de desempeño: ' . $e->getMessage());
            }
            
            $createdEvaluations[] = $evaluation;
        }
        
        $message = '';
        $messageType = 'success';
        
        if (count($createdEvaluations) > 0) {
            $message = 'Se crearon ' . count($createdEvaluations) . ' evaluación(es) exitosamente. ';
            $message .= 'Se ha enviado un correo de notificación a cada empleado evaluado.';
        }
        
        if (count($duplicateEmployees) > 0) {
            $duplicateMessage = 'No se pudieron crear evaluaciones para: ' . implode(', ', $duplicateEmployees) . ' (ya tienen evaluaciones en este período).';
            if ($message) {
                $message .= ' ' . $duplicateMessage;
                $messageType = 'warning';
            } else {
                $message = $duplicateMessage;
                $messageType = 'error';
            }
        }
        
        if (count($createdEvaluations) === 1) {
            return redirect()->route('performance-evaluations.show', $createdEvaluations[0])
                            ->with($messageType, $message);
        } else {
            return redirect()->route('performance-evaluations.index')
                            ->with($messageType, $message);
        }
    }

    /**
     * Mostrar evaluación específica
     */
    public function show(PerformanceEvaluation $performanceEvaluation)
    {
        $user = Auth::user();
        
        // Verificar permisos
        if (!$this->canViewEvaluation($user, $performanceEvaluation)) {
            abort(403, 'No tienes permisos para ver esta evaluación');
        }
        
        $performanceEvaluation->load(['user', 'evaluator']);
        
        return view('performance-evaluations.show', compact('performanceEvaluation'));
    }

    /**
     * Mostrar formulario de autoevaluación
     */
    public function selfEvaluate(PerformanceEvaluation $performanceEvaluation)
    {
        $user = Auth::user();
        
        // Solo el empleado evaluado puede realizar la autoevaluación
        if ($performanceEvaluation->user_id !== $user->id) {
            abort(403, 'No puedes realizar esta autoevaluación');
        }
        
        if (!$performanceEvaluation->canSelfEvaluate()) {
            return redirect()->route('performance-evaluations.show', $performanceEvaluation)
                           ->with('error', 'Esta autoevaluación ya no puede ser modificada');
        }
        
        $objectivesQuestions = PerformanceEvaluation::getObjectivesQuestions();
        $organizationalCompetencies = PerformanceEvaluation::getOrganizationalCompetencies();
        $technicalCompetencies = PerformanceEvaluation::getTechnicalCompetencies();
        $safetyHealthQuestions = PerformanceEvaluation::getSafetyHealthQuestions();
        
        return view('performance-evaluations.self-evaluate', compact(
            'performanceEvaluation',
            'objectivesQuestions',
            'organizationalCompetencies',
            'technicalCompetencies',
            'safetyHealthQuestions'
        ));
    }

    /**
     * Guardar autoevaluación
     */
    public function storeSelfEvaluation(Request $request, PerformanceEvaluation $performanceEvaluation)
    {
        $user = Auth::user();
        
        if ($performanceEvaluation->user_id !== $user->id) {
            abort(403, 'No puedes realizar esta autoevaluación');
        }
        
        if (!$performanceEvaluation->canSelfEvaluate()) {
            return redirect()->route('performance-evaluations.show', $performanceEvaluation)
                           ->with('error', 'Esta autoevaluación ya no puede ser modificada');
        }
        
        // Validar datos
        $validator = Validator::make($request->all(), [
            'objectives_section' => 'required|array',
            'organizational_competencies' => 'required|array',
            'technical_competencies' => 'nullable|array',
            'safety_health_section' => 'required|array',
            'self_observations' => 'nullable|string|max:2000'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }
        
        // Calcular puntajes
        $objectivesScore = $this->calculateObjectivesScore($request->objectives_section);
        $competenciesScore = $this->calculateCompetenciesScore($request->organizational_competencies);
        
        // Actualizar evaluación
        $performanceEvaluation->update([
            'objectives_section' => $request->objectives_section,
            'objectives_self_score' => $objectivesScore,
            'organizational_competencies' => $request->organizational_competencies,
            'competencies_self_score' => $competenciesScore,
            'technical_competencies' => $request->technical_competencies,
            'safety_health_section' => $request->safety_health_section,
            'self_observations' => $request->self_observations
        ]);
        
        if ($request->has('complete_evaluation')) {
            $performanceEvaluation->completeSelfEvaluation();
            $message = 'Autoevaluación completada exitosamente';
        } else {
            $message = 'Autoevaluación guardada como borrador';
        }
        
        return redirect()->route('performance-evaluations.show', $performanceEvaluation)
                        ->with('success', $message);
    }

    /**
     * Mostrar formulario de evaluación del supervisor
     */
    public function supervisorEvaluate(PerformanceEvaluation $performanceEvaluation)
    {
        $user = Auth::user();
          // Solo el supervisor asignado, admins o usuarios con permiso pueden evaluar
        if ($performanceEvaluation->evaluator_id !== $user->id && 
            !$user->hasRole('admin') && 
            !$user->can('evaluate-as-supervisor')) {
            abort(403, 'No puedes realizar esta evaluación');
        }
        
        if (!$performanceEvaluation->canSupervisorEvaluate()) {
            return redirect()->route('performance-evaluations.show', $performanceEvaluation)
                           ->with('error', 'Esta evaluación ya no puede ser modificada');
        }
        
        $objectivesQuestions = PerformanceEvaluation::getObjectivesQuestions();
        $organizationalCompetencies = PerformanceEvaluation::getOrganizationalCompetencies();
        $technicalCompetencies = PerformanceEvaluation::getTechnicalCompetencies();
        $safetyHealthQuestions = PerformanceEvaluation::getSafetyHealthQuestions();
        
        return view('performance-evaluations.supervisor-evaluate', compact(
            'performanceEvaluation',
            'objectivesQuestions',
            'organizationalCompetencies',
            'technicalCompetencies',
            'safetyHealthQuestions'
        ));
    }

    /**
     * Guardar evaluación del supervisor
     */    public function storeSupervisorEvaluation(Request $request, PerformanceEvaluation $performanceEvaluation)
    {
        $user = Auth::user();
        
        // Verificar permisos: debe ser admin O tener permiso de supervisor Y ser el evaluador asignado
        if (!$user->hasRole('admin') && 
            (!$user->can('evaluate-as-supervisor') || $performanceEvaluation->evaluator_id !== $user->id)) {
            abort(403, 'No tienes permisos para realizar esta evaluación');
        }
        
        if (!$performanceEvaluation->canSupervisorEvaluate()) {
            return redirect()->route('performance-evaluations.show', $performanceEvaluation)
                           ->with('error', 'Esta evaluación ya no puede ser modificada');
        }
        
        // Validar datos
        $validator = Validator::make($request->all(), [
            'objectives_section_supervisor' => 'required|array',
            'organizational_competencies_supervisor' => 'required|array',
            'supervisor_observations' => 'nullable|string|max:2000'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }
        
        // Calcular puntajes del supervisor
        $objectivesScore = $this->calculateObjectivesScore($request->objectives_section_supervisor);
        $competenciesScore = $this->calculateCompetenciesScore($request->organizational_competencies_supervisor);
        
        // Actualizar evaluación
        $performanceEvaluation->update([
            'objectives_supervisor_score' => $objectivesScore,
            'competencies_supervisor_score' => $competenciesScore,
            'supervisor_observations' => $request->supervisor_observations
        ]);
        
        if ($request->has('complete_evaluation')) {
            $performanceEvaluation->completeSupervisorEvaluation();
            $message = 'Evaluación del supervisor completada exitosamente';
            
            return redirect()->route('performance-evaluations.show', $performanceEvaluation)
                            ->with('success', $message)
                            ->with('evaluation_completed', true)
                            ->with('show_feedback_session_option', true);
        } else {
            $message = 'Evaluación del supervisor guardada como borrador';
            
            return redirect()->route('performance-evaluations.show', $performanceEvaluation)
                            ->with('success', $message);
        }
    }

    /**
     * Calcular puntaje de objetivos del cargo
     */
    private function calculateObjectivesScore(array $objectives): float
    {
        $objectivesQuestions = PerformanceEvaluation::getObjectivesQuestions();
        $totalScore = 0;
        
        foreach ($objectivesQuestions as $section => $config) {
            if (isset($objectives[$section])) {
                $sectionScore = 0;
                $questionCount = count($config['questions']);
                
                foreach ($objectives[$section] as $score) {
                    $sectionScore += (int) $score;
                }
                
                $sectionAverage = $questionCount > 0 ? $sectionScore / $questionCount : 0;
                $totalScore += $sectionAverage * $config['weight'];
            }
        }
        
        return round($totalScore, 2);
    }

    /**
     * Calcular puntaje de competencias organizacionales
     */
    private function calculateCompetenciesScore(array $competencies): float
    {
        $totalScore = 0;
        $competencyCount = count($competencies);
        
        foreach ($competencies as $score) {
            $totalScore += (int) $score;
        }
        
        return $competencyCount > 0 ? round($totalScore / $competencyCount, 2) : 0;
    }

    /**
     * Verificar si el usuario puede ver la evaluación
     */
    private function canViewEvaluation(User $user, PerformanceEvaluation $evaluation): bool
    {
        if ($user->hasRole('admin') || $user->hasRole('rrhh')) {
            return true;
        }
        
        if ($evaluation->user_id === $user->id || $evaluation->evaluator_id === $user->id) {
            return true;
        }
        
        return false;
    }
}
