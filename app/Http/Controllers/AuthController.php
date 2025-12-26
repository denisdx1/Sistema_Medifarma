<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('login', 'password');

        if ($this->authService->attempt($credentials)) {
            $user = auth()->user();
            

            
            // Verificar si requiere cambio de contraseña
            if ($this->authService->requiereCambioPassword($user)) {
                return redirect()->route('usuarios.cambio-password')
                    ->with('info', 'Debes cambiar tu contraseña antes de continuar.');
            }
            
            return redirect()->route('market-management.index')
                ->with('success', 'Has iniciado sesión correctamente');
        }

        return back()->withErrors(['login' => 'Credenciales incorrectas'])->withInput();
    }

    public function logout(Request $request): RedirectResponse
    {
        try {
            // Verificar si el usuario está autenticado antes de intentar logout
            if (auth()->check()) {
                $this->authService->logout();
            }
            
            // Limpiar completamente la sesión
            $request->session()->flush();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            // Verificar si es un logout automático por inactividad
            $isAutoLogout = $request->is('auto-logout') || $request->has('inactivity');
            
            if ($isAutoLogout) {
                return redirect()->route('login')->with('warning', 'Su sesión ha expirado por inactividad. Por favor, inicie sesión nuevamente.');
            }
            
            return redirect()->route('login')->with('success', 'Has cerrado sesión correctamente');
        } catch (\Exception $e) {
            // Si hay algún error, forzar el logout y redirigir
            if (auth()->check()) {
                auth()->logout();
            }
            $request->session()->flush();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            $isAutoLogout = $request->is('auto-logout') || $request->has('inactivity');
            
            if ($isAutoLogout) {
                return redirect()->route('login')->with('warning', 'Su sesión ha expirado por inactividad. Por favor, inicie sesión nuevamente.');
            }
            
            return redirect()->route('login')->with('info', 'Sesión cerrada');
        }
    }
}
