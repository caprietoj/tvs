<?php

namespace App\Http\Controllers;

use App\Models\SpaceBlock;
use App\Models\SpaceBlockException;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpaceBlockExceptionController extends Controller
{
    /**
     * Mostrar lista de excepciones de bloqueos.
     */
    public function index()
    {
        // Verificar que el usuario tenga permisos de administrador
        if (!Auth::user()->hasRole('admin')) {
            return redirect()->route('home')
                ->with('error', 'No tiene permisos para acceder a esta sección.');
        }

        $exceptions = SpaceBlockException::with(['spaceBlock.space', 'creator'])
            ->orderBy('exception_date', 'desc')
            ->paginate(15);

        return view('space_block_exceptions.index', compact('exceptions'));
    }

    /**
     * Mostrar el formulario para crear una nueva excepción.
     */
    public function create()
    {
        // Verificar que el usuario tenga permisos de administrador
        if (!Auth::user()->hasRole('admin')) {
            return redirect()->route('home')
                ->with('error', 'No tiene permisos para acceder a esta sección.');
        }

        // Obtener solo los bloqueos semanales
        $weeklyBlocks = SpaceBlock::where('is_weekday_block', true)
            ->with('space')
            ->orderBy('space_id')
            ->get();

        return view('space_block_exceptions.create', compact('weeklyBlocks'));
    }

    /**
     * Almacenar una nueva excepción.
     */
    public function store(Request $request)
    {
        // Verificar que el usuario tenga permisos de administrador
        if (!Auth::user()->hasRole('admin')) {
            return redirect()->route('home')
                ->with('error', 'No tiene permisos para acceder a esta sección.');
        }

        $validated = $request->validate([
            'space_block_id' => 'required|exists:space_blocks,id',
            'exception_date' => 'required|date|after_or_equal:today',
            'reason' => 'nullable|string|max:255',
        ]);

        // Verificar que el bloqueo seleccionado sea un bloqueo semanal
        $spaceBlock = SpaceBlock::findOrFail($validated['space_block_id']);
        if (!$spaceBlock->is_weekday_block) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['space_block_id' => 'Solo se pueden crear excepciones para bloqueos semanales.']);
        }

        // Verificar que la fecha seleccionada corresponda al día de la semana del bloqueo
        $exceptionDate = Carbon::parse($validated['exception_date']);
        $dayOfWeek = strtolower($exceptionDate->format('l')); // obtener el día de la semana en inglés y en minúsculas

        if (!$spaceBlock->$dayOfWeek) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['exception_date' => 'La fecha seleccionada debe corresponder a un día de la semana que está bloqueado.']);
        }

        // Verificar que no exista ya una excepción para este bloqueo y fecha
        $existingException = SpaceBlockException::where('space_block_id', $validated['space_block_id'])
            ->where('exception_date', $validated['exception_date'])
            ->first();

        if ($existingException) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['exception_date' => 'Ya existe una excepción para este bloqueo en la fecha seleccionada.']);
        }

        // Registrar el usuario que crea la excepción
        $validated['created_by'] = Auth::id();

        // Crear la excepción
        $exception = SpaceBlockException::create($validated);

        return redirect()->route('space-block-exceptions.index')
            ->with('success', 'Excepción creada exitosamente para el ' . $exceptionDate->format('d/m/Y') . '.');
    }

    /**
     * Mostrar los detalles de una excepción específica.
     */
    public function show(SpaceBlockException $spaceBlockException)
    {
        // Verificar que el usuario tenga permisos de administrador
        if (!Auth::user()->hasRole('admin')) {
            return redirect()->route('home')
                ->with('error', 'No tiene permisos para acceder a esta sección.');
        }

        return view('space_block_exceptions.show', ['exception' => $spaceBlockException]);
    }

    /**
     * Mostrar el formulario para editar una excepción existente.
     */
    public function edit(SpaceBlockException $spaceBlockException)
    {
        // Verificar que el usuario tenga permisos de administrador
        if (!Auth::user()->hasRole('admin')) {
            return redirect()->route('home')
                ->with('error', 'No tiene permisos para acceder a esta sección.');
        }

        return view('space_block_exceptions.edit', ['exception' => $spaceBlockException]);
    }

    /**
     * Actualizar una excepción existente.
     */
    public function update(Request $request, SpaceBlockException $spaceBlockException)
    {
        // Verificar que el usuario tenga permisos de administrador
        if (!Auth::user()->hasRole('admin')) {
            return redirect()->route('home')
                ->with('error', 'No tiene permisos para acceder a esta sección.');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        // Actualizar la excepción
        $spaceBlockException->update($validated);

        return redirect()->route('space-block-exceptions.index')
            ->with('success', 'Excepción actualizada exitosamente.');
    }

    /**
     * Eliminar una excepción existente.
     */
    public function destroy(SpaceBlockException $spaceBlockException)
    {
        // Verificar que el usuario tenga permisos de administrador
        if (!Auth::user()->hasRole('admin')) {
            return redirect()->route('home')
                ->with('error', 'No tiene permisos para acceder a esta sección.');
        }

        // Solo eliminar excepciones para fechas futuras
        if (Carbon::parse($spaceBlockException->exception_date)->lt(Carbon::today())) {
            return redirect()->route('space-block-exceptions.index')
                ->with('error', 'No se pueden eliminar excepciones para fechas pasadas.');
        }

        $spaceBlockException->delete();

        return redirect()->route('space-block-exceptions.index')
            ->with('success', 'Excepción eliminada exitosamente.');
    }

    /**
     * Crear una nueva excepción directamente desde la vista de disponibilidad.
     * Esta función permite crear excepciones rápidamente desde la pantalla de reserva.
     */
    public function quickCreate(Request $request)
    {
        try {
            // Verificar que el usuario tenga permisos de administrador
            if (!Auth::user()->hasRole('admin')) {
                return response()->json(['error' => 'No tiene permisos para realizar esta acción.'], 403);
            }

            $validated = $request->validate([
                'space_block_id' => 'required|exists:space_blocks,id',
                'exception_date' => 'required|date|after_or_equal:today',
                'reason' => 'nullable|string|max:255',
            ]);

            // Verificar que el bloqueo exista
            $spaceBlock = SpaceBlock::findOrFail($validated['space_block_id']);
            
            // Verificar que el bloqueo sea semanal
            if (!$spaceBlock->is_weekday_block) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden crear excepciones para bloqueos semanales.'
                ], 422);
            }

            // Verificar que la fecha corresponda al día de la semana del bloqueo
            $exceptionDate = Carbon::parse($validated['exception_date']);
            $dayOfWeek = strtolower($exceptionDate->format('l'));
            
            if (!$spaceBlock->$dayOfWeek) {
                return response()->json([
                    'success' => false,
                    'message' => 'La fecha seleccionada debe corresponder a un día de la semana que está bloqueado.'
                ], 422);
            }

            // Verificar que no exista ya una excepción
            $existingException = SpaceBlockException::where('space_block_id', $validated['space_block_id'])
                ->where('exception_date', $validated['exception_date'])
                ->first();

            if ($existingException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una excepción para este bloqueo en la fecha seleccionada.'
                ], 422);
            }

            // Registrar el usuario que crea la excepción
            $validated['created_by'] = Auth::id();

            // Crear la excepción
            $exception = SpaceBlockException::create($validated);

            // Verificar que la excepción se haya creado correctamente
            if (!$exception) {
                throw new \Exception('No se pudo crear la excepción en la base de datos.');
            }

            // Obtener información adicional para la respuesta
            $spaceBlock = $exception->spaceBlock;
            $spaceName = $spaceBlock->space->name;
            $formattedDate = Carbon::parse($exception->exception_date)->format('d/m/Y');

            return response()->json([
                'success' => true,
                'message' => "Se ha creado una excepción para el bloqueo en {$spaceName} el {$formattedDate}.",
                'exception' => $exception
            ]);
        } catch (\Exception $e) {
            // Registrar el error para depuración
            \Log::error('Error al crear excepción de bloqueo: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la excepción: ' . $e->getMessage()
            ], 500);
        }
    }
}
