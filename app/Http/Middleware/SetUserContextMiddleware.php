<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SetUserContextMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * Este middleware establece el contexto del usuario en SQL Server
     * para que los triggers puedan registrar correctamente las auditorías.
     */
    public function handle(Request $request, Closure $next)
    {
        // Solo establecer contexto si hay un usuario autenticado
        if (Auth::check()) {
            try {
                $userId = Auth::id();
                
                // Establecer el contexto del usuario en SQL Server para los triggers
                DB::connection('sqlsrv')->statement('EXEC ODS.SP_SET_USER_CONTEXT ?', [$userId]);
                
            } catch (\Exception $e) {
                // Log del error pero no interrumpir la ejecución
                \Log::warning('Error al establecer contexto de usuario para auditoría', [
                    'user_id' => Auth::id(),
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $next($request);
    }
}
