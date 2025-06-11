<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\User;
use App\Notifications\NewAnnouncementNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $announcements = Announcement::orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('announcements.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'expiry_date' => 'nullable|date|after:today',
            'priority' => 'required|integer|min:0|max:10'
        ]);

        $announcement = Announcement::create($validated);

        // Enviar notificación a todos los usuarios
        try {
            $users = User::where('active', true)->get();
            Notification::send($users, new NewAnnouncementNotification($announcement));
            
            \Log::info('Notificaciones de nuevo anuncio enviadas', [
                'announcement_id' => $announcement->id,
                'users_count' => $users->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al enviar notificaciones de anuncio: ' . $e->getMessage());
            // No detener el proceso si falla el envío de notificaciones
        }

        return redirect()->route('announcements.index')
            ->with('success', 'Aviso creado exitosamente y notificado a todos los usuarios.');
    }

    public function edit(Announcement $announcement)
    {
        return view('announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'expiry_date' => 'nullable|date',
            'priority' => 'required|integer|min:0|max:10',
            'is_active' => 'boolean'
        ]);

        $announcement->update($validated);

        return redirect()->route('announcements.index')
            ->with('success', 'Aviso actualizado exitosamente.');
    }

    public function destroy(Announcement $announcement)
    {
        if ($announcement->image_path) {
            Storage::disk('public')->delete($announcement->image_path);
        }
        
        $announcement->delete();

        return redirect()->route('announcements.index')
            ->with('success', 'Aviso eliminado exitosamente.');
    }
}
