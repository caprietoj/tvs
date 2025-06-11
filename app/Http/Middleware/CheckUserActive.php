<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserActive
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && !Auth::user()->active) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Tu cuenta ha sido desactivada. Por favor contacta al administrador.');
        }

        return $next($request);
    }
}
