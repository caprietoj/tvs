<?php

namespace App\Http\Controllers;

use App\Models\SalidaPedagogica;
use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\SalidaPedagogicaNotification;
use App\Http\Controllers\ConfigurationController;

class SalidaPedagogicaController extends Controller
{
    public function index()
    {
        // Verificar permisos de visualización para profesores
        if (auth()->user()->hasRole('profesor') && !auth()->user()->can('view.salidas')) {
            return redirect()->route('home')
                ->with('error', 'No tienes permisos para acceder a esta sección.');
        }

        $salidas = SalidaPedagogica::with('responsable')->orderBy('fecha_salida', 'desc')->get();
        return view('salidas.index', compact('salidas'));
    }

    public function create()
    {
        // Verificar permisos - profesores no pueden crear salidas
        if (auth()->user()->hasRole('profesor')) {
            return redirect()->route('salidas.index')
                ->with('error', 'No tienes permisos para crear salidas pedagógicas.');
        }

        $responsables = User::all();
        $currentDate = Carbon::now()->format('Y-m-d');
        
        // Generar consecutivo
        $lastId = SalidaPedagogica::withTrashed()->max('id') ?? 0;
        $consecutivo = 'S-' . str_pad($lastId + 1, 3, '0', STR_PAD_LEFT);
        
        return view('salidas.create', compact('responsables', 'currentDate', 'consecutivo'));
    }

    public function store(Request $request)
    {
        // Verificar permisos - profesores no pueden crear salidas
        if (auth()->user()->hasRole('profesor')) {
            return redirect()->route('salidas.index')
                ->with('error', 'No tienes permisos para crear salidas pedagógicas.');
        }

        \Log::info('Starting store method');
        \Log::info('Request data:', $request->all());

        try {
            // Validate request data
            $validated = $request->validate([
                'grados' => 'required|string',
                'lugar' => 'required|string',
                'responsable_id' => 'required|exists:users,id',
                'fecha_salida' => 'required|date',
                'hora_salida' => 'required',
                'fecha_regreso' => 'required|date|after_or_equal:fecha_salida',
                'hora_regreso' => 'required',
                'cantidad_pasajeros' => 'required|integer|min:1',
            ]);

            // Generate consecutive number
            $lastId = SalidaPedagogica::withTrashed()->max('id') ?? 0;
            $consecutivo = 'S-' . str_pad($lastId + 1, 3, '0', STR_PAD_LEFT);

            // Prepare data with proper date formatting
            $data = [
                'consecutivo' => $consecutivo,
                'fecha_solicitud' => now(),
                'grados' => $request->grados,
                'lugar' => $request->lugar,
                'responsable_id' => $request->responsable_id,
                'fecha_salida' => Carbon::parse($request->fecha_salida . ' ' . $request->hora_salida),
                'fecha_regreso' => Carbon::parse($request->fecha_regreso . ' ' . $request->hora_regreso),
                'cantidad_pasajeros' => $request->cantidad_pasajeros,
                'calendario_general' => $request->boolean('calendario_general'),
                'visita_inspeccion' => $request->boolean('visita_inspeccion'),
                'detalles_inspeccion' => $request->detalles_inspeccion,
                'contacto_lugar' => $request->contacto_lugar,
                'observaciones' => $request->observaciones,
                'transporte_confirmado' => $request->boolean('transporte_confirmado'),
                'hora_salida_bus' => $request->hora_salida_bus,
                'hora_regreso_bus' => $request->hora_regreso_bus,
                'requiere_alimentacion' => $request->boolean('requiere_alimentacion'),
                'cantidad_snacks' => $request->cantidad_snacks,
                'cantidad_almuerzos' => $request->cantidad_almuerzos,
                'hora_entrega_alimentos' => $request->hora_entrega_alimentos,
                'menu_sugerido' => $request->menu_sugerido,
                'hora_apertura_puertas' => $request->hora_apertura_puertas,
                'requiere_enfermeria' => $request->boolean('requiere_enfermeria'),
                'requiere_comunicaciones' => $request->boolean('requiere_comunicaciones'),
                'requiere_arl' => $request->boolean('requiere_arl'),
                'observaciones_comunicaciones' => $request->observaciones_comunicaciones,
                'estado' => 'Programada'
            ];

            \Log::info('Prepared data:', $data);

            $salida = SalidaPedagogica::create($data);
            \Log::info('Salida created:', ['id' => $salida->id]);

            if ($request->boolean('calendario_general')) {
                $event = Event::create([
                    'title' => 'Salida Pedagógica: ' . $request->grados . ' - ' . $request->lugar,
                    'description' => "Responsable: " . User::find($request->responsable_id)->name . "\n" .
                                   "Cantidad de pasajeros: " . $request->cantidad_pasajeros . "\n" .
                                   "Observaciones: " . $request->observaciones,
                    'start' => Carbon::parse($request->fecha_salida . ' ' . $request->hora_salida),
                    'end' => Carbon::parse($request->fecha_regreso . ' ' . $request->hora_regreso),
                    'color' => '#364E76',
                    'salida_pedagogica_id' => $salida->id
                ]);
                \Log::info('Evento creado:', ['id' => $event->id]);
            }

            // Enviar notificaciones por correo
            $this->sendNotifications($salida);

            return redirect()
                ->route('salidas.show', $salida)
                ->with('success', 'Salida pedagógica creada exitosamente');

        } catch (\Exception $e) {
            \Log::error('Error creating salida:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Error al crear la salida pedagógica: ' . $e->getMessage());
        }
    }

