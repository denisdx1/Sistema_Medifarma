<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register custom middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'password.change' => \App\Http\Middleware\PasswordChangeMiddleware::class,
            'session.expired' => \App\Http\Middleware\HandleSessionExpired::class,
        ]);
        
        // Add global middleware to handle session expired
        $middleware->append(\App\Http\Middleware\HandleSessionExpired::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Manejar errores 419 (Page Expired) globalmente
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Token CSRF expirado'], 419);
            }
            
            // Limpiar sesión
            if (auth()->check()) {
                auth()->logout();
                $request->session()->flush();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
            
            return response()->view('errors.419', [], 419);
        });
    })->create();
