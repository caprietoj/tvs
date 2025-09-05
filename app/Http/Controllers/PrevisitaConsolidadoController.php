<?php

namespace App\Http\Controllers;

use App\Models\PrevisitaConsolidado;
use App\Models\PrevisitaArchivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PrevisitaConsolidadoController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            return $this->checkPrevisitasAccess($request, $next, 'view');
        })->only(['index', 'show', 'dashboard', 'downloadFile']);
        
        $this->middleware(function ($request, $next) {
            return $this->checkPrevisitasAccess($request, $next, 'edit');
        })->only(['create', 'store', 'edit', 'update', 'destroy']);
        
        // Las sugerencias solo requieren autenticación básica, no permisos específicos
    }
    
    /**
     * Verificar acceso específico a previsitas por usuario
     */
    private function checkPrevisitasAccess($request, $next, $type = 'view')
    {
        $userEmail = auth()->user()->email;
        
        // Usuarios con permisos de solo lectura
        $readOnlyUsers = [
            'coordpai@tvs.edu.co',
            'asistentegeneral@tvs.edu.co',
            'coordpep@tvs.edu.co',
            'preschool@tvs.edu.co',
            'dp@tvs.edu.co',
            'generaldirector@tvs.edu.co',
            'escuelamedia@tvs.edu.co'
        ];

        // Usuarios con permisos completos
        $editorUsers = [
            'asistentebachillerato@tvs.edu.co',
            'asistentepyp@tvs.edu.co',
            'wrueda@tvs.edu.co',
            'asistenteadministrativa@tvs.edu.co'
        ];
        
        // Verificar si el usuario tiene acceso
        if (in_array($userEmail, $readOnlyUsers)) {
            // Solo permitir acciones de lectura
            if ($type === 'view') {
                return $next($request);
            } else {
                abort(403, 'No tienes permisos para realizar esta acción.');
            }
        } elseif (in_array($userEmail, $editorUsers)) {
            // Permitir todas las acciones
            return $next($request);
        } elseif (auth()->user()->can('admin')) {
            // Los administradores tienen acceso completo
            return $next($request);
        } else {
            abort(403, 'No tienes acceso al módulo de previsitas.');
        }
    }

    /**
     * Mostrar listado de consolidado previsitas
     */
    public function index(Request $request)
    {
        $query = PrevisitaConsolidado::with('user');

        // Filtros
        if ($request->filled('lugar')) {
            $query->where('lugar', 'like', '%' . $request->lugar . '%');
        }

        if ($request->filled('responsable')) {
            $query->where('responsable', 'like', '%' . $request->responsable . '%');
        }

        if ($request->filled('fecha_desde')) {
            $query->where('fecha_visita', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_visita', '<=', $request->fecha_hasta);
        }

        if ($request->filled('aprobacion_sitio')) {
            $query->where('aprobacion_sitio', $request->aprobacion_sitio === 'si');
        }

        $previsitas = $query->orderBy('fecha_visita', 'desc')->paginate(15);

        if ($request->ajax()) {
            return response()->json([
                'data' => $previsitas->items(),
                'pagination' => [
                    'current_page' => $previsitas->currentPage(),
                    'last_page' => $previsitas->lastPage(),
                    'per_page' => $previsitas->perPage(),
                    'total' => $previsitas->total()
                ]
            ]);
        }

        return view('previsitas.index', compact('previsitas'));
    }

    /**
     * Mostrar formulario para crear nueva previsita
     */
    public function create()
    {
        return view('previsitas.create');
    }

    /**
     * Almacenar nueva previsita
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lugar' => 'required|string|max:255',
            'fecha_visita' => 'required|date',
            'vencimiento' => 'nullable|date|after_or_equal:fecha_visita',
            'responsable' => 'required|string|max:255',
            'aprobacion_sitio' => 'required|boolean',
            'observaciones_recomendaciones' => 'nullable|string',
            'archivos_novedades' => 'nullable|array|max:100', // Máximo 100 archivos
            'archivos_novedades.*' => 'file|mimes:pdf,jpg,jpeg,png,gif,bmp,webp,doc,docx|max:10240' // 10MB máximo por archivo, incluye Word
        ], [
            'lugar.required' => 'El lugar es obligatorio.',
            'fecha_visita.required' => 'La fecha de visita es obligatoria.',
            'fecha_visita.date' => 'La fecha de visita debe ser una fecha válida.',
            'vencimiento.date' => 'El vencimiento debe ser una fecha válida.',
            'vencimiento.after_or_equal' => 'El vencimiento debe ser igual o posterior a la fecha de visita.',
            'responsable.required' => 'El responsable es obligatorio.',
            'aprobacion_sitio.required' => 'La aprobación del sitio es obligatoria.',
            'archivos_novedades.max' => 'No se pueden subir más de 100 archivos.',
            'archivos_novedades.*.file' => 'Cada archivo debe ser un archivo válido.',
            'archivos_novedades.*.mimes' => 'Solo se permiten archivos PDF, JPG, JPEG, PNG, GIF, BMP, WEBP, DOC y DOCX.',
            'archivos_novedades.*.max' => 'Cada archivo no debe superar los 10MB.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->only([
            'lugar',
            'fecha_visita',
            'vencimiento',
            'responsable',
            'aprobacion_sitio',
            'observaciones_recomendaciones'
        ]);

        $data['user_id'] = Auth::id();

        // Crear la previsita primero
        $previsita = PrevisitaConsolidado::create($data);

        // Manejar subida de múltiples archivos
        if ($request->hasFile('archivos_novedades')) {
            foreach ($request->file('archivos_novedades') as $file) {
                // Generar nombre único para el archivo
                $timestamp = time();
                $filename = $timestamp . '_' . uniqid() . '_' . $file->getClientOriginalName();
                
                // Almacenar archivo
                $path = $file->storeAs('previsitas/archivos', $filename, 'public');
                
                // Determinar tipo de archivo basado en MIME type
                $mimeType = $file->getMimeType();
                $tipoArchivo = $this->determinarTipoArchivo($mimeType);
                
                // Crear registro en la base de datos
                PrevisitaArchivo::create([
                    'previsita_consolidado_id' => $previsita->id,
                    'nombre_original' => $file->getClientOriginalName(),
                    'nombre_archivo' => $filename,
                    'ruta_archivo' => $path,
                    'tipo_archivo' => $tipoArchivo,
                    'mime_type' => $mimeType,
                    'tamaño_archivo' => $file->getSize()
                ]);
            }
        }

        return redirect()->route('previsitas.index')
            ->with('success', 'Consolidado previsita creado exitosamente.');
    }

    /**
     * Mostrar detalles de una previsita
     */
    public function show(PrevisitaConsolidado $previsita)
    {
        $previsita->load(['user', 'archivos']);
        return view('previsitas.show', compact('previsita'));
    }

    /**
     * Mostrar formulario para editar previsita
     */
    public function edit(PrevisitaConsolidado $previsita)
    {
        $previsita->load('archivos');
        return view('previsitas.edit', compact('previsita'));
    }

    /**
     * Actualizar previsita
     */
    public function update(Request $request, PrevisitaConsolidado $previsita)
    {
        $validator = Validator::make($request->all(), [
            'lugar' => 'required|string|max:255',
            'fecha_visita' => 'required|date',
            'vencimiento' => 'nullable|date|after_or_equal:fecha_visita',
            'responsable' => 'required|string|max:255',
            'aprobacion_sitio' => 'required|boolean',
            'observaciones_recomendaciones' => 'nullable|string',
            'archivos_novedades' => 'nullable|array|max:100', // Máximo 100 archivos
            'archivos_novedades.*' => 'file|mimes:pdf,jpg,jpeg,png,gif,bmp,webp,doc,docx|max:10240' // 10MB máximo por archivo, incluye Word
        ], [
            'lugar.required' => 'El lugar es obligatorio.',
            'fecha_visita.required' => 'La fecha de visita es obligatoria.',
            'fecha_visita.date' => 'La fecha de visita debe ser una fecha válida.',
            'vencimiento.date' => 'El vencimiento debe ser una fecha válida.',
            'vencimiento.after_or_equal' => 'El vencimiento debe ser igual o posterior a la fecha de visita.',
            'responsable.required' => 'El responsable es obligatorio.',
            'aprobacion_sitio.required' => 'La aprobación del sitio es obligatoria.',
            'archivos_novedades.max' => 'No se pueden subir más de 100 archivos.',
            'archivos_novedades.*.file' => 'Cada archivo debe ser un archivo válido.',
            'archivos_novedades.*.mimes' => 'Solo se permiten archivos PDF, JPG, JPEG, PNG, GIF, BMP, WEBP, DOC y DOCX.',
            'archivos_novedades.*.max' => 'Cada archivo no debe superar los 10MB.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->only([
            'lugar',
            'fecha_visita',
            'vencimiento',
            'responsable',
            'aprobacion_sitio',
            'observaciones_recomendaciones'
        ]);

        // Actualizar datos básicos de la previsita
        $previsita->update($data);

        // Manejar subida de nuevos archivos
        if ($request->hasFile('archivos_novedades')) {
            foreach ($request->file('archivos_novedades') as $file) {
                // Generar nombre único para el archivo
                $timestamp = time();
                $filename = $timestamp . '_' . uniqid() . '_' . $file->getClientOriginalName();
                
                // Almacenar archivo
                $path = $file->storeAs('previsitas/archivos', $filename, 'public');
                
                // Determinar tipo de archivo basado en MIME type
                $mimeType = $file->getMimeType();
                $tipoArchivo = $this->determinarTipoArchivo($mimeType);
                
                // Crear registro en la base de datos
                PrevisitaArchivo::create([
                    'previsita_consolidado_id' => $previsita->id,
                    'nombre_original' => $file->getClientOriginalName(),
                    'nombre_archivo' => $filename,
                    'ruta_archivo' => $path,
                    'tipo_archivo' => $tipoArchivo,
                    'mime_type' => $mimeType,
                    'tamaño_archivo' => $file->getSize()
                ]);
            }
        }

        return redirect()->route('previsitas.index')
            ->with('success', 'Consolidado previsita actualizado exitosamente.');
    }

    /**
     * Eliminar previsita
     */
    public function destroy(PrevisitaConsolidado $previsita)
    {
        // Eliminar archivos asociados
        foreach ($previsita->archivos as $archivo) {
            Storage::disk('public')->delete($archivo->ruta_archivo);
            $archivo->delete();
        }

        // Eliminar archivo legacy si existe
        if ($previsita->novedades_visita_archivo) {
            Storage::disk('public')->delete($previsita->novedades_visita_archivo);
        }

        $previsita->delete();

        return redirect()->route('previsitas.index')
            ->with('success', 'Consolidado previsita eliminado exitosamente.');
    }

    /**
     * Descargar archivo específico
     */
    public function downloadArchivo(PrevisitaArchivo $archivo)
    {
        $pathToFile = storage_path('app/public/' . $archivo->ruta_archivo);
        
        if (!file_exists($pathToFile)) {
            abort(404, 'Archivo no encontrado');
        }

        return response()->download($pathToFile, $archivo->nombre_original);
    }

    /**
     * Eliminar archivo específico
     */
    public function destroyArchivo(PrevisitaArchivo $archivo)
    {
        // Verificar que el usuario tenga permisos
        $request = request();
        $this->checkPrevisitasAccess($request, function() {}, 'edit');

        // Eliminar archivo físico
        if (Storage::disk('public')->exists($archivo->ruta_archivo)) {
            Storage::disk('public')->delete($archivo->ruta_archivo);
        }

        // Eliminar registro de la base de datos
        $archivo->delete();

        return response()->json(['success' => true, 'message' => 'Archivo eliminado exitosamente']);
    }

    /**
     * Descargar archivo PDF de novedades
     */
    public function downloadFile(PrevisitaConsolidado $previsita)
    {
        if (!$previsita->novedades_visita_archivo || !Storage::disk('public')->exists($previsita->novedades_visita_archivo)) {
            return redirect()->back()->with('error', 'El archivo no existe.');
        }

        return Storage::disk('public')->download(
            $previsita->novedades_visita_archivo,
            'novedades_visita_' . $previsita->id . '.pdf'
        );
    }

    /**
     * Obtener estadísticas del dashboard
     */
    public function dashboard()
    {
        $totalPrevisitas = PrevisitaConsolidado::count();
        $aprobadas = PrevisitaConsolidado::where('aprobacion_sitio', true)->count();
        $pendientes = PrevisitaConsolidado::where('aprobacion_sitio', false)->count();
        $vencidas = PrevisitaConsolidado::where('vencimiento', '<', now())->count();
        
        $proximasVencer = PrevisitaConsolidado::where('vencimiento', '>=', now())
            ->where('vencimiento', '<=', now()->addDays(7))
            ->with('user')
            ->orderBy('vencimiento')
            ->limit(5)
            ->get();

        return view('previsitas.dashboard', compact(
            'totalPrevisitas',
            'aprobadas',
            'pendientes',
            'vencidas',
            'proximasVencer'
        ));
    }

    /**
     * Obtener sugerencias de lugares para autocompletado
     */
    public function getLugarSuggestions(Request $request)
    {
        $term = $request->get('term', '');
        
        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $lugares = PrevisitaConsolidado::where('lugar', 'like', '%' . $term . '%')
            ->select('lugar')
            ->distinct()
            ->orderBy('lugar')
            ->limit(10)
            ->pluck('lugar');

        return response()->json($lugares);
    }

    /**
     * Obtener sugerencias de responsables para autocompletado
     */
    public function getResponsableSuggestions(Request $request)
    {
        $term = $request->get('term', '');
        
        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $responsables = PrevisitaConsolidado::where('responsable', 'like', '%' . $term . '%')
            ->select('responsable')
            ->distinct()
            ->orderBy('responsable')
            ->limit(10)
            ->pluck('responsable');

        return response()->json($responsables);
    }

    /**
     * Determinar tipo de archivo basado en el MIME type
     */
    private function determinarTipoArchivo($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        } elseif ($mimeType === 'application/pdf') {
            return 'pdf';
        } elseif (in_array($mimeType, [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ])) {
            return 'word';
        } else {
            // Fallback: determinar por extensión del MIME type
            if (str_contains($mimeType, 'word') || str_contains($mimeType, 'document')) {
                return 'word';
            }
            return 'document'; // Tipo genérico para otros documentos
        }
    }
}