<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    /**
     * Redirige al usuario a la página de autenticación de Google
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Maneja el callback de Google después de la autenticación
     */
    public function handleGoogleCallback()
    {
        try {
            // Obtener el usuario de Google
            $googleUser = Socialite::driver('google')->user();
            
            // Buscar usuario por google_id
            $user = User::where('google_id', $googleUser->getId())->first();
            
            // Si no existe, buscar por email
            if (!$user) {
                $user = User::where('email', $googleUser->getEmail())->first();
                
                // Si existe usuario con ese email, vincular google_id
                if ($user) {
                    $user->google_id = $googleUser->getId();
                    $user->save();
                } else {
                    // Crear nuevo usuario
                    $user = User::create([
                        'name' => $googleUser->getName(),
                        'email' => $googleUser->getEmail(),
                        'google_id' => $googleUser->getId(),
                        'password' => Hash::make(Str::random(32)), // Password aleatorio
                        'email_verified_at' => now(),
                    ]);
                }
            }
            
            // Autenticar al usuario
            Auth::login($user, true);
            
            return redirect()->intended('/home');
            
        } catch (Exception $e) {
            return redirect()->route('login')->with('error', 'Error al autenticar con Google: ' . $e->getMessage());
        }
    }
}
