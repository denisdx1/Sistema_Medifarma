<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class PasswordChangeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            \Log::info('PasswordChangeMiddleware: Usuario no autenticado, redirigiendo a login');
            return redirect()->route('login');
        }

        $user = Auth::user();
        $routeName = $request->route() ? $request->route()->getName() : 'unknown';

        \Log::info("PasswordChangeMiddleware: Usuario {$user->usuario} accediendo a ruta: {$routeName}");

        // Excluir rutas relacionadas con cambio de contraseña y logout para evitar bucles
        $rutasExcluidas = [
            'usuarios.cambio-password',
            'usuarios.cambio-password.submit',
            'usuarios.primer-cambio-password',
            'usuarios.primer-cambio-password.submit',
            'login',
            'login.attempt',
            'logout',
            'logout.get'
        ];

        if (in_array($routeName, $rutasExcluidas)) {
            \Log::info("PasswordChangeMiddleware: Ruta {$routeName} excluida, continuando");
            return $next($request);
        }

        // Verificar si el usuario requiere cambio de contraseña
        if ($this->requiereCambioPassword($user)) {
            \Log::info("PasswordChangeMiddleware: Usuario {$user->usuario} requiere cambio de contraseña");
            // Redirigir a la página de cambio de contraseña obligatorio
            return redirect()->route('usuarios.cambio-password')
                ->with('info', 'Debes cambiar tu contraseña antes de continuar.');
        }

        \Log::info("PasswordChangeMiddleware: Usuario {$user->usuario} autorizado, continuando");
        return $next($request);
    }

    /**
     * Verificar si el usuario requiere cambio de contraseña
     */
    private function requiereCambioPassword($user): bool
    {
        // Verificar si la contraseña es temporal (misma que el login + algún patrón)
        // O si hay un campo específico en BD que indique primer login
        
        // Por ahora, verificamos si la contraseña es simple (como 123456)
        $passwordsTemporales = ['123456', 'password', 'temporal', $user->login];
        
        foreach ($passwordsTemporales as $passwordTemporal) {
            if (hash('sha256', $passwordTemporal, true) === $user->password) {
                return true;
            }
        }
        
        return false;
    }
}
