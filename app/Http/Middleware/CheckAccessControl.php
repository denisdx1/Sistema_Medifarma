<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckAccessControl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return $next($request);
        }
        
        $user = Auth::user();
        
        // Si el usuario es administrador, permitir acceso siempre
        if ($user && $user->idRol === 1) { // Asumiendo que 1 es el ID del rol administrador
            return $next($request);
        }
        
        // Verificar si el usuario está en la lista de excepciones
        if ($this->isUserException($user)) {
            return $next($request);
        }
        
        // Verificar si el sistema está deshabilitado
        if ($this->isSystemDisabled()) {
            return redirect()->route('access.denied');
        }
        
        return $next($request);
    }
    
    /**
     * Verificar si el sistema está deshabilitado según la configuración
     */
    private function isSystemDisabled(): bool
    {
        try {
            $configPath = storage_path('app/access_control.json');
            
            if (!file_exists($configPath)) {
                return false; // Sin configuración = sistema habilitado
            }
            
            $config = json_decode(file_get_contents($configPath), true);
            
            if (!$config || !isset($config['fecha_inicio']) || !isset($config['fecha_fin'])) {
                return false;
            }
            
            $now = now();
            $fechaInicio = \Carbon\Carbon::parse($config['fecha_inicio']);
            $fechaFin = \Carbon\Carbon::parse($config['fecha_fin']);
            
            // Si estamos dentro del período de deshabilitación
            return $now->between($fechaInicio, $fechaFin);
            
        } catch (\Exception $e) {
            // En caso de error, permitir acceso
            return false;
        }
    }
    
    /**
     * Verificar si el usuario está en la lista de excepciones
     */
    private function isUserException($user): bool
    {
        try {
            $exceptionsPath = storage_path('app/user_exceptions.json');
            
            if (!file_exists($exceptionsPath)) {
                return false; // Sin excepciones = no acceso especial
            }
            
            $exceptions = json_decode(file_get_contents($exceptionsPath), true);
            
            if (!$exceptions || !isset($exceptions['users'])) {
                return false;
            }
            
            // Verificar si el ID del usuario está en la lista de excepciones
            return in_array($user->idUsuario, $exceptions['users']);
            
        } catch (\Exception $e) {
            // En caso de error, no permitir acceso especial
            return false;
        }
    }
}