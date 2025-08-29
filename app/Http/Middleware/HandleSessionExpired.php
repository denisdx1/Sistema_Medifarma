<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HandleSessionExpired
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Si la respuesta es un error 419 (Page Expired), mostrar vista personalizada
        if ($response->getStatusCode() === 419) {
            // Limpiar la sesión si está autenticado
            if (Auth::check()) {
                Auth::logout();
                $request->session()->flush();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
            
            // Retornar vista personalizada para error 419
            return response()->view('errors.419', [], 419);
        }

        return $response;
    }
}
