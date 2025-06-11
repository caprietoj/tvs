<?php

namespace App\Http\Controllers;

use App\Models\HelpVideo;
use Illuminate\Http\Request;

class HelpVideoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', HelpVideo::class);
        
        // Los administradores pueden ver todos los videos, los usuarios normales solo los activos
        if (auth()->user()->hasRole('admin')) {
            $videos = HelpVideo::orderBy('created_at', 'desc')->get();
        } else {
            $videos = HelpVideo::active()->orderBy('created_at', 'desc')->get();
        }
        
        return view('help-videos.index', compact('videos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', HelpVideo::class);
        return view('help-videos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', HelpVideo::class);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'video_url' => 'required|url',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean'
        ], [
            'title.required' => 'El título es obligatorio.',
            'title.string' => 'El título debe ser texto.',
            'title.max' => 'El título no puede exceder los 255 caracteres.',
            'video_url.required' => 'La URL del video es obligatoria.',
            'video_url.url' => 'La URL del video debe ser válida.',
            'description.string' => 'La descripción debe ser texto.'
        ]);

        HelpVideo::create([
            'title' => $request->title,
            'video_url' => $request->video_url,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active')
        ]);

        return redirect()->route('help-videos.index')
            ->with('success', 'Video de ayuda creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(HelpVideo $helpVideo)
    {
        $this->authorize('view', $helpVideo);
        
        // Si el usuario no es admin y el video está inactivo, no puede verlo
        if (!auth()->user()->hasRole('admin') && !$helpVideo->is_active) {
            abort(404);
        }
        
        return view('help-videos.show', compact('helpVideo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HelpVideo $helpVideo)
    {
        $this->authorize('update', $helpVideo);
        return view('help-videos.edit', compact('helpVideo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HelpVideo $helpVideo)
    {
        $this->authorize('update', $helpVideo);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'video_url' => 'required|url',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean'
        ], [
            'title.required' => 'El título es obligatorio.',
            'title.string' => 'El título debe ser texto.',
            'title.max' => 'El título no puede exceder los 255 caracteres.',
            'video_url.required' => 'La URL del video es obligatoria.',
            'video_url.url' => 'La URL del video debe ser válida.',
            'description.string' => 'La descripción debe ser texto.'
        ]);

        $helpVideo->update([
            'title' => $request->title,
            'video_url' => $request->video_url,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active')
        ]);

        return redirect()->route('help-videos.index')
            ->with('success', 'Video de ayuda actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HelpVideo $helpVideo)
    {
        $this->authorize('delete', $helpVideo);
        
        $helpVideo->delete();

        return redirect()->route('help-videos.index')
            ->with('success', 'Video de ayuda eliminado exitosamente.');
    }
}
