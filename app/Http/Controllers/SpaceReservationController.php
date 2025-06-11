<?php

namespace App\Http\Controllers;

use App\Mail\SpaceReservationNotification;
use App\Models\CycleDay;
use App\Models\Holiday;
use App\Models\SchoolCycle;
use App\Models\Space;
use App\Models\SpaceBlock;
use App\Models\SpaceReservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SpaceReservationController extends Controller
{
    /**
     * Verificar si el usuario tiene permisos administrativos para gestionar reservas de espacios
     */
    private function isSpaceAdmin()
    {
        return Auth::user()->hasRole(['admin', 'admin-espacios']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SpaceReservation::with(['space', 'user']);
        
        // Filtros
        if ($request->filled('space_id')) {
            $query->where('space_id', $request->space_id);
        }
        
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Si no es administrador, solo mostrar sus propias reservas
        if (!$this->isSpaceAdmin()) {
            $query->where('user_id', Auth::id());
        }
        
        $reservations = $query->orderBy('date', 'desc')
            ->orderBy('start_time')
            ->paginate(15);
            
        $spaces = Space::where('active', true)->get();
        
        return view('space_reservations.index', compact('reservations', 'spaces'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $spaces = Space::where('active', true)->get();
        $activeCycle = SchoolCycle::where('active', true)->first();
        
        if (!$activeCycle) {
            return redirect()->back()->with('error', 'No hay un ciclo escolar activo. No se pueden realizar reservas.');
        }
        
        return view('space_reservations.create', compact('spaces', 'activeCycle'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'space_id' => 'required|exists:spaces,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'purpose' => 'required|string|max:255',
        ]);
        
        // Verificar si se puede realizar la reserva en esa fecha
        $canReserve = SpaceReservation::canReserveOnDate($validated['space_id'], $validated['date']);
        if (!$canReserve[0]) {
            return redirect()->back()->withInput()->with('error', 'No se puede reservar en esta fecha: ' . $canReserve[1]);
        }
        
        // Verificar conflictos de horario
        if (SpaceReservation::hasTimeConflict(
            $validated['space_id'],
            $validated['date'],
            $validated['start_time'],
            $validated['end_time']
        )) {
            return redirect()->back()->withInput()->with('error', 'Ya existe una reserva para este espacio en el horario seleccionado.');
        }
        
        // Añadir el usuario que crea la reserva y establecer el estado como pendiente
        $validated['user_id'] = Auth::id();
        $validated['status'] = 'pending';
        
        // Procesar el campo requires_librarian (asegurarse de que sea booleano)
        $validated['requires_librarian'] = $request->has('requires_librarian') ? true : false;
        
        // Crear la reserva
        $reservation = SpaceReservation::create($validated);
        
        // Procesar implementos seleccionados si existen
        if ($request->has('items')) {
            $space = Space::findOrFail($validated['space_id']);
            foreach ($request->items as $itemId => $itemData) {
                if (isset($itemData['selected']) && $itemData['selected']) {
                    $spaceItem = $space->items()->find($itemId);
                    if ($spaceItem && $spaceItem->available) {
                        $quantity = isset($itemData['quantity']) ? min($itemData['quantity'], $spaceItem->quantity) : 1;
                        
                        // Registrar el implemento en la reserva
                        $reservation->items()->create([
                            'space_item_id' => $itemId,
                            'quantity' => $quantity,
                            'status' => 'pending' // El estado del implemento sigue el de la reserva
                        ]);
                    }
                }
            }
        }
        
        // Cargar las relaciones necesarias para el correo
        $reservation->load(['user', 'space', 'items.item']);
        
        // Enviar notificación por correo electrónico
        try {
            $recipients = ['asistentegeneral@tvs.edu.co', 'library@tvs.edu.co'];
            Mail::to($recipients)->send(new SpaceReservationNotification($reservation));
        } catch (\Exception $e) {
            // Log del error pero no interrumpir el flujo de la reserva
            \Log::error('Error enviando correo de notificación de reserva: ' . $e->getMessage());
        }
        
        return redirect()->route('space-reservations.index')
            ->with('success', 'Reserva creada exitosamente. Estado: Pendiente de aprobación.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SpaceReservation $spaceReservation)
    {
        // Verificar que el usuario tenga permisos para ver esta reserva
        if (!$this->isSpaceAdmin() && Auth::id() != $spaceReservation->user_id) {
            return redirect()->route('space-reservations.index')
                ->with('error', 'No tiene permisos para ver esta reserva.');
        }
        
        $reservation = $spaceReservation; // Asignar a la variable que espera la vista
        return view('space_reservations.show', compact('reservation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SpaceReservation $spaceReservation)
    {
        // Verificar que el usuario tenga permisos para editar esta reserva
        if (!$this->isSpaceAdmin() && Auth::id() != $spaceReservation->user_id) {
            return redirect()->route('space-reservations.index')
                ->with('error', 'No tiene permisos para editar esta reserva.');
        }
        
        // No permitir editar reservas canceladas o rechazadas
        if (in_array($spaceReservation->status, ['cancelled', 'rejected'])) {
            return redirect()->route('space-reservations.index')
                ->with('error', 'No se puede editar una reserva cancelada o rechazada.');
        }
        
        $spaces = Space::where('active', true)->get();
        $reservation = $spaceReservation; // Asignar a la variable que espera la vista
        
        return view('space_reservations.edit', compact('reservation', 'spaces'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SpaceReservation $spaceReservation)
    {
        // Log para depuración
        \Log::info('Iniciando actualización de reserva', [
            'reservation_id' => $spaceReservation->id,
            'request_data' => $request->all(),
            'is_ajax' => $request->ajax()
        ]);
        
        // Verificar que el usuario tenga permisos para actualizar esta reserva
        if (!$this->isSpaceAdmin() && Auth::id() != $spaceReservation->user_id) {
            \Log::warning('Permiso denegado para actualizar reserva', [
                'user_id' => Auth::id(),
                'reservation_id' => $spaceReservation->id
            ]);
            
            if ($request->ajax()) {
                return response()->json(['error' => 'No tiene permisos para actualizar esta reserva.'], 403);
            }
            
            return redirect()->route('space-reservations.index')
                ->with('error', 'No tiene permisos para actualizar esta reserva.');
        }
        
        // No permitir editar reservas canceladas o rechazadas
        if (in_array($spaceReservation->status, ['cancelled', 'rejected'])) {
            \Log::warning('Intento de actualizar reserva cancelada o rechazada', [
                'reservation_id' => $spaceReservation->id,
                'status' => $spaceReservation->status
            ]);
            
            if ($request->ajax()) {
                return response()->json(['error' => 'No se puede actualizar una reserva cancelada o rechazada.'], 400);
            }
            
            return redirect()->route('space-reservations.index')
                ->with('error', 'No se puede actualizar una reserva cancelada o rechazada.');
        }
        
        // Si estamos aprobando/rechazando desde la página de pendientes
        $fromPendingPage = $request->has('status') && in_array($request->status, ['approved', 'rejected']);
        
        try {
            // Ajustar las reglas de validación según la acción
            if ($fromPendingPage) {
                $validated = $request->validate([
                    'space_id' => 'required|exists:spaces,id',
                    'date' => 'required|date',  // Sin restricción de fecha futura para aprobaciones
                    'start_time' => 'required|date_format:H:i',
                    'end_time' => 'required|date_format:H:i|after:start_time',
                    'purpose' => 'required|string|max:255',
                    'notes' => 'nullable|string',
                ]);
                
                \Log::info('Validación exitosa para aprobación/rechazo', [
                    'validated' => $validated
                ]);
            } else {
                $validated = $request->validate([
                    'space_id' => 'required|exists:spaces,id',
                    'date' => 'required|date|after_or_equal:today',
                    'start_time' => 'required|date_format:H:i',
                    'end_time' => 'required|date_format:H:i|after:start_time',
                    'purpose' => 'required|string|max:255',
                    'notes' => 'nullable|string',
                ]);
                
                \Log::info('Validación exitosa para edición normal', [
                    'validated' => $validated
                ]);
            }
            
            // Procesar el campo requires_librarian (asegurarse de que sea booleano)
            $validated['requires_librarian'] = $request->has('requires_librarian') ? true : false;
            
            // Si es administrador, también puede cambiar el estado
            if ($this->isSpaceAdmin() && $request->has('status')) {
                $validated['status'] = $request->status;
                
                // Registrar información adicional para aprobaciones o rechazos
                if ($request->status == 'approved') {
                    $validated['approved_by'] = Auth::id();
                    $validated['approved_at'] = now();
                    
                    \Log::info('Reserva marcada para aprobación', [
                        'approved_by' => Auth::id(),
                        'approved_at' => now()
                    ]);
                    
                    // Si se aprueba la reserva, también aprobar los implementos
                    if ($spaceReservation->items()->count() > 0) {
                        $spaceReservation->items()->update(['status' => 'approved']);
                    }
                }
                
                // Si se está rechazando, requerir comentarios
                if ($request->status == 'rejected' && empty($request->comments)) {
                    \Log::warning('Rechazo sin comentarios');
                    
                    if ($request->ajax()) {
                        return response()->json(['error' => 'Debe proporcionar un comentario al rechazar una reserva.'], 422);
                    }
                    
                    return redirect()->back()->withInput()
                        ->with('error', 'Debe proporcionar un comentario al rechazar una reserva.');
                }
                
                if ($request->filled('comments')) {
                    $validated['comments'] = $request->comments;
                    
                    if ($request->status == 'rejected') {
                        $validated['rejected_by'] = Auth::id();
                        $validated['rejected_at'] = now();
                        
                        \Log::info('Reserva marcada para rechazo', [
                            'rejected_by' => Auth::id(),
                            'rejected_at' => now(),
                            'comments' => $request->comments
                        ]);
                        
                        // Si se rechaza la reserva, también rechazar los implementos
                        if ($spaceReservation->items()->count() > 0) {
                            $spaceReservation->items()->update(['status' => 'rejected']);
                        }
                    }
                }
            } else {
                // Si no es admin y la reserva ya estaba aprobada, volver a estado pendiente
                if ($spaceReservation->status == 'approved') {
                    $validated['status'] = 'pending';
                    // Limpiar los campos de aprobación
                    $validated['approved_by'] = null;
                    $validated['approved_at'] = null;
                    
                    \Log::info('Reserva devuelta a estado pendiente');
                    
                    // También actualizar los implementos a pendientes
                    if ($spaceReservation->items()->count() > 0) {
                        $spaceReservation->items()->update(['status' => 'pending']);
                    }
                }
            }
            
            // Solo verificar conflictos y disponibilidad si la fecha es futura y no es una aprobación o rechazo
            if (Carbon::parse($validated['date'])->gte(Carbon::today()) && !$fromPendingPage) {
                // Verificar si se puede realizar la reserva en esa fecha
                $canReserve = SpaceReservation::canReserveOnDate($validated['space_id'], $validated['date']);
                if (!$canReserve[0]) {
                    \Log::warning('Fecha no disponible para reserva', [
                        'reason' => $canReserve[1]
                    ]);
                    
                    if ($request->ajax()) {
                        return response()->json(['error' => 'No se puede reservar en esta fecha: ' . $canReserve[1]], 422);
                    }
                    
                    return redirect()->back()->withInput()->with('error', 'No se puede reservar en esta fecha: ' . $canReserve[1]);
                }
                
                // Verificar conflictos de horario (excluyendo la reserva actual)
                if (SpaceReservation::hasTimeConflict(
                    $validated['space_id'],
                    $validated['date'],
                    $validated['start_time'],
                    $validated['end_time'],
                    $spaceReservation->id
                )) {
                    \Log::warning('Conflicto de horario detectado');
                    
                    if ($request->ajax()) {
                        return response()->json(['error' => 'Ya existe otra reserva para este espacio en el horario seleccionado.'], 422);
                    }
                    
                    return redirect()->back()->withInput()->with('error', 'Ya existe otra reserva para este espacio en el horario seleccionado.');
                }
            }
            
            // Actualizar la reserva
            $spaceReservation->fill($validated);
            $spaceReservation->save();
            
            // Procesar los implementos seleccionados si se han modificado
            if ($request->has('items') && !$fromPendingPage) {
                // Obtener el espacio
                $space = Space::findOrFail($validated['space_id']);
                
                // Eliminar los implementos actuales (es más simple recrearlos)
                $spaceReservation->items()->delete();
                
                // Crear los nuevos implementos seleccionados
                foreach ($request->items as $itemId => $itemData) {
                    if (isset($itemData['selected']) && $itemData['selected']) {
                        $spaceItem = $space->items()->find($itemId);
                        if ($spaceItem && $spaceItem->available) {
                            $quantity = isset($itemData['quantity']) ? min($itemData['quantity'], $spaceItem->quantity) : 1;
                            
                            // Registrar el implemento en la reserva
                            $spaceReservation->items()->create([
                                'space_item_id' => $itemId,
                                'quantity' => $quantity,
                                'status' => $spaceReservation->status === 'approved' ? 'approved' : 'pending'
                            ]);
                        }
                    }
                }
            }
            
            $message = '';
            if ($request->has('status')) {
                $message = $request->status == 'approved' ? 'Reserva aprobada exitosamente.' : 'Reserva rechazada exitosamente.';
            } else {
                $message = 'Reserva actualizada exitosamente.';
            }
            
            \Log::info('Reserva actualizada exitosamente', [
                'reservation_id' => $spaceReservation->id,
                'new_status' => $spaceReservation->status
            ]);
            
            // Si es una solicitud AJAX, devolver una respuesta JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'reservation' => $spaceReservation->fresh()->load(['space', 'user'])
                ]);
            }
            
            // Determinar la redirección adecuada para solicitudes no-AJAX
            if ($fromPendingPage && url()->previous() == route('space-reservations.pending')) {
                return redirect()->route('space-reservations.pending')
                    ->with('success', $message);
            }
            
            return redirect()->route('space-reservations.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            // Capturar cualquier error durante la actualización
            \Log::error('Error al actualizar reserva', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax()) {
                return response()->json(['error' => 'Se produjo un error al actualizar la reserva: ' . $e->getMessage()], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Se produjo un error al actualizar la reserva. Detalles: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SpaceReservation $spaceReservation)
    {
        // Verificar que el usuario tenga permisos para eliminar esta reserva
        if (!$this->isSpaceAdmin() && Auth::id() != $spaceReservation->user_id) {
            return redirect()->route('space-reservations.index')
                ->with('error', 'No tiene permisos para eliminar esta reserva.');
        }
        
        // No permitir eliminar reservas pasadas
        if (Carbon::parse($spaceReservation->date)->lt(Carbon::today())) {
            return redirect()->route('space-reservations.index')
                ->with('error', 'No se puede eliminar una reserva pasada.');
        }
        
        // Los administradores pueden eliminar cualquier reserva
        // Los usuarios solo pueden cancelar sus propias reservas
        if ($this->isSpaceAdmin()) {
            $spaceReservation->delete();
            $message = 'Reserva eliminada exitosamente.';
        } else {
            $spaceReservation->update(['status' => 'cancelled']);
            $message = 'Reserva cancelada exitosamente.';
        }
        
        return redirect()->route('space-reservations.index')
            ->with('success', $message);
    }

    /**
     * Mostrar el calendario de reservas.
     */
    public function calendar(Request $request, $spaceId = null)
    {
        // Obtener todos los espacios activos
        $spaces = Space::where('active', true)->get();
        
        // Si no hay espacios disponibles, mostrar un mensaje
        if ($spaces->isEmpty()) {
            return view('space_reservations.calendar', [
                'spaces' => [],
                'selectedSpace' => null,
                'reservations' => [],
                'cycleDays' => [],
                'blockedDays' => [],
                'blockedSpaces' => [], // Agregar variable vacía
                'noSpacesAvailable' => true
            ]);
        }
        
        // Si no se especifica un espacio, usar el primero de la lista
        if (!$spaceId && $spaces->isNotEmpty()) {
            $spaceId = $spaces->first()->id;
        }
        
        // Si hay un espacio seleccionado, obtener sus reservas
        $selectedSpace = null;
        $reservations = [];
        $cycleDays = [];
        $blockedDays = [];
        $blockedSpaces = []; // Definir la variable
        
        if ($spaceId) {
            $selectedSpace = Space::findOrFail($spaceId);
            
            // Obtener el ciclo escolar activo
            $activeCycle = SchoolCycle::where('active', true)->first();
            
            if ($activeCycle) {
                // Obtener los días de ciclo
                $cycleDays = $activeCycle->cycleDays()
                    ->orderBy('date')
                    ->get()
                    ->keyBy(function ($item) {
                        return $item->date->format('Y-m-d');
                    });
                
                // Obtener los días bloqueados para este espacio
                $blockedDays = SpaceBlock::where('space_id', $spaceId)
                    ->where('school_cycle_id', $activeCycle->id)
                    ->pluck('cycle_day')
                    ->toArray();
                
                // Obtener todos los espacios bloqueados para mostrar en el sidebar
                $blockedSpaces = SpaceBlock::with('space')
                    ->where('school_cycle_id', $activeCycle->id)
                    ->get();
            }
            
            // Obtener las reservas para el espacio seleccionado
            $reservations = SpaceReservation::where('space_id', $spaceId)
                ->where('date', '>=', Carbon::now()->startOfMonth()->subMonth()->format('Y-m-d'))
                ->where('date', '<=', Carbon::now()->endOfMonth()->addMonths(2)->format('Y-m-d'))
                ->where('status', '!=', 'cancelled')
                ->where('status', '!=', 'rejected')
                ->get();
        }
        
        return view('space_reservations.calendar', compact(
            'spaces',
            'selectedSpace',
            'reservations',
            'cycleDays',
            'blockedDays',
            'blockedSpaces' // Incluir la variable en la vista
        ));
    }

    /**
     * Verificar disponibilidad de un espacio en una fecha.
     */
    public function checkAvailability($spaceId, $date)
    {
        $space = Space::findOrFail($spaceId);
        $dateObj = Carbon::parse($date);
        $dayOfWeek = strtolower($dateObj->format('l')); // obtener el día de la semana en inglés y en minúsculas
        
        // Verificar si se puede reservar en esta fecha
        $canReserve = SpaceReservation::canReserveOnDate($spaceId, $date);
        
        if (!$canReserve[0]) {
            return response()->json([
                'available' => false,
                'message' => $canReserve[1]
            ]);
        }
        
        // Obtener las reservas existentes para ese día
        $reservations = SpaceReservation::where('space_id', $spaceId)
            ->where('date', $date)
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'rejected')
            ->orderBy('start_time')
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'start' => $reservation->start_time instanceof \Carbon\Carbon 
                        ? $reservation->start_time->format('H:i') 
                        : substr($reservation->start_time, 0, 5),
                    'end' => $reservation->end_time instanceof \Carbon\Carbon 
                        ? $reservation->end_time->format('H:i') 
                        : substr($reservation->end_time, 0, 5),
                    'user' => $reservation->user->name,
                    'purpose' => $reservation->purpose,
                    'status' => $reservation->status
                ];
            });
        
        // Obtener bloqueos semanales para este día de la semana
        $activeCycle = SchoolCycle::where('active', true)->first();
        $weeklyBlocks = [];
        
        if ($activeCycle) {
            // Usar el nuevo método que incluye información sobre excepciones
            $blocks = SpaceBlock::getWeekdayBlocksForSpace($spaceId, $dayOfWeek, $date);
            
            if ($blocks->isNotEmpty()) {
                foreach ($blocks as $block) {
                    // Si el bloqueo tiene una excepción, agregar información sobre ello
                    if (isset($block->has_exception) && $block->has_exception) {
                        $weeklyBlocks[] = [
                            'id' => $block->id,
                            'start' => substr($block->start_time, 0, 5),
                            'end' => substr($block->end_time, 0, 5),
                            'reason' => $block->reason ?: 'Bloqueo semanal',
                            'has_exception' => true,
                            'exception_message' => 'Este bloqueo tiene una excepción para esta fecha'
                        ];
                    } else {
                        $weeklyBlocks[] = [
                            'id' => $block->id,
                            'start' => substr($block->start_time, 0, 5),
                            'end' => substr($block->end_time, 0, 5),
                            'reason' => $block->reason ?: 'Bloqueo semanal',
                            'has_exception' => false
                        ];
                    }
                }
            }
        }
        
        // Obtener el día del ciclo
        $cycleDay = null;
        
        if ($activeCycle) {
            $cycleDayObj = CycleDay::where('school_cycle_id', $activeCycle->id)
                ->where('date', $date)
                ->first();
            
            if ($cycleDayObj) {
                $cycleDay = $cycleDayObj->cycle_day;
            }
        }
        
        return response()->json([
            'available' => true,
            'space' => $space->name,
            'date' => $dateObj->format('Y-m-d'),
            'day_name' => $dateObj->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY'),
            'cycle_day' => $cycleDay,
            'reservations' => $reservations,
            'weekly_blocks' => $weeklyBlocks,
            'day_of_week' => ucfirst($dayOfWeek)  // Día de la semana en español
        ]);
    }

    /**
     * Obtener eventos para el calendario en formato JSON.
     */
    public function getEvents(Request $request)
    {
        \Log::info('Solicitud de eventos recibida', [
            'params' => $request->all(),
            'start' => $request->input('start'),
            'end' => $request->input('end'),
            'space_id' => $request->input('space_id'),
            'statuses' => $request->input('statuses')
        ]);
        
        // Obtener el rango de fechas desde la solicitud o usar un rango predeterminado
        $start = $request->filled('start') 
            ? Carbon::parse($request->start) 
            : Carbon::now()->startOfMonth()->subMonth();
            
        $end = $request->filled('end') 
            ? Carbon::parse($request->end) 
            : Carbon::now()->endOfMonth()->addMonths(2);

        try {
            $query = SpaceReservation::with(['space', 'user'])
                ->where('date', '>=', $start->format('Y-m-d'))
                ->where('date', '<=', $end->format('Y-m-d'));
            
            // Filtro por espacio
            if ($request->filled('space_id') && $request->space_id != 'all') {
                $query->where('space_id', $request->space_id);
            }
            
            // Filtro por estados
            if ($request->filled('statuses')) {
                $statuses = explode(',', $request->statuses);
                if (!empty($statuses)) {
                    $query->whereIn('status', $statuses);
                }
            }

            $reservations = $query->get();
            
            \Log::info('Reservas encontradas', [
                'count' => $reservations->count(),
                'filter_conditions' => [
                    'start_date' => $start->format('Y-m-d'),
                    'end_date' => $end->format('Y-m-d'),
                    'space_id' => $request->filled('space_id') ? $request->space_id : 'all',
                    'statuses' => $request->filled('statuses') ? $request->statuses : 'all'
                ]
            ]);
            
            $events = [];
            
            foreach ($reservations as $reservation) {
                // Extraer componentes de fecha y hora de forma segura
                $dateStr = $reservation->date->format('Y-m-d');
                
                // Extraer solo la hora de los campos de hora
                $startHour = is_string($reservation->start_time) ? $reservation->start_time : substr($reservation->start_time->format('H:i:s'), 0, 5);
                $endHour = is_string($reservation->end_time) ? $reservation->end_time : substr($reservation->end_time->format('H:i:s'), 0, 5);
                
                // Crear objetos de fecha y hora combinados correctamente
                $startDateTime = Carbon::createFromFormat('Y-m-d H:i', $dateStr . ' ' . $startHour);
                $endDateTime = Carbon::createFromFormat('Y-m-d H:i', $dateStr . ' ' . $endHour);
                
                $events[] = [
                    'id' => $reservation->id,
                    'title' => $reservation->purpose,
                    'start' => $startDateTime->format('Y-m-d\TH:i:s'),
                    'end' => $endDateTime->format('Y-m-d\TH:i:s'),
                    'extendedProps' => [
                        'user' => $reservation->user->name,
                        'space' => $reservation->space->name,
                        'purpose' => $reservation->purpose,
                        'status' => $reservation->status
                    ],
                    'backgroundColor' => $this->getStatusColor($reservation->status),
                    'borderColor' => $this->getStatusColor($reservation->status)
                ];
            }
            
            return response()->json($events);
        } catch (\Exception $e) {
            \Log::error('Error al procesar eventos del calendario', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Error al cargar los eventos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener contenido del modal para una reserva.
     */
    public function getModalContent(SpaceReservation $spaceReservation)
    {
        // Verificar que el usuario tenga permisos para ver esta reserva
        if (!$this->isSpaceAdmin() && Auth::id() != $spaceReservation->user_id) {
            return response('No autorizado', 403);
        }
        
        return view('space_reservations.modal', compact('spaceReservation'))->render();
    }
    
    /**
     * Obtener el color correspondiente al estado de la reserva.
     */
    private function getStatusColor($status)
    {
        switch ($status) {
            case 'approved':
                return '#28a745'; // Verde
            case 'pending':
                return '#ffc107'; // Amarillo
            case 'rejected':
                return '#dc3545'; // Rojo
            case 'cancelled':
                return '#6c757d'; // Gris
            default:
                return '#007bff'; // Azul por defecto
        }
    }

    /**
     * Mostrar las reservas pendientes de aprobación.
     */
    public function pending()
    {
        // Verificar que el usuario sea administrador
        if (!$this->isSpaceAdmin()) {
            return redirect()->route('space-reservations.index')
                ->with('error', 'No tiene permisos para acceder a esta sección.');
        }
        
        $reservations = SpaceReservation::with(['space', 'user'])
            ->where('status', 'pending')
            ->orderBy('date')
            ->orderBy('start_time')
            ->paginate(15);
            
        $spaces = Space::where('active', true)->get();
        
        return view('space_reservations.pending', compact('reservations', 'spaces'));
    }
    
    /**
     * Cancelar una reserva.
     */
    public function cancel(SpaceReservation $spaceReservation)
    {
        // Verificar que el usuario tenga permisos para cancelar esta reserva
        if (!$this->isSpaceAdmin() && Auth::id() != $spaceReservation->user_id) {
            return redirect()->route('space-reservations.index')
                ->with('error', 'No tiene permisos para cancelar esta reserva.');
        }
        
        // No permitir cancelar reservas pasadas
        if (Carbon::parse($spaceReservation->date)->lt(Carbon::today())) {
            return redirect()->route('space-reservations.index')
                ->with('error', 'No se puede cancelar una reserva pasada.');
        }
        
        // Actualizar el estado de la reserva a "cancelled"
        $spaceReservation->update(['status' => 'cancelled']);
        
        return redirect()->back()
            ->with('success', 'Reserva cancelada exitosamente.');
    }

    /**
     * Crear una nueva reserva basada en una existente.
     */
    public function copy(SpaceReservation $spaceReservation)
    {
        // Verificar que el usuario tenga permisos para ver esta reserva
        if (!$this->isSpaceAdmin() && Auth::id() != $spaceReservation->user_id) {
            return redirect()->route('space-reservations.index')
                ->with('error', 'No tiene permisos para copiar esta reserva.');
        }
        
        // Obtener todos los espacios activos
        $spaces = Space::where('active', true)->get();
        $activeCycle = SchoolCycle::where('active', true)->first();
        
        if (!$activeCycle) {
            return redirect()->back()->with('error', 'No hay un ciclo escolar activo. No se pueden realizar reservas.');
        }
        
        // Crear una nueva instancia con los datos de la reserva original, pero sin guardarla
        $newReservation = new SpaceReservation([
            'space_id' => $spaceReservation->space_id,
            'date' => $spaceReservation->date,
            'start_time' => $spaceReservation->start_time,
            'end_time' => $spaceReservation->end_time,
            'purpose' => $spaceReservation->purpose,
            'notes' => $spaceReservation->notes,
        ]);
        
        // Mostrar el formulario de creación pre-llenado
        return view('space_reservations.create', [
            'spaces' => $spaces,
            'activeCycle' => $activeCycle,
            'reservation' => $newReservation,
            'isCopy' => true,
            'originalId' => $spaceReservation->id
        ]);
    }
}
