<?php

namespace App\Http\Controllers;

use App\Models\FeedbackSession;
use App\Models\PerformanceEvaluation;
use App\Services\GoogleCalendarService;
use App\Mail\FeedbackSessionScheduled;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FeedbackSessionController extends Controller
{
    protected $googleCalendarService;

    public function __construct(GoogleCalendarService $googleCalendarService)
    {
        $this->googleCalendarService = $googleCalendarService;
    }

    /**
     * Mostrar formulario para programar sesión de retroalimentación
     */
    public function create(PerformanceEvaluation $evaluation)
    {
        // Verificar que el usuario sea el supervisor de la evaluación o admin
        if (Auth::id() !== $evaluation->evaluator_id && !Auth::user()->hasRole('admin')) {
            abort(403, 'No tienes permisos para programar sesiones para esta evaluación.');
        }

        // Verificar que la evaluación del supervisor esté completada
        if ($evaluation->status !== 'completed' || !$evaluation->supervisor_evaluation_completed_at) {
            abort(400, 'La evaluación del supervisor debe estar completada para programar sesiones de retroalimentación.');
        }

        // Verificar si ya existe una sesión programada
        $existingSession = $evaluation->feedbackSessions()->programadas()->first();
        
        if ($existingSession) {
            return redirect()->route('performance-evaluations.show', $evaluation)
                ->with('warning', 'Ya existe una sesión de retroalimentación programada para esta evaluación.');
        }

        return view('feedback-sessions.create', [
            'evaluation' => $evaluation,
            'employee' => $evaluation->user,
            'supervisor' => $evaluation->evaluator
        ]);
    }

    /**
     * Almacenar nueva sesión de retroalimentación
     */
    public function store(Request $request, PerformanceEvaluation $evaluation)
    {
        // Verificar permisos
        if (Auth::id() !== $evaluation->evaluator_id && !Auth::user()->hasRole('admin')) {
            abort(403, 'No tienes permisos para programar sesiones para esta evaluación.');
        }

        $request->validate([
            'scheduled_date' => 'required|date|after:today',
            'scheduled_time' => 'required|date_format:H:i',
            'duration' => 'required|integer|min:30|max:240', // 30 min a 4 horas
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000'
        ]);

        try {
            // Combinar fecha y hora
            $scheduledDateTime = Carbon::createFromFormat(
                'Y-m-d H:i',
                $request->scheduled_date . ' ' . $request->scheduled_time
            );

            $endDateTime = $scheduledDateTime->copy()->addMinutes((int) $request->duration);

            // Crear sesión de retroalimentación
            $feedbackSession = FeedbackSession::create([
                'performance_evaluation_id' => $evaluation->id,
                'supervisor_id' => $evaluation->evaluator_id,
                'employee_id' => $evaluation->user_id,
                'title' => "Sesión de Retroalimentación - {$evaluation->user->name}",
                'description' => $request->description ?: "Sesión de retroalimentación para la evaluación de desempeño del período " . 
                    ($evaluation->evaluation_period_start ? $evaluation->evaluation_period_start->format('d/m/Y') : 'N/A') . " - " . 
                    ($evaluation->evaluation_period_end ? $evaluation->evaluation_period_end->format('d/m/Y') : 'N/A'),
                'scheduled_datetime' => $scheduledDateTime,
                'location' => $request->location,
                'status' => 'programada'
            ]);

            // Intentar crear evento en Google Calendar
            if ($this->googleCalendarService->isConfigured()) {
                $attendees = [
                    [
                        'email' => $evaluation->user->email,
                        'name' => $evaluation->user->name
                    ],
                    [
                        'email' => $evaluation->evaluator->email,
                        'name' => $evaluation->evaluator->name
                    ]
                ];

                $calendarData = [
                    'title' => $feedbackSession->title,
                    'description' => $feedbackSession->description,
                    'start_datetime' => $scheduledDateTime,
                    'end_datetime' => $endDateTime,
                    'location' => $request->location ?: '',
                    'attendees' => $attendees
                ];

                $calendarResult = $this->googleCalendarService->createFeedbackSession($calendarData);
                
                if ($calendarResult['success']) {
                    $feedbackSession->update([
                        'google_event_id' => $calendarResult['event_id']
                    ]);
                    
                    Log::info('Sesión de retroalimentación creada con Google Calendar', [
                        'feedback_session_id' => $feedbackSession->id,
                        'google_event_id' => $calendarResult['event_id']
                    ]);
                }
            }

            // Enviar notificaciones por email
            $this->sendScheduledNotifications($feedbackSession);

            return redirect()->route('performance-evaluations.show', $evaluation)
                ->with('success', 'Sesión de retroalimentación programada exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al programar sesión de retroalimentación', [
                'evaluation_id' => $evaluation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withInput()
                ->with('error', 'Ocurrió un error al programar la sesión de retroalimentación. Por favor intenta nuevamente.');
        }
    }

    /**
     * Mostrar detalles de sesión de retroalimentación
     */
    public function show(FeedbackSession $feedbackSession)
    {
        return view('feedback-sessions.show', compact('feedbackSession'));
    }

    /**
     * Mostrar formulario para editar sesión
     */
    public function edit(FeedbackSession $feedbackSession)
    {
        // Verificar permisos
        if (Auth::id() !== $feedbackSession->supervisor_id && !Auth::user()->hasRole('admin')) {
            abort(403, 'No tienes permisos para editar esta sesión.');
        }

        // No permitir editar sesiones ya realizadas o canceladas
        if (in_array($feedbackSession->status, ['realizada', 'cancelada'])) {
            return redirect()->route('feedback-sessions.show', $feedbackSession)
                ->with('warning', 'No se puede editar una sesión que ya fue realizada o cancelada.');
        }

        return view('feedback-sessions.edit', compact('feedbackSession'));
    }

    /**
     * Actualizar sesión de retroalimentación
     */
    public function update(Request $request, FeedbackSession $feedbackSession)
    {
        // Verificar permisos
        if (Auth::id() !== $feedbackSession->supervisor_id && !Auth::user()->hasRole('admin')) {
            abort(403, 'No tienes permisos para editar esta sesión.');
        }

        $request->validate([
            'scheduled_date' => 'required|date|after:today',
            'scheduled_time' => 'required|date_format:H:i',
            'duration' => 'required|integer|min:30|max:240',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000'
        ]);

        try {
            $scheduledDateTime = Carbon::createFromFormat(
                'Y-m-d H:i',
                $request->scheduled_date . ' ' . $request->scheduled_time
            );

            $endDateTime = $scheduledDateTime->copy()->addMinutes((int) $request->duration);

            $feedbackSession->update([
                'scheduled_datetime' => $scheduledDateTime,
                'location' => $request->location,
                'description' => $request->description
            ]);

            // Actualizar evento en Google Calendar si existe
            if ($feedbackSession->google_event_id && $this->googleCalendarService->isConfigured()) {
                $calendarData = [
                    'start_datetime' => $scheduledDateTime,
                    'end_datetime' => $endDateTime,
                    'location' => $request->location ?: '',
                    'description' => $request->description
                ];

                $this->googleCalendarService->updateEvent($feedbackSession->google_event_id, $calendarData);
            }

            // Enviar notificación de actualización
            $this->sendUpdatedNotifications($feedbackSession);

            return redirect()->route('feedback-sessions.show', $feedbackSession)
                ->with('success', 'Sesión de retroalimentación actualizada exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al actualizar sesión de retroalimentación', [
                'feedback_session_id' => $feedbackSession->id,
                'error' => $e->getMessage()
            ]);

            return back()->withInput()
                ->with('error', 'Ocurrió un error al actualizar la sesión.');
        }
    }

    /**
     * Marcar sesión como completada
     */
    public function complete(Request $request, FeedbackSession $feedbackSession)
    {
        // Verificar permisos
        if (Auth::id() !== $feedbackSession->supervisor_id && !Auth::user()->hasRole('admin')) {
            abort(403, 'No tienes permisos para completar esta sesión.');
        }

        $request->validate([
            'meeting_notes' => 'nullable|string|max:2000'
        ]);

        $feedbackSession->markAsCompleted($request->meeting_notes);

        return redirect()->route('feedback-sessions.show', $feedbackSession)
            ->with('success', 'Sesión de retroalimentación marcada como completada.');
    }

    /**
     * Cancelar sesión de retroalimentación
     */
    public function cancel(FeedbackSession $feedbackSession)
    {
        // Verificar permisos
        if (Auth::id() !== $feedbackSession->supervisor_id && !Auth::user()->hasRole('admin')) {
            abort(403, 'No tienes permisos para cancelar esta sesión.');
        }

        $feedbackSession->cancel();

        // Eliminar evento de Google Calendar si existe
        if ($feedbackSession->google_event_id && $this->googleCalendarService->isConfigured()) {
            $this->googleCalendarService->deleteEvent($feedbackSession->google_event_id);
        }

        return redirect()->route('performance-evaluations.show', $feedbackSession->performance_evaluation)
            ->with('info', 'Sesión de retroalimentación cancelada.');
    }

    /**
     * Enviar notificaciones de sesión programada
     */
    private function sendScheduledNotifications(FeedbackSession $feedbackSession)
    {
        try {
            // Enviar al empleado
            Mail::to($feedbackSession->employee->email)
                ->send(new FeedbackSessionScheduled($feedbackSession, 'employee'));

            // Enviar al supervisor
            Mail::to($feedbackSession->supervisor->email)
                ->send(new FeedbackSessionScheduled($feedbackSession, 'supervisor'));

            Log::info('Notificaciones de sesión de retroalimentación enviadas', [
                'feedback_session_id' => $feedbackSession->id,
                'employee_email' => $feedbackSession->employee->email,
                'supervisor_email' => $feedbackSession->supervisor->email
            ]);

        } catch (\Exception $e) {
            Log::error('Error enviando notificaciones de sesión de retroalimentación', [
                'feedback_session_id' => $feedbackSession->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Enviar notificaciones de sesión actualizada
     */
    private function sendUpdatedNotifications(FeedbackSession $feedbackSession)
    {
        // Implementar lógica similar para notificaciones de actualización
        // Se puede crear un Mailable específico para actualizaciones
    }
}
