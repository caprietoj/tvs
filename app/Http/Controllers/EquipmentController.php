<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentLoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Traits\NotificationHelpers;
use App\Mail\EquipmentLoanRequested;

class EquipmentController extends Controller
{
    use NotificationHelpers;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if ($request->expectsJson()) {
                $this->catchJsonErrors();
            }
            return $next($request);
        });
    }

    protected function catchJsonErrors()
    {
        app()->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            function ($app) {
                return new class extends \App\Exceptions\Handler {
                    public function render($request, \Throwable $e)
                    {
                        \Log::error('Error en solicitud JSON', [
                            'error' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => $e->getTraceAsString()
                        ]);

                        return response()->json([
                            'success' => false,
                            'error' => 'Error en el servidor: ' . $e->getMessage()
                        ], 500);
                    }
                };
            }
        );
    }

    public function index()
    {
        $equipment = Equipment::all();
        return view('equipment.index', compact('equipment'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:laptop,ipad',
            'section' => 'required|in:bachillerato,preescolar_primaria',
            'total_units' => 'required|integer|min:1'
        ]);

        $equipment = Equipment::create([
            'type' => $validated['type'],
            'section' => $validated['section'],
            'total_units' => $validated['total_units'],
            'available_units' => $validated['total_units']
        ]);

        return redirect()->route('equipment.index')
            ->with('success', 'Equipos registrados correctamente');
    }

    /**
     * Get class schedule periods for the requested section and date
     */
    public function getClassSchedule(Request $request)
    {
        $validated = $request->validate([
            'section' => 'required|string',
            'loan_date' => 'required|date',
            'sub_section' => 'nullable|string'
        ]);
        
        $section = $validated['section'];
        $loanDate = Carbon::parse($validated['loan_date']);
        $isFriday = $loanDate->dayOfWeek === Carbon::FRIDAY;
        $subSection = $request->has('sub_section') ? $validated['sub_section'] : null;
        
        // Obtener períodos de clase según sección y día de la semana
        $dayType = $isFriday ? 'friday' : 'regular';
        $schedule = \App\Models\ClassSchedule::getClassPeriods($section, $dayType, $subSection);
        
        // Formatear para la respuesta JSON
        $formattedPeriods = [];
        
        // Agregar períodos de clase
        foreach ($schedule['periods'] as $index => $period) {
            $formattedPeriods[] = [
                'id' => 'period_' . $index,
                'type' => 'class',
                'start' => $period[0],
                'end' => $period[1],
                'label' => 'Clase ' . ($index + 1)
            ];
        }
        
        // Agregar períodos de descanso
        foreach ($schedule['breaks'] as $index => $break) {
            $formattedPeriods[] = [
                'id' => 'break_' . $index,
                'type' => 'break',
                'start' => $break[0],
                'end' => $break[1],
                'label' => $break[2]
            ];
        }
        
        // Ordenar por hora de inicio
        usort($formattedPeriods, function($a, $b) {
            return $a['start'] <=> $b['start'];
        });
        
        return response()->json([
            'periods' => $formattedPeriods,
            'is_friday' => $isFriday,
            'day_of_week' => $loanDate->format('l')
        ]);
    }

    /**
     * Modificación del método requestLoan para usar los horarios predefinidos
     */
    public function requestLoan(Request $request)
    {
        try {
            Log::info('Iniciando solicitud de préstamo', $request->all());
            
            // Obtener la fecha de mañana
            $tomorrow = now()->addDay()->format('Y-m-d');
            
            // Determinar la fecha máxima permitida según el día de la semana
            $today = now();
            if ($today->dayOfWeek === 5) { // Si hoy es viernes (5)
                // Permitir reservar para toda la próxima semana (hasta el domingo siguiente)
                $endOfWeek = $today->copy()->addDays(9)->format('Y-m-d');
            } else if ($today->dayOfWeek === 0) { // Si hoy es domingo (0)
                $endOfWeek = $today->copy()->addDays(7)->format('Y-m-d'); // Próximo domingo
            } else {
                $endOfWeek = $today->copy()->endOfWeek()->format('Y-m-d'); // Domingo de esta semana
            }
            
            $validated = $request->validate([
                'equipment_id' => 'required|exists:equipment,id',
                'section' => 'required',
                'grade' => 'required',
                'loan_date' => 'required|date|after_or_equal:' . $tomorrow . '|before_or_equal:' . $endOfWeek,
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'units_requested' => 'required|integer|min:1',
                'period_id' => 'nullable|string' // ID del período de clase seleccionado
            ], [
                'equipment_id.required' => 'Debe seleccionar un tipo de equipo.',
                'equipment_id.exists' => 'El equipo seleccionado no existe en nuestro inventario.',
                'section.required' => 'Debe seleccionar una sección.',
                'grade.required' => 'Debe ingresar el salón o ubicación.',
                'loan_date.required' => 'Debe seleccionar una fecha para el préstamo.',
                'loan_date.after_or_equal' => 'La fecha de préstamo debe ser mínimo para mañana.',
                'loan_date.before_or_equal' => 'La fecha de préstamo está fuera del rango permitido. Los viernes puede reservar para toda la semana siguiente.',
                'start_time.required' => 'Debe seleccionar una hora de inicio.',
                'start_time.date_format' => 'El formato de la hora de inicio no es válido.',
                'end_time.required' => 'Debe seleccionar una hora de finalización.',
                'end_time.date_format' => 'El formato de la hora de finalización no es válido.',
                'end_time.after' => 'La hora de finalización debe ser posterior a la hora de inicio.',
                'units_requested.required' => 'Debe indicar cuántos equipos necesita.',
                'units_requested.integer' => 'La cantidad de equipos debe ser un número entero.',
                'units_requested.min' => 'Debe solicitar al menos un equipo.'
            ]);

            DB::beginTransaction();

            $equipment = Equipment::findOrFail($validated['equipment_id']);
            
            // Si se seleccionó un período específico
            if ($request->filled('period_id')) {
                $periodId = $validated['period_id'];
                $loanDate = Carbon::parse($validated['loan_date']);
                $isFriday = $loanDate->dayOfWeek === Carbon::FRIDAY;
                $dayType = $isFriday ? 'friday' : 'regular';
                $subSection = $request->filled('sub_section') ? $request->sub_section : null;
                
                // Verificar si es un período simple o un bloque de períodos
                if (strpos($periodId, ':') !== false) {
                    // Formato bloque: "start_period_id:end_period_id"
                    list($startPeriodId, $endPeriodId) = explode(':', $periodId);
                    
                    // Obtener todos los períodos de clase
                    $schedule = \App\Models\ClassSchedule::getClassPeriods($validated['section'], $dayType, $subSection);
                    
                    // Convertir IDs de períodos a índices de períodos
                    $startParts = explode('_', $startPeriodId);
                    $endParts = explode('_', $endPeriodId);
                    
                    if ($startParts[0] === 'period' && $endParts[0] === 'period') {
                        $startIndex = (int)$startParts[1];
                        $endIndex = (int)$endParts[1];
                        
                        // Verificar que existen ambos períodos
                        if (isset($schedule['periods'][$startIndex]) && isset($schedule['periods'][$endIndex])) {
                            $validated['start_time'] = $schedule['periods'][$startIndex][0];
                            $validated['end_time'] = $schedule['periods'][$endIndex][1];
                            
                            Log::info('Bloque de períodos seleccionado', [
                                'start_period' => $startIndex,
                                'end_period' => $endIndex,
                                'start_time' => $validated['start_time'],
                                'end_time' => $validated['end_time']
                            ]);
                        }
                    }
                } else {
                    // Determinar automáticamente la hora de inicio y fin basada en un solo período seleccionado
                    $schedule = \App\Models\ClassSchedule::getClassPeriods($validated['section'], $dayType, $subSection);
                    $periodParts = explode('_', $periodId);
                    $periodType = $periodParts[0]; // 'period' o 'break'
                    $periodIndex = (int)$periodParts[1];
                    
                    if ($periodType === 'period' && isset($schedule['periods'][$periodIndex])) {
                        $period = $schedule['periods'][$periodIndex];
                        $validated['start_time'] = $period[0];
                        $validated['end_time'] = $period[1];
                    } elseif ($periodType === 'break' && isset($schedule['breaks'][$periodIndex])) {
                        // No permitir reservar durante los descansos
                        return back()
                            ->withInput()
                            ->with('error', 'No se pueden reservar equipos durante los períodos de descanso.');
                    }
                }
            }

            // Obtener todos los préstamos para la fecha
            $allLoans = EquipmentLoan::where('equipment_id', $validated['equipment_id'])
                ->where('loan_date', $validated['loan_date'])
                ->where('status', '!=', 'returned')
                ->get();
            
            // Convertir las horas a Carbon para facilitar la comparación
            // Usamos createFromFormat para evitar problemas con concatenación de fechas
            $loanDate = Carbon::parse($validated['loan_date'])->format('Y-m-d');
            $requestStart = Carbon::createFromFormat('Y-m-d H:i', $loanDate . ' ' . $validated['start_time']);
            $requestEnd = Carbon::createFromFormat('Y-m-d H:i', $loanDate . ' ' . $validated['end_time']);
            
            // Encontrar los préstamos que se solapan con el horario solicitado
            $conflictingLoans = $allLoans->filter(function ($loan) use ($requestStart, $requestEnd) {
                // Extraer solo el componente de fecha y tiempo para prevenir doble especificación
                $loanDate = $loan->loan_date->format('Y-m-d');
                // Extraer solo el componente de tiempo del start_time y end_time
                $startTime = Carbon::parse($loan->start_time)->format('H:i');
                $endTime = Carbon::parse($loan->end_time)->format('H:i');
                
                $loanStart = Carbon::createFromFormat('Y-m-d H:i', $loanDate . ' ' . $startTime);
                $loanEnd = Carbon::createFromFormat('Y-m-d H:i', $loanDate . ' ' . $endTime);
                
                return ($requestStart < $loanEnd) && ($loanStart < $requestEnd);
            });
            
            // Calcular la máxima cantidad de unidades ocupadas en cualquier momento del intervalo solicitado
            $maxOccupiedUnits = $this->calculateMaxOccupiedUnitsForTimeRange(
                $conflictingLoans, 
                $validated['start_time'], 
                $validated['end_time']
            );
            
            // La disponibilidad real para este horario es el total de unidades menos las ocupadas en este horario específico
            $availableForTimeSlot = $equipment->total_units - $maxOccupiedUnits;
            
            // Registro adicional para depuración
            Log::info('Cálculo de disponibilidad en requestLoan', [
                'equipment_id' => $equipment->id,
                'equipment_type' => $equipment->type,
                'total_units' => $equipment->total_units,
                'max_occupied_units' => $maxOccupiedUnits,
                'available_for_time_slot' => $availableForTimeSlot,
                'requested_units' => $validated['units_requested']
            ]);
            
            if ($availableForTimeSlot < $validated['units_requested']) {
                Log::warning('Préstamo solicitado con unidades insuficientes', [
                    'equipment_id' => $equipment->id,
                    'units_requested' => $validated['units_requested'],
                    'available_for_time_slot' => $availableForTimeSlot,
                    'total_units' => $equipment->total_units,
                    'max_occupied_units' => $maxOccupiedUnits,
                    'loan_date' => $validated['loan_date'],
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time']
                ]);
                
                return back()
                    ->withInput()
                    ->with('error', 'No hay suficientes equipos disponibles para el horario seleccionado. 
                        Disponibles: ' . $availableForTimeSlot . ' de ' . $equipment->total_units . ' unidades. 
                        Pruebe con otro horario o solicite menos unidades.');
            }
            
            // Crear el préstamo con devolución automática programada
            $loan = EquipmentLoan::create([
                'user_id' => auth()->id(),
                'equipment_id' => $validated['equipment_id'],
                'section' => $validated['section'],
                'grade' => $validated['grade'],
                'loan_date' => $validated['loan_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'units_requested' => $validated['units_requested'],
                'status' => 'pending',
                'auto_return' => true, // Marcar que este préstamo debe devolverse automáticamente
                'period_id' => $request->filled('period_id') ? $request->period_id : null
            ]);

            // Cargar la relación equipment para asegurar que esté disponible en el correo
            $loan->load('equipment', 'user');

            // Enviar notificación por correo al usuario y al administrador
            $user = auth()->user();
            Mail::to([
                $user->email,
                config('mail.equipment_admin')
            ])->send(new EquipmentLoanRequested($loan));

            DB::commit();
            
            Log::info('Préstamo creado exitosamente', [
                'loan_id' => $loan->id,
                'units_requested' => $validated['units_requested'],
                'available_units' => $equipment->available_units,
                'available_for_time_slot' => $availableForTimeSlot,
                'auto_return' => true,
                'period_id' => $request->filled('period_id') ? $request->period_id : null
            ]);

            return redirect()->route('equipment.loans')
                ->with('success', 'Solicitud de préstamo registrada correctamente. El equipo será devuelto automáticamente al finalizar el período de clase.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en requestLoan: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Error al procesar la solicitud: ' . $e->getMessage());
        }
    }

    public function resetInventory()
    {
        try {
            Log::info('Iniciando reseteo de inventario');
            
            DB::beginTransaction();
            
            $affected = Equipment::query()->update([
                'available_units' => DB::raw('total_units')
            ]);
            
            Log::info('Equipos actualizados: ' . $affected);
            
            DB::commit();
            
            if ($affected > 0) {
                return redirect()->back()->with('success', 'Inventario reiniciado correctamente (' . $affected . ' equipos actualizados)');
            }
            
            return redirect()->back()->with('info', 'No hubo cambios en el inventario');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en resetInventory: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al reiniciar el inventario. Por favor, intente nuevamente.');
        }
    }

    public function showRequestForm()
    {
        $equipment = Equipment::all();
        return view('equipment.request', compact('equipment'));
    }

    public function showLoans()
    {
        $loans = EquipmentLoan::with(['equipment', 'user'])
            ->orderBy('loan_date', 'desc')
            ->get();
        return view('equipment.loans', compact('loans'));
    }

    public function inventory()
    {
        $equipment = Equipment::all();
        return view('equipment.inventory', compact('equipment'));
    }

    public function dashboard()
    {
        // Obtener estadísticas generales
        $totalLoans = EquipmentLoan::count();
        $activeLoans = EquipmentLoan::where('loan_date', '>=', now())->count();
        
        // Préstamos por sección
        $loansBySection = EquipmentLoan::select('section', DB::raw('count(*) as total'))
            ->groupBy('section')
            ->get();

        // Equipos más solicitados - Corregida la ambigüedad de la columna section
        $mostRequestedEquipment = Equipment::select(
            'equipment.type',
            'equipment.section',
            DB::raw('count(*) as total_loans')
        )
            ->join('equipment_loans', 'equipment.id', '=', 'equipment_loans.equipment_id')
            ->groupBy('equipment.type', 'equipment.section')
            ->orderBy('total_loans', 'desc')
            ->get();

        // Préstamos por mes
        $loansByMonth = EquipmentLoan::select(
            DB::raw('MONTH(loan_date) as month'),
            DB::raw('count(*) as total')
        )
            ->whereYear('loan_date', now()->year)
            ->groupBy('month')
            ->get();

        return view('equipment.dashboard', compact(
            'totalLoans',
            'activeLoans',
            'loansBySection',
            'mostRequestedEquipment',
            'loansByMonth'
        ));
    }

    public function getLoansData(Request $request)
    {
        try {
            $loans = EquipmentLoan::join('equipment', 'equipment_loans.equipment_id', '=', 'equipment.id')
                ->select(
                    'equipment_loans.*',
                    'equipment.type as equipment_type'
                );

            if ($request->filled('month')) {
                $loans->whereMonth('loan_date', $request->month);
            }

            $loansData = $loans->get();

            // Calcular los totales para el resumen
            $summary = [
                'ipads_primaria' => $loansData->where('section', 'preescolar_primaria')
                    ->where('equipment_type', 'ipad')
                    ->count(),
                'ipads_bachillerato' => $loansData->where('section', 'bachillerato')
                    ->where('equipment_type', 'ipad')
                    ->count(),
                'laptops_bachillerato' => $loansData->where('section', 'bachillerato')
                    ->where('equipment_type', 'laptop')
                    ->count()
            ];

            return response()->json([
                'draw' => request()->draw,
                'recordsTotal' => $loansData->count(),
                'recordsFiltered' => $loansData->count(),
                'data' => $loansData->map(function($loan) {
                    return [
                        'section' => $loan->section,
                        'grade' => $loan->grade,
                        'equipment_type' => $loan->equipment_type,
                        'units_requested' => $loan->units_requested,
                        'loan_date' => $loan->loan_date ? Carbon::parse($loan->loan_date)->format('d/m/Y') : '',
                        'start_time' => $loan->start_time ? Carbon::parse($loan->start_time)->format('H:i') : '',
                        'end_time' => $loan->end_time ? Carbon::parse($loan->end_time)->format('H:i') : ''
                    ];
                }),
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getLoansData: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al obtener los datos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEquipmentTypes($section)
    {
        try {
            $equipment = null;
            
            if ($section === 'administrativo') {
                // Para administrativos, mostrar equipos de todas las secciones
                $equipment = Equipment::whereIn('section', ['bachillerato', 'preescolar_primaria']);
            } else if ($section === 'bachillerato') {
                // Para bachillerato, mostrar laptops e iPads de bachillerato
                $equipment = Equipment::where('section', 'bachillerato')
                    ->whereIn('type', ['laptop', 'ipad']);
            } else if ($section === 'preescolar_primaria') {
                // Para preescolar_primaria, mostrar solo iPads de esa sección
                $equipment = Equipment::where('section', 'preescolar_primaria')
                    ->where('type', 'ipad');
            }

            // Get equipment with their active loans
            $equipment = $equipment->get();

            // Calculate real availability for each equipment
            $mappedEquipment = $equipment->map(function($item) {
                // Para el caso de disponibilidad general, solo consideramos el total de unidades
                // ya que las disponibilidades específicas por horario se calcularán después
                return [
                    'id' => $item->id,
                    'type' => $item->type,
                    'section' => $item->section,
                    'available_units' => $item->total_units,
                    'show_availability' => $item->total_units > 0,
                    'total_units' => $item->total_units
                ];
            });

            return response()->json($mappedEquipment);

        } catch (\Exception $e) {
            \Log::error('Error en getEquipmentTypes: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener equipos'], 500);
        }
    }

    public function checkAvailability(Request $request)
    {
        // Validación básica para la entrega de equipos
        if ($request->has('equipment_id') && !$request->has('loan_date')) {
            $equipment = Equipment::findOrFail($request->equipment_id);
            return response()->json([
                'total_units' => $equipment->total_units,
                'available_units' => $equipment->available_units
            ]);
        }

        // Obtener la fecha de mañana
        $tomorrow = now()->addDay()->format('Y-m-d');
        
        // Determinar la fecha máxima permitida según el día de la semana
        $today = now();
        if ($today->dayOfWeek === 5) { // Si hoy es viernes (5)
            // Permitir reservar para toda la próxima semana (hasta el domingo siguiente)
            $endOfWeek = $today->copy()->addDays(9)->format('Y-m-d');
        } else if ($today->dayOfWeek === 0) { // Si hoy es domingo (0)
            $endOfWeek = $today->copy()->addDays(7)->format('Y-m-d'); // Próximo domingo
        } else {
            $endOfWeek = $today->copy()->endOfWeek()->format('Y-m-d'); // Domingo de esta semana
        }
        
        // Validación completa para la planificación de préstamos
        $validated = $request->validate([
            'equipment_id' => 'required|exists:equipment,id',
            'loan_date' => 'required|date|after_or_equal:' . $tomorrow . '|before_or_equal:' . $endOfWeek,
            'section' => 'required',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i'
        ], [
            'loan_date.after_or_equal' => 'La fecha de préstamo debe ser mínimo para mañana.',
            'loan_date.before_or_equal' => 'La fecha de préstamo está fuera del rango permitido. Los viernes puede reservar para toda la semana siguiente.'
        ]);

        $equipment = Equipment::findOrFail($validated['equipment_id']);
        
        // Obtener préstamos activos para la fecha seleccionada
        $loans = EquipmentLoan::where('equipment_id', $validated['equipment_id'])
            ->where('loan_date', $validated['loan_date'])
            ->where('status', '!=', 'returned')
            ->orderBy('start_time')
            ->get();

        // Si se proporcionaron horarios específicos, verificar la disponibilidad en ese intervalo
        $conflictingLoans = [];
        
        if ($request->has('start_time') && $request->has('end_time')) {
            $requestStart = $validated['start_time'];
            $requestEnd = $validated['end_time'];
            
            // Filtrar préstamos que se solapan con el intervalo solicitado
            foreach ($loans as $loan) {
                $loanStart = Carbon::parse($loan->start_time)->format('H:i');
                $loanEnd = Carbon::parse($loan->end_time)->format('H:i');
                
                if ($this->timesOverlap($requestStart, $requestEnd, $loanStart, $loanEnd)) {
                    $conflictingLoans[] = $loan;
                }
            }
            
            // Calcular unidades ocupadas en el horario específico
            $occupiedUnits = collect($conflictingLoans)->sum('units_requested');
            
            // Calcular disponibilidad real para el horario específico
            // El total disponible es el total de unidades menos las ocupadas en este horario
            $availableUnits = max(0, $equipment->total_units - $occupiedUnits);
        } else {
            // Si no se proporcionaron horarios específicos, buscar el horario con menos disponibilidad
            $occupiedUnitsByHour = $this->calculateMaxOccupiedUnits($loans, $equipment->total_units);
            $availableUnits = $equipment->total_units - $occupiedUnitsByHour;
            $occupiedUnits = $occupiedUnitsByHour;
        }

        // Registrar los datos para depuración
        Log::info('Cálculo de disponibilidad para equipo', [
            'equipment_id' => $equipment->id,
            'equipment_type' => $equipment->type,
            'total_units' => $equipment->total_units,
            'available_units' => $availableUnits,
            'occupied_units' => isset($occupiedUnits) ? $occupiedUnits : 0,
            'has_specific_time' => $request->has('start_time') && $request->has('end_time'),
            'time_range' => $request->has('start_time') ? $validated['start_time'] . ' - ' . $validated['end_time'] : 'No especificado',
            'conflicting_loans_count' => count($conflictingLoans ?? [])
        ]);
        
        return response()->json([
            'total_units' => $equipment->total_units,
            'available_units' => $availableUnits,
            'initial_units' => $equipment->available_units,
            'occupied_units' => isset($occupiedUnits) ? $occupiedUnits : 0,
            'occupied_slots' => $loans->map(function($loan) {
                return [
                    'start' => Carbon::parse($loan->start_time)->format('H:i'),
                    'end' => Carbon::parse($loan->end_time)->format('H:i'),
                    'units_taken' => $loan->units_requested
                ];
            }),
            'has_availability_by_hour' => true
        ]);
    }

    private function calculateAvailableSlots($sectionTimes, $loans)
    {
        $availableSlots = [];
        
        foreach ($sectionTimes as $period => $times) {
            $slot = [
                'period' => $period,
                'start' => $times[0],
                'end' => $times[1],
                'is_available' => true,
                'conflicts' => []
            ];

            // Verificar conflictos con préstamos existentes
            foreach ($loans as $loan) {
                if ($this->timesOverlap($times[0], $times[1], $loan->start_time, $loan->end_time)) {
                    $slot['conflicts'][] = [
                        'start' => $loan->start_time,
                        'end' => $loan->end_time,
                        'units' => $loan->units_requested
                    ];
                }
            }

            $availableSlots[] = $slot;
        }

        return $availableSlots;
    }

    private function timesOverlap($start1, $end1, $start2, $end2)
    {
        return $start1 < $end2 && $start2 < $end1;
    }

    public function deliverEquipment(Request $request, EquipmentLoan $loan)
    {
        try {
            DB::beginTransaction();

            $equipment = Equipment::findOrFail($loan->equipment_id);

            // Solo descontar del inventario si hay suficientes unidades disponibles
            if ($equipment->available_units >= $loan->units_requested) {
                // Descontar unidades del inventario
                $equipment->available_units -= $loan->units_requested;
                $equipment->save();

                $loan->update([
                    'status' => 'delivered',
                    'delivery_signature' => $request->signature,
                    'delivery_observations' => $request->delivery_observations,
                    'delivery_date' => now(),
                    'inventory_discounted' => true // Marcar que se descontó del inventario
                ]);

                Log::info('Equipo entregado y descontado del inventario', [
                    'loan_id' => $loan->id,
                    'equipment_id' => $loan->equipment_id,
                    'units_requested' => $loan->units_requested,
                    'available_units' => $equipment->available_units
                ]);
            } else {
                $loan->update([
                    'status' => 'delivered',
                    'delivery_signature' => $request->signature,
                    'delivery_observations' => $request->delivery_observations . ' (Entregado sin stock disponible)',
                    'delivery_date' => now(),
                    'inventory_discounted' => false
                ]);

                Log::warning('Equipo entregado sin stock disponible', [
                    'loan_id' => $loan->id,
                    'equipment_id' => $loan->equipment_id,
                    'units_requested' => $loan->units_requested,
                    'available_units' => $equipment->available_units
                ]);
            }

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en deliverEquipment: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function returnEquipment(Request $request, EquipmentLoan $loan)
    {
        try {
            if (!$request->has('signature')) {
                throw new \Exception('La firma es requerida para procesar la devolución');
            }

            \Log::info('Iniciando proceso de devolución', [
                'loan_id' => $loan->id,
                'status' => $loan->status,
                'inventory_discounted' => $loan->inventory_discounted ?? false
            ]);

            DB::beginTransaction();

            // Solo proceder si el préstamo está en estado 'delivered'
            if ($loan->status !== 'delivered') {
                throw new \Exception('Solo se pueden procesar devoluciones de préstamos entregados');
            }

            // Obtener el equipo
            $equipment = Equipment::findOrFail($loan->equipment_id);
            
            \Log::info('Equipo encontrado', [
                'equipment_id' => $equipment->id,
                'available_units' => $equipment->available_units,
                'total_units' => $equipment->total_units
            ]);

            // Devolver unidades al inventario si fueron descontadas
            if ($loan->inventory_discounted) {
                $newAvailableUnits = $equipment->available_units + $loan->units_requested;
                
                if ($newAvailableUnits > $equipment->total_units) {
                    throw new \Exception('La devolución excedería el total de unidades disponibles');
                }

                $equipment->available_units = $newAvailableUnits;
                $equipment->save();

                \Log::info('Unidades devueltas al inventario', [
                    'previous_units' => $equipment->available_units - $loan->units_requested,
                    'returned_units' => $loan->units_requested,
                    'new_total' => $equipment->available_units
                ]);
            }

            // Actualizar el préstamo
            $updateData = [
                'status' => 'returned',
                'return_signature' => $request->signature,
                'return_date' => now()
            ];

            if ($request->has('return_observations')) {
                $updateData['return_observations'] = $request->return_observations;
            }

            if (Schema::hasColumn('equipment_loans', 'inventory_returned')) {
                $updateData['inventory_returned'] = true;
            }

            $loan->update($updateData);

            DB::commit();

            \Log::info('Devolución procesada exitosamente', [
                'loan_id' => $loan->id,
                'equipment_id' => $equipment->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Equipo devuelto correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error en returnEquipment', [
                'loan_id' => $loan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exporta los préstamos de equipos a Excel
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportLoans(Request $request)
    {
        // Construir la consulta base
        $query = EquipmentLoan::with(['equipment', 'user'])
                 ->join('equipment', 'equipment_loans.equipment_id', '=', 'equipment.id')
                 ->select('equipment_loans.*', 'equipment.type as equipment_type');
        
        // Aplicar filtros si existen
        if ($request->filled('section')) {
            $query->where('equipment_loans.section', 'like', '%' . $request->section . '%');
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('equipment_loans.loan_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('equipment_loans.loan_date', '<=', $request->date_to);
        }
        
        // Obtener los préstamos filtrados
        $loans = $query->get();
        
        // Crear el archivo Excel con los datos filtrados
        return \Excel::download(new \App\Exports\EquipmentLoansExport($loans), 'prestamos-equipos.xlsx');
    }

    /**
     * Actualiza un préstamo de equipo existente
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateLoan(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            // Validar datos de entrada
            $validated = $request->validate([
                'loan_date' => 'required|date',
                'start_time' => 'required',
                'end_time' => 'required|after:start_time',
                'units_requested' => 'required|integer|min:1',
                'grade' => 'required|string|max:50',
            ]);

            // Buscar el préstamo
            $loan = EquipmentLoan::findOrFail($id);

            // Verificar que el préstamo esté pendiente
            if ($loan->status !== 'pending') {
                throw new \Exception('Solo se pueden editar préstamos pendientes');
            }

            // Verificar disponibilidad de equipos si se cambia la cantidad
            if ($loan->units_requested != $validated['units_requested']) {
                $equipment = Equipment::findOrFail($loan->equipment_id);
                $difference = $validated['units_requested'] - $loan->units_requested;
                
                if ($difference > 0 && $equipment->available_units < $difference) {
                    throw new \Exception('No hay suficientes unidades disponibles');
                }
                
                // Actualizar disponibilidad de equipos
                $equipment->available_units -= $difference;
                $equipment->save();
            }

            // Actualizar préstamo
            $loan->update([
                'loan_date' => $validated['loan_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'units_requested' => $validated['units_requested'],
                'grade' => $validated['grade'],
            ]);

            DB::commit();
            
            Log::info('Préstamo actualizado exitosamente', [
                'loan_id' => $loan->id,
                'equipment_id' => $loan->equipment_id
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en updateLoan: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Activa o desactiva la devolución automática para un préstamo.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleAutoReturn($id)
    {
        try {
            DB::beginTransaction();
            
            // Buscar el préstamo
            $loan = EquipmentLoan::findOrFail($id);
            
            // No permitir cambiar la devolución automática en préstamos ya devueltos
            if ($loan->status === 'returned') {
                throw new \Exception('No se puede cambiar la devolución automática en préstamos ya devueltos');
            }
            
            // Invertir el valor actual de auto_return
            $loan->auto_return = !$loan->auto_return;
            $loan->save();
            
            DB::commit();
            
            // Registrar la acción
            $actionType = $loan->auto_return ? 'activada' : 'desactivada';
            Log::info("Devolución automática {$actionType} para el préstamo ID: {$loan->id}");
            
            return response()->json([
                'success' => true,
                'autoReturn' => $loan->auto_return,
                'message' => 'Devolución automática ' . ($loan->auto_return ? 'activada' : 'desactivada') . ' correctamente'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en toggleAutoReturn: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * Edit a loan record.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function editLoan(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $loan = EquipmentLoan::findOrFail($id);
            
            // Validar datos de entrada
            $validated = $request->validate([
                'loan_date' => 'required|date',
                'start_time' => 'required',
                'end_time' => 'required|after:start_time',
                'units_requested' => 'required|integer|min:1',
                'grade' => 'required'
            ]);
            
            // Solo permite editar préstamos pendientes
            if ($loan->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'error' => 'Solo se pueden editar préstamos en estado pendiente'
                ], 422);
            }
            
            // Verificar disponibilidad de equipos si se cambia la cantidad
            if ($loan->units_requested != $validated['units_requested']) {
                $equipment = Equipment::findOrFail($loan->equipment_id);
                $difference = $validated['units_requested'] - $loan->units_requested;
                
                if ($difference > 0 && $equipment->available_units < $difference) {
                    return response()->json([
                        'success' => false,
                        'error' => 'No hay suficientes unidades disponibles'
                    ], 422);
                }
                
                // Actualizar disponibilidad de equipos
                $equipment->available_units -= $difference;
                $equipment->save();
            }
            
            // Actualizar el préstamo
            $loan->loan_date = $validated['loan_date'];
            $loan->start_time = $validated['start_time'];
            $loan->end_time = $validated['end_time'];
            $loan->units_requested = $validated['units_requested'];
            $loan->grade = $validated['grade'];
            $loan->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Préstamo actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al editar préstamo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al procesar la edición: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a loan record.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteLoan($id)
    {
        try {
            $loan = EquipmentLoan::findOrFail($id);
            
            // Solo permite eliminar préstamos pendientes
            if ($loan->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'error' => 'Solo se pueden eliminar préstamos en estado pendiente'
                ], 422);
            }
            
            $loan->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Préstamo eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar préstamo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al eliminar el préstamo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método para procesar la devolución automática de equipos
     * Este método debe ser llamado por un cron job cada 5-10 minutos
     */
    public function processAutoReturns()
    {
        try {
            Log::info('Iniciando proceso de devolución automática de equipos');
            
            $now = Carbon::now();
            
            // Obtener préstamos que deberían finalizar ahora
            $loansToReturn = EquipmentLoan::with('equipment')
                ->where('status', 'delivered')
                ->where('auto_return', true)
                ->where('loan_date', '<=', $now->format('Y-m-d'))
                ->where('end_time', '<=', $now->format('H:i'))
                ->where('inventory_returned', false)
                ->get();
                
            if ($loansToReturn->count() === 0) {
                Log::info('No hay préstamos para devolver automáticamente');
                return response()->json([
                    'message' => 'No hay préstamos para devolver automáticamente',
                    'count' => 0
                ]);
            }
            
            DB::beginTransaction();
            
            $processed = [];
            foreach ($loansToReturn as $loan) {
                try {
                    // Actualizar el estado del préstamo
                    $loan->update([
                        'status' => 'returned',
                        'return_signature' => 'Devolución automática por sistema',
                        'return_observations' => 'Devolución procesada automáticamente al finalizar el período de clase',
                        'return_date' => now(),
                        'inventory_returned' => true
                    ]);
                    
                    // Devolver unidades al inventario si fueron descontadas
                    if ($loan->inventory_discounted) {
                        $equipment = $loan->equipment;
                        
                        // Verificar que no exceda el total de unidades
                        $newAvailableUnits = $equipment->available_units + $loan->units_requested;
                        if ($newAvailableUnits <= $equipment->total_units) {
                            $equipment->available_units = $newAvailableUnits;
                            $equipment->save();
                            
                            Log::info('Unidades devueltas automáticamente al inventario', [
                                'equipment_id' => $equipment->id,
                                'previous_units' => $equipment->available_units - $loan->units_requested,
                                'returned_units' => $loan->units_requested,
                                'new_total' => $equipment->available_units
                            ]);
                        }
                    }
                    
                    $processed[] = $loan->id;
                    
                } catch (\Exception $e) {
                    Log::error('Error al procesar devolución automática para préstamo #' . $loan->id, [
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            DB::commit();
            
            Log::info('Proceso de devolución automática completado', [
                'loans_processed' => count($processed),
                'loan_ids' => $processed
            ]);
            
            return response()->json([
                'message' => 'Devoluciones automáticas procesadas correctamente',
                'count' => count($processed),
                'loans' => $processed
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en processAutoReturns: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Error al procesar devoluciones automáticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener los detalles de un préstamo específico
     * 
     * @param int $id ID del préstamo
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLoanDetails($id)
    {
        try {
            $loan = EquipmentLoan::with(['equipment', 'user'])
                ->findOrFail($id);
            
            $formattedLoanDate = $loan->loan_date->format('Y-m-d');
            
            $loanDetails = [
                'id' => $loan->id,
                'user' => [
                    'id' => $loan->user->id,
                    'name' => $loan->user->name,
                    'email' => $loan->user->email
                ],
                'equipment' => [
                    'id' => $loan->equipment->id,
                    'type' => $loan->equipment->type,
                    'section' => $loan->equipment->section
                ],
                'section' => $loan->section,
                'grade' => $loan->grade,
                'loan_date' => $formattedLoanDate,
                'start_time' => $loan->start_time,
                'end_time' => $loan->end_time,
                'units_requested' => $loan->units_requested,
                'status' => $loan->status,
                'delivery_date' => $loan->delivery_date,
                'delivery_observations' => $loan->delivery_observations,
                'return_date' => $loan->return_date,
                'return_observations' => $loan->return_observations,
                'auto_return' => $loan->auto_return,
                'period_id' => $loan->period_id,
                'created_at' => $loan->created_at,
                'updated_at' => $loan->updated_at
            ];
            
            return response()->json([
                'success' => true,
                'data' => $loanDetails
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener detalles del préstamo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'No se pudo obtener los detalles del préstamo: ' . $e->getMessage()
            ], 404);
        }
    }

    protected function handleException($e)
    {
        \Log::error('Error en EquipmentController', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }

        throw $e;
    }

    private function calculateMaxOccupiedUnits($loans, $totalUnits)
    {
        // Si no hay préstamos, no hay unidades ocupadas
        if ($loans->isEmpty()) {
            return 0;
        }

        // Crear un array para almacenar unidades ocupadas por cada intervalo de tiempo
        $timeIntervals = [];
        
        foreach ($loans as $loan) {
            $startTime = Carbon::parse($loan->start_time);
            $endTime = Carbon::parse($loan->end_time);
            
            // Convertir a minutos para facilitar el cálculo
            $startMinutes = $startTime->hour * 60 + $startTime->minute;
            $endMinutes = $endTime->hour * 60 + $endTime->minute;
            
            // Registrar las unidades ocupadas para cada minuto en el intervalo
            for ($minute = $startMinutes; $minute < $endMinutes; $minute++) {
                if (!isset($timeIntervals[$minute])) {
                    $timeIntervals[$minute] = 0;
                }
                $timeIntervals[$minute] += $loan->units_requested;
            }
        }
        
        // Encontrar el valor máximo de unidades ocupadas simultáneamente
        $maxOccupiedUnits = !empty($timeIntervals) ? max($timeIntervals) : 0;
        
        return $maxOccupiedUnits;
    }

    /**
     * Calcula el máximo número de unidades ocupadas en un rango de tiempo específico
     * 
     * @param \Illuminate\Support\Collection $loans Lista de préstamos que se solapan con el intervalo
     * @param string $startTime Hora de inicio del intervalo en formato HH:MM
     * @param string $endTime Hora de fin del intervalo en formato HH:MM
     * @return int Número máximo de unidades ocupadas simultáneamente
     */
    private function calculateMaxOccupiedUnitsForTimeRange($loans, $startTime, $endTime)
    {
        // Si no hay préstamos, no hay unidades ocupadas
        if ($loans->isEmpty()) {
            return 0;
        }
        
        // Convertir los tiempos de inicio y fin a minutos para facilitar el cálculo
        $startTimeMinutes = $this->timeToMinutes($startTime);
        $endTimeMinutes = $this->timeToMinutes($endTime);
        
        // Crear un array para almacenar las unidades ocupadas por minuto
        $occupiedByMinute = [];
        
        // Inicializar el array con ceros para todo el rango de tiempo
        for ($minute = $startTimeMinutes; $minute <= $endTimeMinutes; $minute++) {
            $occupiedByMinute[$minute] = 0;
        }
        
        // Agregar las unidades ocupadas por cada préstamo
        foreach ($loans as $loan) {
            $loanStartTime = Carbon::parse($loan->start_time)->format('H:i');
            $loanEndTime = Carbon::parse($loan->end_time)->format('H:i');
            
            $loanStartMinutes = $this->timeToMinutes($loanStartTime);
            $loanEndMinutes = $this->timeToMinutes($loanEndTime);
            
            // Calcular la intersección del préstamo con el rango solicitado
            $overlapStart = max($startTimeMinutes, $loanStartMinutes);
            $overlapEnd = min($endTimeMinutes, $loanEndMinutes);
            
            // Sumar las unidades para cada minuto en el solapamiento
            for ($minute = $overlapStart; $minute <= $overlapEnd; $minute++) {
                if (isset($occupiedByMinute[$minute])) {
                    $occupiedByMinute[$minute] += $loan->units_requested;
                }
            }
        }
        
        // Encontrar el valor máximo de unidades ocupadas en cualquier minuto
        return !empty($occupiedByMinute) ? max($occupiedByMinute) : 0;
    }
    
    /**
     * Convierte una hora en formato HH:MM a minutos totales desde medianoche
     * 
     * @param string $time Hora en formato HH:MM
     * @return int Minutos totales desde medianoche
     */
    private function timeToMinutes($time)
    {
        list($hours, $minutes) = explode(':', $time);
        return (int)$hours * 60 + (int)$minutes;
    }
}
