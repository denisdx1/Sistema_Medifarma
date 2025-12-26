<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class VerificarCambioPasswordObligatorio
{
    /**
     * Handle an incoming request.
     * Verifica si el usuario debe cambiar su contraseña en el primer login
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo aplicar a usuarios autenticados
        if (!Auth::check()) {
            return $next($request);
        }

        $usuario = Auth::user();

        // Excluir rutas relacionadas con cambio de contraseña y logout para evitar bucles
        $rutasExcluidas = [
            'usuarios.primer-cambio-password',
            'usuarios.primer-cambio-password.submit',
            'login',
            'login.attempt',
            'logout',
            'logout.now'
        ];

        if (in_array($request->route()->getName(), $rutasExcluidas)) {
            return $next($request);
        }

        // TODO: Verificar en la base de datos si el usuario requiere cambio de contraseña
        // Por ahora, verificamos usando una lógica temporal
        if ($this->requiereCambioPassword($usuario)) {
            // Cerrar sesión actual y redirigir a la página de cambio de contraseña
            auth()->logout();
            return redirect()->route('usuarios.primer-cambio-password')
                ->with('warning', 'Debe cambiar su contraseña temporal antes de acceder al sistema.')
                ->with('login_email', $usuario->login); // Pasar el email para prellenar el formulario
        }

        return $next($request);
    }

    /**
     * Determina si el usuario requiere cambio de contraseña
     * TODO: Implementar verificación real con campo en BD
     */
    private function requiereCambioPassword($usuario): bool
    {
        // TEMPORAL: Por ahora retorna false para evitar bucles
        // Cuando se implemente el SP, aquí se verificará el campo correspondiente
        
        // Ejemplo de lógica futura:
        // return $usuario->requiere_cambio_password == 1;
        
        return false;
    }
}
