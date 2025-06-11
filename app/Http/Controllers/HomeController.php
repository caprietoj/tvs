<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Announcement;
use App\Models\SchoolCycle; // Importar el modelo SchoolCycle
use Illuminate\Http\Request;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $announcements = Announcement::query()
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereDate('expiry_date', '>=', now())
                      ->orWhereNull('expiry_date');
            })
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Obtener el ciclo escolar activo
        $activeCycle = SchoolCycle::where('active', true)->first();
        
        // Obtener el día actual del ciclo si existe un ciclo activo
        $currentCycleDay = null;
        if ($activeCycle) {
            $today = Carbon::today()->format('Y-m-d');
            $currentCycleDay = $activeCycle->cycleDays()
                ->where('date', $today)
                ->first();
        }

        return view('welcome', compact('announcements', 'activeCycle', 'currentCycleDay'));
    }

    public function dashboard()
    {
        $totalTickets = Ticket::count();
        $abiertos = Ticket::where('estado', 'Abierto')->count();
        $enProceso = Ticket::where('estado', 'En Proceso')->count();
        $cerrados = Ticket::where('estado', 'Cerrado')->count();

        $baja = Ticket::where('prioridad', 'Baja')->count();
        $media = Ticket::where('prioridad', 'Media')->count();
        $alta = Ticket::where('prioridad', 'Alta')->count();

        // Obtener los últimos 10 tickets
        $recentTickets = Ticket::latest()->take(10)->get();

        return view('dashboard', compact(
            'totalTickets', 
            'abiertos', 
            'enProceso', 
            'cerrados',
            'baja',
            'media',
            'alta',
            'recentTickets'
        ));
    }
}