    protected function sendNotifications($salida)
    {
        try {
            \Log::info('Iniciando envío de notificaciones');
            
            // Generar token único para cada notificación
            $token = md5(uniqid($salida->id, true));

            // Cargar la relación del responsable
            $salida->load('responsable');

            // Enviar al responsable
            if ($salida->responsable && $salida->responsable->email) {
                Mail::send('emails.salida-pedagogica', [
                    'salida' => $salida,
                    'tipoDestinatario' => 'responsable',
                    'token' => $token
                ], function($message) use ($salida) {
                    $message->to($salida->responsable->email)
                            ->subject('Nueva Salida Pedagógica - ' . $salida->grados);
                });
            }

            // Metro Juniors (Transporte)
            if ($salida->transporte_confirmado) {
                Mail::send('emails.salida-pedagogica', [
                    'salida' => $salida,
                    'tipoDestinatario' => 'transporte',
                    'token' => $token
                ], function($message) use ($salida) {
                    $message->to('transporte@tvs.edu.co')
                            ->subject('Nueva Salida Pedagógica - Transporte - ' . $salida->grados);
                });
            }

            // Aldimark (Alimentación)
            if ($salida->requiere_alimentacion) {
                Mail::send('emails.salida-pedagogica', [
                    'salida' => $salida,
                    'tipoDestinatario' => 'alimentacion',
                    'token' => $token
                ], function($message) use ($salida) {
                    $message->to('cafeteria@tvs.edu.co')
                            ->subject('Nueva Salida Pedagógica - Alimentación - ' . $salida->grados);
                });
            }

            // Enfermería
            if ($salida->requiere_enfermeria) {
                Mail::send('emails.salida-pedagogica', [
                    'salida' => $salida,
                    'tipoDestinatario' => 'enfermeria',
                    'token' => $token
                ], function($message) use ($salida) {
                    $message->to('enfermeria@tvs.edu.co')
                            ->subject('Nueva Salida Pedagógica - Enfermería - ' . $salida->grados);
                });
            }

            // Comunicaciones
            if ($salida->requiere_comunicaciones) {
                Mail::send('emails.salida-pedagogica', [
                    'salida' => $salida,
                    'tipoDestinatario' => 'comunicaciones',
                    'token' => $token
                ], function($message) use ($salida) {
                    $message->to('comunicaciones@tvs.edu.co')
                            ->subject('Nueva Salida Pedagógica - Comunicaciones - ' . $salida->grados);
                });
            }

            // ARL - Gestión Humana
            if ($salida->requiere_arl) {
                Mail::send('emails.salida-pedagogica', [
                    'salida' => $salida,
                    'tipoDestinatario' => 'arl',
                    'token' => $token
                ], function($message) use ($salida) {
                    $message->to('recursoshumanos@tvs.edu.co')
                            ->subject('Nueva Salida Pedagógica - Reporte ARL - ' . $salida->grados);
                });
                
                \Log::info('Notificación enviada a Gestión Humana para reporte ARL');
            }

            \Log::info('Notificaciones enviadas exitosamente');
        } catch (\Exception $e) {
            \Log::error('Error enviando notificaciones: ' . $e->getMessage());
        }
    }

