<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\User;
use App\Models\Configuration;  // Agregar esta línea
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\MaintenanceRequestCreated;
use Carbon\Carbon;

class MaintenanceRequestController extends Controller
{
    private $requestTypes = [
        'mantenimiento_preventivo' => 'Mantenimiento Preventivo',
        'mantenimiento_correctivo' => 'Mantenimiento Correctivo',
        // 'instalaciones' => 'Instalaciones',
        // 'modificacion' => 'Modificación',
        'Instalacion_de_persianas' => 'Instalación de Persianas',
        'plomeria' => 'Plomería',
        'electricidad' => 'Electricidad',
        // 'adecuaciones' => 'Adecuaciones',
        'goteras' => 'Goteras',
        'pintura' => 'Pintura',
        'carpinteria' => 'Carpintería',
        'cerrajeria' => 'Cerrajería',
        'vidrios' => 'Vidrios',
        'jardineria' => 'Jardinería',
        'cambio_de_bombillos' => 'Cambio de Bombillos',
        'demarcacion_de_canchas' => 'Demarcación de Canchas',
        'traslado_de_mobiliario' => 'Traslado de Mobiliario',
        'limpieza_de_tanques_de_agua' => 'Limpieza de Tanques de Agua',
        'otros' => 'Otros'
    ];

    public function index()
    {
        $user = auth()->user();
        $requests = MaintenanceRequest::when(!$user->hasAnyRole(['admin', 'mantenimiento']), function($query) use ($user) {
            return $query->where('user_id', $user->id);
        })->with('user')->latest()->paginate(10);
        
        return view('maintenance.index', compact('requests'));
    }

    public function create()
    {
        return view('maintenance.create', [
            'request_types' => $this->requestTypes
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_type' => 'required',
            'location' => 'required',
            'description' => 'required'
        ]);

        // Calculate priority based on request type and description
        $priority = $this->calculatePriority($request->request_type, $request->description);

        $maintenanceRequest = MaintenanceRequest::create([
            'user_id' => auth()->id(),
            'request_type' => strval($validated['request_type']), // Convertir explícitamente a string
            'location' => $validated['location'],
            'description' => $validated['description'],
            'priority' => $priority,
            'status' => 'pending'
        ]);

        try {
            // Obtener correos configurados
            $config = Configuration::where('key', 'maintenance_emails')->first();
            $notificationEmails = $config ? explode(',', $config->value) : [];
            
            // Enviar correos a todos los destinatarios configurados
            foreach ($notificationEmails as $email) {
                Mail::to(trim($email))->send(
                    new MaintenanceRequestCreated($maintenanceRequest)
                );
            }

            // Enviar correo al usuario que creó la solicitud
            Mail::to(auth()->user()->email)->send(
                new MaintenanceRequestCreated($maintenanceRequest)
            );

        } catch (\Exception $e) {
            \Log::error('Error enviando correos de mantenimiento: ' . $e->getMessage());
            // No detenemos el proceso si falla el envío de correos
        }
        
        return redirect()->route('maintenance.index')
            ->with('success', 'La solicitud de mantenimiento ha sido creada exitosamente');
    }

    public function update(Request $request, MaintenanceRequest $maintenance)
    {
        $validated = $request->validate([
            'request_type' => 'required',
            'location' => 'required',
            'description' => 'required',
            'status' => 'required_if:user_role,admin',
            'technician_id' => 'nullable|exists:users,id'
        ]);

        $maintenance->update($validated);

        return redirect()->route('maintenance.index')
            ->with('success', 'Solicitud actualizada exitosamente');
    }

