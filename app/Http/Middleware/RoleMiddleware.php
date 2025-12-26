<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user is active (usando la nueva estructura)
        if ($user->idEstado !== 1) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Tu cuenta ha sido desactivada. Contacta al administrador.');
        }

        // Check if user has any of the required roles
        if (!empty($roles)) {
            $hasRequiredRole = false;
            
            foreach ($roles as $role) {
                switch ($role) {
                    case 'administrador':
                        if ($user->isAdmin()) {
                            $hasRequiredRole = true;
                        }
                        break;
                    case 'gerente_producto':
                        if ($user->isGerenteProducto()) {
                            $hasRequiredRole = true;
                        }
                        break;
                    // Mantener compatibilidad con roles por ID
                    case '1':
                        if ($user->idRol == 1) {
                            $hasRequiredRole = true;
                        }
                        break;
                    case '2':
                        if ($user->idRol == 2) {
                            $hasRequiredRole = true;
                        }
                        break;
                }
                
                if ($hasRequiredRole) {
                    break;
                }
            }
            
            if (!$hasRequiredRole) {
                abort(403, 'No tienes permisos para acceder a esta sección.');
            }
        }

        return $next($request);
    }
}
