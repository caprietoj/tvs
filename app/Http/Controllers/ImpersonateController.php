<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function impersonate(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        if (!$user->canBeImpersonated()) {
            return redirect()->back()->with('error', 'No puedes impersonar a este usuario.');
        }

        // Store current user id in session
        $request->session()->put('impersonate', Auth::id());
        
        // Login as the impersonated user
        Auth::login($user);

        return redirect()->route('home')
            ->with('success', 'Ahora estás viendo el sistema como ' . $user->name);
    }

    public function stopImpersonating(Request $request)
    {
        // Verify if user is actually impersonating
        if (!$request->session()->has('impersonate')) {
            return redirect()->route('home');
        }

        $originalId = $request->session()->get('impersonate');
        $originalUser = User::findOrFail($originalId);
        
        // Remove impersonation session
        $request->session()->forget('impersonate');
        
        // Login as original user
        Auth::login($originalUser);

        return redirect()->route('home')
            ->with('success', 'Has vuelto a tu cuenta original');
    }
}
