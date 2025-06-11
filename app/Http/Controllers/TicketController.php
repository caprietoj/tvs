<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TicketController extends Controller
{
    /**
     * Muestra el listado de tickets.
     */
    public function index(Request $request)
    {
        // Los administradores ven todos los tickets; los demás solo sus tickets.
        if (auth()->user()->hasRole('admin')) {
            $tickets = Ticket::with('user', 'tecnico')->get();
        } else {
            $tickets = Ticket::with('user', 'tecnico')
                ->where('user_id', auth()->id())
                ->get();
        }

        if ($request->ajax()) {
            return response()->json(['data' => $tickets]);
        }

        return view('tickets.index', compact('tickets'));
    }

    /**
     * Muestra el formulario para crear un ticket.
     * En esta acción NO se asigna técnico.
     */
    public function create()
    {
        return view('tickets.create');
    }

    /**
     * Almacena un nuevo ticket.
     * Se omite la asignación de técnico en este proceso.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'tipo_requerimiento' => 'required|string|in:Hardware,Software,Mantenimiento,Instalación,Conectividad',
        ]);

        $data['estado'] = 'Abierto';
        $data['user_id'] = auth()->id();
        $data['prioridad'] = $this->calculatePriority($data['tipo_requerimiento'], $data['descripcion']);
        
        $ticket = Ticket::create($data);

        if ($ticket->assigned_to) {
            Mail::to($ticket->assignedUser->email)
                ->send(new \App\Mail\TicketCreated($ticket));
        }

        return response()->json([
            'message' => 'Ticket creado exitosamente',
            'ticket' => $ticket
        ], 201);
    }

    private function calculatePriority($requestType, $description)
    {
        // Keywords that indicate high priority
        $highPriorityKeywords = [
            'urgente', 'emergencia', 'crítico', 'grave', 'inmediato',
            'no funciona', 'error', 'daño', 'dañado', 'bloqueado'
        ];

        // Request types that are typically high priority
        $highPriorityTypes = ['Hardware', 'Conectividad'];

        // Check request type first
        if (in_array($requestType, $highPriorityTypes)) {
            return 'Alta';
        }

        // Check for high priority keywords in description
        $description = strtolower($description);
        foreach ($highPriorityKeywords as $keyword) {
            if (str_contains($description, $keyword)) {
                return 'Alta';
            }
        }

        // Default to Media if no high priority conditions are met
        return 'Media';
    }

    /**
     * Muestra el detalle de un ticket.
     */
    public function show($id)
    {
        $ticket = Ticket::with('user', 'tecnico')->findOrFail($id);
        return view('tickets.show', compact('ticket'));
    }

    /**
     * Muestra el formulario para editar un ticket.
     * Aquí se permite asignar el técnico.
     */
    public function edit($id)
    {
        $ticket = Ticket::findOrFail($id);
        // Se consultan los usuarios cuyo cargo sea "Tecnico" o "Auxiliar"
        $tecnicos = User::whereIn('cargo', ['Tecnico', 'Auxiliar'])->get();
        return view('tickets.edit', compact('ticket', 'tecnicos'));
    }

    /**
     * Actualiza un ticket existente.
     * Permite asignar (o cambiar) el técnico al ticket.
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'titulo'             => 'required|string|max:255',
            'descripcion'        => 'required|string',
            'estado'             => 'required|in:Abierto,En Proceso,Cerrado',
            'prioridad'          => 'required|string|in:Baja,Media,Alta',
            'tipo_requerimiento' => 'required|string|in:Hardware,Software,Mantenimiento,Instalación,Conectividad',
            'tecnico_id'         => 'nullable|exists:users,id',
        ]);

        if (!empty($data['tecnico_id'])) {
            $tecnico = User::find($data['tecnico_id']);
            if (!in_array($tecnico->cargo, ['Tecnico', 'Auxiliar'])) {
                return response()->json([
                    'errors' => ['tecnico_id' => ['El usuario seleccionado no es un técnico o auxiliar válido.']]
                ], 422);
            }
        }

        $ticket = Ticket::findOrFail($id);
        $ticket->update($data);

        return response()->json([
            'message' => 'Ticket actualizado exitosamente',
            'ticket'  => $ticket
        ], 200);
    }

    /**
     * Elimina un ticket.
     */
    public function destroy($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->delete();

        return response()->json([
            'message' => 'Ticket eliminado exitosamente'
        ], 200);
    }
}