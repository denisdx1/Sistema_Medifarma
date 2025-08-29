<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CheckInactivity
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
        // Solo verificar si el usuario está autenticado
        if (Auth::check()) {
            $lastActivity = Session::get('last_activity');
            $timeout = 60; // 1 minuto en segundos
            
            // Si no hay actividad registrada, crear una
            if (!$lastActivity) {
                Session::put('last_activity', time());
            } else {
                // Verificar si ha pasado más de 1 minuto
                if (time() - $lastActivity > $timeout) {
                    // Logout automático
                    Auth::logout();
                    Session::flush();
                    
                    // Si es una petición AJAX, devolver JSON
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Sesión expirada por inactividad',
                            'redirect' => route('login')
                        ], 401);
                    }
                    
                    // Redirigir al login con mensaje
                    return redirect()->route('login')
                        ->with('error', 'Su sesión ha expirado por inactividad. Por favor, inicie sesión nuevamente.');
                }
                
                // Actualizar la última actividad
                Session::put('last_activity', time());
            }
        }
        
        return $next($request);
    }
}
