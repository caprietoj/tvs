<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Mail\WelcomeNewUser;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'first_login' => true,
        ]);

        $user->roles()->sync($request->roles);

        // Send welcome email
        $this->sendWelcomeEmail($user, $request->password);

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(User $user)  // Type-hint the User model
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)  // Type-hint the User model
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'cargo' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'required|boolean'
        ]);

        $data = $request->except(['password', 'avatar']);
        
        // Handle password update
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        
        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }
        
        // Ensure active is boolean
        $data['active'] = filter_var($request->active, FILTER_VALIDATE_BOOLEAN);

        $user->update($data);
        $user->roles()->sync($request->roles);

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado exitosamente');
    }

    /**
     * Show bulk import form
     */
    public function showBulkImport()
    {
        $roles = Role::all();
        return view('admin.users.bulk-import', compact('roles'));
    }

    /**
     * Process bulk user import
     */
    public function bulkImport(Request $request)
    {
        $request->validate([
            'user_data' => 'required|string',
            'default_roles' => 'required|array',
        ]);

        $userData = $request->user_data;
        $lines = explode("\n", $userData);
        
        $successCount = 0;
        $errors = [];
        
        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $columns = preg_split('/\t/', $line);
            if (count($columns) < 2) {
                $columns = str_getcsv($line, ',');
            }
            
            if (count($columns) < 2) {
                $errors[] = "Línea " . ($index + 1) . ": formato inválido, se requiere al menos nombre y email.";
                continue;
            }
            
            $name = trim($columns[0]);
            $email = trim($columns[1]);
            $password = Str::random(10); // Generate random password
            
            $validator = Validator::make([
                'name' => $name,
                'email' => $email,
            ], [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
            ]);
            
            if ($validator->fails()) {
                $errors[] = "Línea " . ($index + 1) . ": " . implode(', ', $validator->errors()->all());
                continue;
            }
            
            try {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'first_login' => true,
                ]);
                
                $user->roles()->sync($request->default_roles);
                
                // Send welcome email
                $this->sendWelcomeEmail($user, $password);
                
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Línea " . ($index + 1) . ": " . $e->getMessage();
            }
        }
        
        $message = "Se han creado $successCount usuarios correctamente";
        if (!empty($errors)) {
            return redirect()->back()
                ->with('success', $message)
                ->with('errors', $errors);
        }
        
        return redirect()->route('users.index')
            ->with('success', $message);
    }
    
    /**
     * Send welcome email to newly created user
     */
    private function sendWelcomeEmail($user, $password)
    {
        try {
            Mail::to($user->email)->send(new WelcomeNewUser($user, $password));
        } catch (\Exception $e) {
            \Log::error('Error sending welcome email: ' . $e->getMessage());
        }
    }

    /**
     * Download Excel template for bulk user import
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=users_template.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for proper Excel encoding
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Add header row
            fputcsv($file, ['Nombre Completo', 'Correo Electrónico']);
            
            // Add example row
            fputcsv($file, ['Juan Pérez', 'juan.perez@ejemplo.com']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show password change form for first login
     */
    public function showChangePasswordForm()
    {
        return view('auth.passwords.first-login');
    }

    /**
     * Update password on first login
     */
    public function updateFirstPassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed|different:current_password',
        ]);
        
        $user = auth()->user();
        
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
        }
        
        $user->password = Hash::make($request->password);
        $user->first_login = false;
        $user->save();
        
        return redirect()->route('home')->with('success', 'Contraseña actualizada correctamente.');
    }

    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }
}