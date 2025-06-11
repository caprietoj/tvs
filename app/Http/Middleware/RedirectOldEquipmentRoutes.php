<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectOldEquipmentRoutes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Redirige la URL incorrecta de check-availability a la correcta
        if ($request->is('equipment/loans/check-availability')) {
            $queryString = $request->getQueryString();
            $newUrl = '/equipment/check-availability';
            
            if ($queryString) {
                $newUrl .= '?' . $queryString;
            }
            
            // Si es una solicitud AJAX, actualiza la respuesta con una redirección JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'redirect' => $newUrl,
                    'message' => 'Redirigiendo a la ruta correcta'
                ], 302);
            }
            
            // Si no es AJAX, redirige normalmente
            return redirect($newUrl);
        }

        return $next($request);
    }
}