    public function updateStatus(MaintenanceRequest $maintenance, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,rejected'
        ]);

        $maintenance->update($validated);

        return redirect()->back()
            ->with('success', 'Estado actualizado exitosamente');
    }

    public function dashboard(Request $request)
    {
        $dateRange = $request->get('date_range', 'month');
        $startDate = $this->getStartDate($dateRange);

        // Totales para las tarjetas
        $totalRequests = MaintenanceRequest::where('created_at', '>=', $startDate)->count();
        $pendingRequests = MaintenanceRequest::where('status', 'pending')
            ->where('created_at', '>=', $startDate)->count();
        $completedRequests = MaintenanceRequest::where('status', 'completed')
            ->where('created_at', '>=', $startDate)->count();
        $averageCompletionTime = MaintenanceRequest::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('completion_date')
            ->avg(DB::raw('TIMESTAMPDIFF(HOUR, created_at, completion_date)'));

        // Estadísticas por tipo
        $requestsByType = MaintenanceRequest::select('request_type', DB::raw('count(*) as total'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('request_type')
            ->get();

        // Estadísticas por estado
        $requestsByStatus = MaintenanceRequest::select('status', DB::raw('count(*) as total'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('status')
            ->get();

        // Estadísticas por prioridad
        $requestsByPriority = MaintenanceRequest::select('priority', DB::raw('count(*) as total'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('priority')
            ->get();

        // Solicitudes por mes
        $requestsByMonth = MaintenanceRequest::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('count(*) as total')
        )
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Tiempo promedio de resolución por tipo
        $avgTimeByType = MaintenanceRequest::select(
            'request_type',
            DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, completion_date)) as avg_time')
        )
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->groupBy('request_type')
            ->get();

        // Solicitudes recientes con paginación
        $recentRequests = MaintenanceRequest::with('user')
            ->where('created_at', '>=', $startDate)
            ->latest()
            ->paginate(10);

        return view('maintenance.dashboard', compact(
            'totalRequests',
            'pendingRequests',
            'completedRequests',
            'averageCompletionTime',
            'requestsByType',
            'requestsByStatus',
            'requestsByPriority',
            'requestsByMonth',
            'avgTimeByType',
            'recentRequests',
            'dateRange'
        ));
    }

    public function show(MaintenanceRequest $maintenance)
    {
        $technicians = User::role('technician')->get();
        return view('maintenance.show', compact('maintenance', 'technicians') + ['request_types' => $this->requestTypes]);
    }

    public function edit(MaintenanceRequest $maintenance)
    {
        $technicians = User::role('technician')->get();
        
        return view('maintenance.edit', compact('maintenance', 'technicians') + ['request_types' => $this->requestTypes]);
    }

    public function assignTechnician(Request $request, MaintenanceRequest $maintenance)
    {
        $validated = $request->validate([
            'technician_id' => 'required|exists:users,id'
        ]);

        $maintenance->update([
            'technician_id' => $validated['technician_id'],
            'status' => 'in_progress'
        ]);

        return redirect()->route('maintenance.show', $maintenance)->with('success', 'Técnico asignado exitosamente');
    }

    public function destroy(MaintenanceRequest $maintenance)
    {
        $maintenance->delete();
        
        return redirect()->route('maintenance.index')
            ->with('success', 'La solicitud de mantenimiento ha sido eliminada exitosamente');
    }

    private function calculatePriority($requestType, $description)
    {
        // Keywords that might indicate high priority
        $highPriorityKeywords = ['urgente', 'emergencia', 'peligro', 'riesgo', 'inmediato'];
        
        // Request types that are typically high priority
        $highPriorityTypes = ['electricidad', 'plomeria', 'goteras'];

        if (in_array($requestType, $highPriorityTypes)) {
            return 'high';
        }

        foreach ($highPriorityKeywords as $keyword) {
            if (str_contains(strtolower($description), $keyword)) {
                return 'high';
            }
        }

        return 'medium';
    }

    private function getStartDate($range)
    {
        return match($range) {
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            'quarter' => Carbon::now()->subQuarter(),
            'year' => Carbon::now()->subYear(),
            default => Carbon::now()->subMonth(),
        };
    }
}