    public function confirmarArea($id, $area, $token)
    {
        try {
            $salida = SalidaPedagogica::findOrFail($id);
            
            switch ($area) {
                case 'transporte':
                    $salida->transporte_confirmado = true;
                    break;
                case 'alimentacion':
                    $salida->alimentacion_confirmada = true;
                    break;
                case 'enfermeria':
                    $salida->enfermeria_confirmada = true;
                    break;
                case 'comunicaciones':
                    $salida->comunicaciones_confirmada = true;
                    break;
            }
            
            $salida->save();
            
            return view('salidas.confirmacion-popup', [
                'area' => $area,
                'salida' => $salida,
                'success' => true
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en confirmación de área: ' . $e->getMessage());
            return view('salidas.confirmacion-popup', [
                'success' => false,
                'error' => 'No se pudo procesar la confirmación'
            ]);
        }
    }

    public function show(SalidaPedagogica $salida)
    {
        $salida->load('responsable');
        return view('salidas.show', compact('salida'));
    }

    public function edit(SalidaPedagogica $salida)
    {
        // Verificar permisos - profesores no pueden editar salidas
        if (auth()->user()->hasRole('profesor')) {
            return redirect()->route('salidas.show', $salida)
                ->with('error', 'No tienes permisos para editar salidas pedagógicas.');
        }

        $responsables = User::all();
        return view('salidas.edit', compact('salida', 'responsables'));
    }

    public function update(Request $request, SalidaPedagogica $salida)
    {
        // Verificar permisos - profesores no pueden editar salidas
        if (auth()->user()->hasRole('profesor')) {
            return redirect()->route('salidas.show', $salida)
                ->with('error', 'No tienes permisos para editar salidas pedagógicas.');
        }

        $validated = $request->validate([
            'grados' => 'required|string',
            'lugar' => 'required|string',
            'responsable_id' => 'required|exists:users,id',
            'fecha_salida' => 'required|date',
            'hora_salida' => 'required',
            'fecha_regreso' => 'required|date|after_or_equal:fecha_salida',
            'hora_regreso' => 'required',
            'cantidad_pasajeros' => 'required|integer|min:1',
            'calendario_general' => 'nullable|boolean',
            'visita_inspeccion' => 'nullable|boolean',
            'detalles_inspeccion' => 'nullable|string',
            'contacto_lugar' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'requiere_alimentacion' => 'nullable|boolean',
            'cantidad_snacks' => 'nullable|integer|min:0',
            'cantidad_almuerzos' => 'nullable|integer|min:0',
            'menu_sugerido' => 'nullable|string',
            'hora_apertura_puertas' => 'nullable',
            'requiere_enfermeria' => 'nullable|boolean',
            'requiere_comunicaciones' => 'nullable|boolean',
            'requiere_arl' => 'nullable|boolean',
            'observaciones_comunicaciones' => 'nullable|string'
        ]);

        try {
            $data = [
                'grados' => $request->grados,
                'lugar' => $request->lugar,
                'responsable_id' => $request->responsable_id,
                'fecha_salida' => Carbon::parse($request->fecha_salida . ' ' . $request->hora_salida)->format('Y-m-d H:i:s'),
                'fecha_regreso' => Carbon::parse($request->fecha_regreso . ' ' . $request->hora_regreso)->format('Y-m-d H:i:s'),
                'cantidad_pasajeros' => $request->cantidad_pasajeros,
                'calendario_general' => $request->has('calendario_general') ? true : false,
                'visita_inspeccion' => $request->has('visita_inspeccion') ? true : false,
                'detalles_inspeccion' => $request->detalles_inspeccion,
                'contacto_lugar' => $request->contacto_lugar,
                'observaciones' => $request->observaciones,
                'requiere_alimentacion' => $request->has('requiere_alimentacion') ? true : false,
                'cantidad_snacks' => $request->cantidad_snacks,
                'cantidad_almuerzos' => $request->cantidad_almuerzos,
                'menu_sugerido' => $request->menu_sugerido,
                'hora_apertura_puertas' => $request->hora_apertura_puertas,
                'requiere_enfermeria' => $request->has('requiere_enfermeria') ? true : false,
                'requiere_comunicaciones' => $request->has('requiere_comunicaciones') ? true : false,
                'requiere_arl' => $request->has('requiere_arl') ? true : false,
                'observaciones_comunicaciones' => $request->observaciones_comunicaciones
            ];

            $salida->update($data);

            // Update or create calendar event
            if ($request->has('calendario_general')) {
                Event::updateOrCreate(
                    ['salida_pedagogica_id' => $salida->id],
                    [
                        'title' => 'Salida Pedagógica: ' . $request->grados . ' - ' . $request->lugar,
                        'description' => "Responsable: " . User::find($request->responsable_id)->name . "\n" .
                                       "Cantidad de pasajeros: " . $request->cantidad_pasajeros . "\n" .
                                       "Observaciones: " . $request->observaciones,
                        'start' => Carbon::parse($request->fecha_salida . ' ' . $request->hora_salida),
                        'end' => Carbon::parse($request->fecha_regreso . ' ' . $request->hora_regreso),
                        'color' => '#364E76'
                    ]
                );
            } else {
                // Remove calendar event if calendario_general is unchecked
                Event::where('salida_pedagogica_id', $salida->id)->delete();
            }

            return redirect()
                ->route('salidas.show', $salida)
                ->with('success', 'Salida pedagógica actualizada exitosamente');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar la salida pedagógica: ' . $e->getMessage());
        }
    }

    public function destroy(SalidaPedagogica $salida)
    {
        // Verificar permisos - profesores no pueden eliminar salidas
        if (auth()->user()->hasRole('profesor')) {
            return redirect()->route('salidas.index')
                ->with('error', 'No tienes permisos para eliminar salidas pedagógicas.');
        }

        try {
            // Delete associated calendar event
            Event::where('salida_pedagogica_id', $salida->id)->delete();
            
            $salida->delete();
            return redirect()
                ->route('salidas.index')
                ->with('success', 'Salida pedagógica eliminada exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar la salida pedagógica');
        }
    }

    public function reports()
    {
        $salidas = SalidaPedagogica::with('responsable')->get();
        return view('salidas.reports', compact('salidas'));
    }
}
