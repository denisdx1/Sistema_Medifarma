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
            return redirect()->route('market-management.index')->with('success', 'Has iniciado sesión correctamente');
        }

        return back()->withErrors(['login' => 'Credenciales incorrectas'])->withInput();
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logout();
        return redirect()->route('login');
    }
}
