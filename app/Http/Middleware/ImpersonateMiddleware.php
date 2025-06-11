<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->session()->has('impersonate')) {
            // Add variables to share with all views
            view()->share('impersonating', true);
            view()->share('originalUser', Auth::user());
        } else {
            view()->share('impersonating', false);
        }
        
        return $next($request);
    }
}
