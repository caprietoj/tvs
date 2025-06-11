<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventNovelty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventNoveltyController extends Controller
{
    /**
     * Mostrar el listado de novedades para un evento específico.
     */
    public function index(Event $event)
    {
        $novelties = $event->novelties()->with('user')->orderBy('created_at', 'desc')->get();
        return view('events.novelties.index', compact('event', 'novelties'));
    }

    /**
     * Mostrar el formulario para crear una nueva novedad.
     */
    public function create(Event $event)
    {
        return view('events.novelties.create', compact('event'));
    }

    /**
     * Almacenar una nueva novedad en la base de datos.
     */
    public function store(Request $request, Event $event)
    {
        $request->validate([
            'observation' => 'required|string|max:1000'
        ]);

        $novelty = new EventNovelty([
            'event_id' => $event->id,
            'user_id' => Auth::id(),
            'observation' => $request->observation
        ]);

        $novelty->save();

        return redirect()->route('event.novelties.index', $event)
            ->with('swal', [
                'icon' => 'success',
                'title' => '¡Novedad registrada!',
                'text' => 'La novedad ha sido registrada exitosamente.',
            ]);
    }

    /**
     * Mostrar el detalle de una novedad específica.
     */
    public function show(Event $event, EventNovelty $novelty)
    {
        return view('events.novelties.show', compact('event', 'novelty'));
    }

    /**
     * Mostrar el formulario para editar una novedad.
     */
    public function edit(Event $event, EventNovelty $novelty)
    {
        // Solo permitir editar a usuarios con rol 'modificacion-novedad' o admin
        if (!Auth::user()->hasAnyRole(['admin', 'Admin', 'modificacion-novedad'])) {
            return redirect()->route('event.novelties.index', $event)
                ->with('error', 'No tienes permisos para editar novedades.');
        }

        return view('events.novelties.edit', compact('event', 'novelty'));
    }

    /**
     * Actualizar una novedad específica en la base de datos.
     */
    public function update(Request $request, Event $event, EventNovelty $novelty)
    {
        // Solo permitir actualizar a usuarios con rol 'modificacion-novedad' o admin
        if (!Auth::user()->hasAnyRole(['admin', 'Admin', 'modificacion-novedad'])) {
            return redirect()->route('event.novelties.index', $event)
                ->with('error', 'No tienes permisos para editar novedades.');
        }

        $request->validate([
            'observation' => 'required|string|max:1000'
        ]);

        $novelty->update([
            'observation' => $request->observation
        ]);

        return redirect()->route('event.novelties.index', $event)
            ->with('swal', [
                'icon' => 'success',
                'title' => '¡Actualizado!',
                'text' => 'La novedad ha sido actualizada exitosamente.',
            ]);
    }

    /**
     * Eliminar una novedad específica.
     */
    public function destroy(Event $event, EventNovelty $novelty)
    {
        // Solo permitir eliminar a usuarios con rol 'modificacion-novedad' o admin
        if (!Auth::user()->hasAnyRole(['admin', 'Admin', 'modificacion-novedad'])) {
            return redirect()->route('event.novelties.index', $event)
                ->with('error', 'No tienes permisos para eliminar novedades.');
        }

        $novelty->delete();

        return redirect()->route('event.novelties.index', $event)
            ->with('swal', [
                'icon' => 'success',
                'title' => '¡Eliminado!',
                'text' => 'La novedad ha sido eliminada exitosamente.',
            ]);
    }
}