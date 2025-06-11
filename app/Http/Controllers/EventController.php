<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\EventCreated as EventCreatedMail;
use App\Mail\EventConfirmed;
use App\Notifications\EventCreated as EventCreatedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class EventController extends Controller
{
    protected function sendEventNotifications($event, $mailableClass)
    {
        // Obtener correos configurados para el área específica
        $config = Configuration::where('key', "events_{$event->department}_emails")->first();
        $notificationEmails = $config ? explode(',', $config->value) : [];
        
        // Enviar notificaciones a todos los correos configurados
        foreach ($notificationEmails as $email) {
            Mail::to(trim($email))->send(new $mailableClass($event));
        }

        // Enviar notificación al creador del evento
        if ($event->user && $event->user->email) {
            Mail::to($event->user->email)->send(new $mailableClass($event));
        }
    }

    public function index()
    {
        // Verificar permisos de visualización para profesores
        if (auth()->user()->hasRole('profesor') && !auth()->user()->can('view.events')) {
            return redirect()->route('home')
                ->with('error', 'No tienes permisos para acceder a esta sección.');
        }

        $events = Event::orderBy('service_date', 'desc')->get();
        return view('events.index', compact('events'));
    }

    public function create()
    {
        // Verificar permisos - profesores no pueden crear eventos
        if (auth()->user()->hasRole('profesor')) {
            return redirect()->route('events.index')
                ->with('error', 'No tienes permisos para crear eventos.');
        }

        \Log::info('Vista de creación de evento cargada', [
            'session_id' => session()->getId(),
            'csrf_token' => csrf_token(),
            'user_id' => auth()->id()
        ]);
        return view('events.create');
    }

    public function store(Request $request)
    {
        // Verificar permisos - profesores no pueden crear eventos
        if (auth()->user()->hasRole('profesor')) {
            return redirect()->route('events.index')
                ->with('error', 'No tienes permisos para crear eventos.');
        }

        \Log::info('Iniciando proceso de creación de evento', [
            'session_id' => session()->getId(),
            'csrf_token' => $request->header('X-CSRF-TOKEN'),
            'csrf_field' => $request->input('_token'),
            'user_id' => auth()->id(),
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type')
        ]);

        try {
            \Log::info('Iniciando creación de evento', [
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);

            // Validar campos requeridos
            $validated = $request->validate([
                // Campos básicos
                'event_name' => 'required|string|max:255',
                'section' => 'required|string|max:255',
                'responsible' => 'required|string|max:255',
                'date_type' => 'required|in:single,multiple',
                'service_date' => 'required_if:date_type,single|nullable|date',
                'service_dates' => 'required_if:date_type,multiple|nullable|array',
                'service_dates.*' => 'required|date',
                'location_type' => 'required|in:single,multiple',
                'location' => 'required_if:location_type,single|nullable|string|max:255',
                'locations' => 'required_if:location_type,multiple|nullable|array',
                'locations.*' => 'string|max:255',
                'event_time' => 'required',
                'end_time' => 'required',
                'cafam_parking' => 'required|boolean',
                'request_date' => 'required|date',

                // Metro Junior
                'metro_junior_required' => 'boolean',
                'route' => 'required_if:metro_junior_required,1|nullable|string|max:255',
                'passengers' => 'required_if:metro_junior_required,1|nullable|integer',
                'departure_time' => 'required_if:metro_junior_required,1|nullable',
                'return_time' => 'required_if:metro_junior_required,1|nullable',
                'metro_junior_observations' => 'nullable|string',

                // Servicios Generales
                'general_services_required' => 'boolean',
                'general_services_requirement' => 'required_if:general_services_required,1|nullable|string',
                'general_services_setup_date' => 'required_if:general_services_required,1|nullable|date',
                'general_services_setup_time' => 'required_if:general_services_required,1|nullable',

                // Mantenimiento
                'maintenance_required' => 'boolean',
                'maintenance_requirement' => 'required_if:maintenance_required,1|nullable|string',
                'maintenance_setup_date' => 'required_if:maintenance_required,1|nullable|date',
                'maintenance_setup_time' => 'required_if:maintenance_required,1|nullable',

                // Sistemas
                'systems_required' => 'boolean',
                'systems_requirement' => 'required_if:systems_required,1|nullable|string',
                'systems_setup_date' => 'required_if:systems_required,1|nullable|date',
                'systems_setup_time' => 'required_if:systems_required,1|nullable',
                'systems_observations' => 'nullable|string',

                // Aldimark
                'aldimark_required' => 'boolean',
                'aldimark_requirement' => 'required_if:aldimark_required,1|nullable|string',
                'aldimark_time' => 'required_if:aldimark_required,1|nullable',
                'aldimark_details' => 'nullable|string',

                // Compras
                'purchases_required' => 'boolean',
                'purchases_requirement' => 'required_if:purchases_required,1|nullable|string',
                'purchases_observations' => 'nullable|string',

                // Comunicaciones
                'communications_required' => 'boolean',
                'communications_coverage' => 'required_if:communications_required,1|nullable|string',
                'communications_observations' => 'nullable|string',
            ]);
            
            \Log::info('Validación de datos exitosa', ['validated_data' => $validated]);
            
            DB::beginTransaction();

            try {
                $lastEvent = Event::latest()->first();
                $consecutive = $lastEvent ? 'EV-' . str_pad((intval(substr($lastEvent->consecutive, 3)) + 1), 4, '0', STR_PAD_LEFT) : 'EV-0001';
                
                $eventData = $request->all();
                $eventData['consecutive'] = $consecutive;
                $eventData['request_date'] = now();
                $eventData['user_id'] = auth()->id();
                
                // Eliminar campos que no existen en la tabla
                unset($eventData['date_type']);
                unset($eventData['location_type']);
                
                // Procesar fecha única vs múltiple
                if ($request->input('date_type') === 'multiple') {
                    $eventData['service_dates'] = $request->input('service_dates');
                    // Establecer una fecha principal (la primera fecha seleccionada)
                    if (!empty($eventData['service_dates'])) {
                        $eventData['service_date'] = $eventData['service_dates'][0];
                    }
                } else {
                    // Si es fecha única, establecer service_dates como un array con esa fecha única
                    $eventData['service_dates'] = [$request->input('service_date')];
                }
                
                // Procesar lugar único vs múltiple
                if ($request->input('location_type') === 'multiple') {
                    $eventData['locations'] = $request->input('locations');
                    // Establecer un lugar principal (el primer lugar seleccionado)
                    if (!empty($eventData['locations'])) {
                        $eventData['location'] = $eventData['locations'][0];
                    }
                } else {
                    $eventData['locations'] = null;
                }

                \Log::info('Intentando crear evento', [
                    'event_data' => $eventData,
                    'consecutive' => $consecutive
                ]);

                $event = Event::create($eventData);
                
                \Log::info('Evento creado exitosamente', [
                    'event_id' => $event->id,
                    'consecutive' => $event->consecutive
                ]);

                // Enviar notificaciones
                try {
                    $notificationEmails = config('notifications.events.emails', []);
                    
                    if (!empty($notificationEmails)) {
                        \Log::info('Enviando notificaciones generales', ['emails' => $notificationEmails]);
                        
                        foreach ($notificationEmails as $email) {
                            try {
                                Notification::route('mail', trim($email))
                                    ->notify(new EventCreatedNotification($event));
                                
                                \Log::info('Notificación enviada exitosamente', ['email' => $email]);
                            } catch (\Exception $e) {
                                \Log::error('Error al enviar notificación', [
                                    'email' => $email,
                                    'error' => $e->getMessage(),
                                    'trace' => $e->getTraceAsString()
                                ]);
                            }
                        }
                    }

                    // Enviar notificaciones a departamentos específicos
                    $services = [
                        'systems',
                        'purchases',
                        'maintenance',
                        'general_services',
                        'communications',
                        'aldimark',
                        'metro_junior'
                    ];
                    
                    foreach ($services as $service) {
                        $requiredField = $service . '_required';
                        
                        if ($event->$requiredField) {
                            $serviceEmails = config("notifications.events.{$service}_emails", []);
                            \Log::info("Enviando notificaciones al departamento {$service}", ['emails' => $serviceEmails]);
                            
                            foreach ($serviceEmails as $email) {
                                try {
                                    Mail::to(trim($email))
                                        ->send(new EventCreatedMail($event));
                                    
                                    \Log::info("Notificación enviada al departamento {$service}", ['email' => $email]);
                                } catch (\Exception $e) {
                                    \Log::error("Error al enviar notificación al departamento {$service}", [
                                        'email' => $email,
                                        'error' => $e->getMessage(),
                                        'trace' => $e->getTraceAsString()
                                    ]);
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Error al enviar notificaciones', [
                        'event_id' => $event->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }

                DB::commit();
                \Log::info('Transacción completada exitosamente');

                return redirect()->route('events.index')
                    ->with('success', 'Evento creado correctamente.');

            } catch (\Exception $e) {
                DB::rollback();
                \Log::error('Error al crear el evento', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'data' => $eventData ?? null
                ]);
                
                return back()
                    ->withInput()
                    ->with('error', 'Error al crear el evento: ' . $e->getMessage());
            }
        } catch (ValidationException $e) {
            \Log::error('Error de validación', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Error general', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Error inesperado al procesar la solicitud');
        }
    }

    public function show(Event $event)
    {
        return view('events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        // Verificar si el usuario tiene permiso para editar eventos
        if (!auth()->user()->hasAnyRole(['admin', 'Admin', 'edicion-eliminacion-evento'])) {
            return redirect()->route('events.show', $event)
                ->with('error', 'No tienes permisos para editar eventos.');
        }

        // Restricción adicional para profesores
        if (auth()->user()->hasRole('profesor')) {
            return redirect()->route('events.show', $event)
                ->with('error', 'No tienes permisos para editar eventos.');
        }

        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        // Verificar si el usuario tiene permiso para editar eventos
        if (!auth()->user()->hasAnyRole(['admin', 'Admin', 'edicion-eliminacion-evento'])) {
            return redirect()->route('events.show', $event)
                ->with('error', 'No tienes permisos para editar eventos.');
        }

        // Restricción adicional para profesores
        if (auth()->user()->hasRole('profesor')) {
            return redirect()->route('events.show', $event)
                ->with('error', 'No tienes permisos para editar eventos.');
        }

        $event->update($request->all());
        return redirect()->route('events.index')
            ->with('swal', [
                'icon' => 'success',
                'title' => '¡Actualizado!',
                'text' => 'El evento ha sido actualizado exitosamente.',
            ]);
    }

    public function destroy(Event $event)
    {
        // Verificar si el usuario tiene permiso para eliminar eventos
        if (!auth()->user()->hasAnyRole(['admin', 'Admin', 'edicion-eliminacion-evento'])) {
            return redirect()->route('events.index')
                ->with('error', 'No tienes permisos para eliminar eventos.');
        }

        // Restricción adicional para profesores
        if (auth()->user()->hasRole('profesor')) {
            return redirect()->route('events.index')
                ->with('error', 'No tienes permisos para eliminar eventos.');
        }

        $event->delete();
        return redirect()->route('events.index')
            ->with('swal', [
                'icon' => 'success',
                'title' => '¡Eliminado!',
                'text' => 'El evento ha sido eliminado exitosamente.',
            ]);
    }

    public function calendar()
    {
        // Verificar permisos para calendario
        if (auth()->user()->hasRole('profesor') && !auth()->user()->can('view.calendar')) {
            return redirect()->route('home')
                ->with('error', 'No tienes permisos para acceder al calendario.');
        }

        $events = Event::orderBy('service_date')->get();
        return view('events.calendar', compact('events'));
    }

    public function confirm(Event $event, Request $request)
    {
        // Verificar permisos de confirmación para profesores
        if (auth()->user()->hasRole('profesor') && !auth()->user()->can('confirm.events')) {
            if ($request->ajax()) {
                return response()->json(['error' => 'No tienes permisos para confirmar eventos'], 403);
            }
            return redirect()->route('events.show', $event)
                ->with('error', 'No tienes permisos para confirmar eventos.');
        }

        try {
            // Verify token
            $decrypted = decrypt($request->token);
            if ($decrypted != $event->id) {
                return response()->json(['error' => 'Token inválido'], 403);
            }

            // Define los servicios disponibles
            $services = [
                'systems',
                'purchases',
                'maintenance',
                'general_services',
                'communications',
                'aldimark',
                'metro_junior'
            ];
            
            $serviceConfirmed = false;

            // Check which service is required and not yet confirmed
            foreach ($services as $service) {
                $requiredField = $service . '_required';
                $confirmedField = $service . '_confirmed';
                
                if ($event->$requiredField && !$event->$confirmedField) {
                    $event->update([
                        $confirmedField => true
                    ]);
                    $serviceConfirmed = true;
                    break;
                }
            }

            if (!$serviceConfirmed) {
                if ($request->ajax()) {
                    return response()->json(['error' => 'No hay servicios pendientes de confirmar'], 400);
                }
                return redirect()->route('events.show', $event)
                    ->with('warning', 'Este servicio ya fue confirmado anteriormente.');
            }

            // Enviar notificaciones de confirmación
            $this->sendEventNotifications($event, EventConfirmed::class);

            if ($request->ajax()) {
                return response()->json(['message' => 'Evento confirmado exitosamente']);
            }

            return redirect()->route('events.show', $event)
                ->with('success', 'Has confirmado tu participación en el evento.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Error al confirmar el evento: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Error al confirmar el evento');
        }
    }

    public function confirmService(Request $request, Event $event)
    {
        $service = $request->input('service');
        $confirmedField = $service . '_confirmed';
        
        // Verificar que el usuario tiene permiso para confirmar este servicio
        $permissionRole = 'confirmacion-' . str_replace('_', '-', $service);
        $canConfirm = auth()->user()->hasAnyRole(['admin', 'Admin', $permissionRole]) || 
                     (auth()->user()->hasRole('profesor') && auth()->user()->can('confirm.events'));
        
        if (!$canConfirm) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para confirmar este servicio.'
            ], 403);
        }

        // Verificar que el servicio existe y está requerido
        $requiredField = $service . '_required';
        if (!$event->$requiredField) {
            return response()->json([
                'success' => false,
                'message' => 'Este servicio no está requerido para este evento.'
            ], 400);
        }

        try {
            $event->$confirmedField = true;
            $event->save();

            // Enviar notificación si es necesario
            // event(new ServiceConfirmed($event, $service));

            return response()->json([
                'success' => true,
                'message' => 'Servicio confirmado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al confirmar el servicio: ' . $e->getMessage()
            ], 500);
        }
    }

    public function dashboard(Request $request)
    {
        $period = $request->get('period', 'all');
        $service = $request->get('service', 'all');

        // Query base para eventos
        $query = Event::query();

        // Filtrar por período
        switch ($period) {
            case 'today':
                $query->whereDate('service_date', today());
                break;
            case 'month':
                $query->whereMonth('service_date', now()->month);
                break;
        }

        // Filtrar por servicio
        if ($service !== 'all') {
            $query->where($service . '_required', true);
        }

        // Eventos filtrados
        $events = $query->orderBy('service_date', 'desc')->get();

        // Estadísticas generales
        $totalEvents = Event::count();
        $pendingEvents = Event::where(function($query) {
            $services = ['metro_junior', 'aldimark', 'maintenance', 'general_services', 'systems', 'purchases', 'communications'];
            foreach ($services as $service) {
                $query->orWhere(function($q) use ($service) {
                    $q->where($service . '_required', true)
                      ->where($service . '_confirmed', false);
                });
            }
        })->count();

        $confirmedEvents = Event::where(function($query) {
            $services = ['metro_junior', 'aldimark', 'maintenance', 'general_services', 'systems', 'purchases', 'communications'];
            $query->where(function($q) use ($services) {
                foreach ($services as $service) {
                    $q->where(function($subq) use ($service) {
                        $subq->where($service . '_required', false)
                             ->orWhere($service . '_confirmed', true);
                    });
                }
            });
        })->count();

        // Eventos por ubicación
        $eventsByLocation = Event::select('location', \DB::raw('count(*) as total'))
            ->groupBy('location')
            ->get();

        // Eventos por servicio
        $services = [
            'metro_junior' => 'Metro Junior',
            'aldimark' => 'Aldimark',
            'maintenance' => 'Mantenimiento',
            'general_services' => 'Servicios Generales',
            'systems' => 'Sistemas',
            'purchases' => 'Compras',
            'communications' => 'Comunicaciones'
        ];

        // Eventos por servicio (Total)
        $eventsByService = [];
        foreach ($services as $key => $name) {
            $eventsByService[$name] = Event::where($key . '_required', true)->count();
        }

        // Eventos por servicio en el mes actual
        $eventsThisMonth = [];
        foreach ($services as $key => $name) {
            $count = Event::where($key . '_required', true)
                ->whereMonth('service_date', now()->month)
                ->count();
            $eventsThisMonth[$name] = $count;
        }

        // Obtener el servicio más solicitado del mes
        $maxCount = max($eventsThisMonth);
        $mostRequestedService = $maxCount > 0 ? array_search($maxCount, $eventsThisMonth) : 'N/A';

        return view('events.dashboard', compact(
            'totalEvents',
            'pendingEvents',
            'confirmedEvents',
            'eventsByLocation',
            'eventsByService',
            'eventsThisMonth',
            'mostRequestedService',
            'events',
            'services',
            'period',
            'service'
        ));
    }

    /**
     * Exportar todos los eventos con sus novedades a Excel
     */
    public function export()
    {
        // Verificar permisos - profesores no pueden exportar
        if (auth()->user()->hasRole('profesor')) {
            return redirect()->route('events.index')
                ->with('error', 'No tienes permisos para exportar eventos.');
        }

        return \Excel::download(new \App\Exports\EventsWithNoveltiesExport, 'eventos-con-novedades.xlsx');
    }
}
