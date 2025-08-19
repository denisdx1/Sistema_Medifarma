<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthService
{
    public function attempt(array $credentials): bool
    {
        // Buscar usuario por login en lugar de email
        $user = User::where('login', $credentials['login'])->first();

        if (! $user) {
            return false;
        }

        // Verificar que el usuario esté activo
        if ($user->idEstado != 1) {
            return false;
        }

        // Verificar contraseña usando SHA2_256
        if (! $this->verificarPasswordSHA256($credentials['password'], $user->password)) {
            return false;
        }

        Auth::login($user);

        return true;
    }

    /**
     * Verificar contraseña con SHA2_256
     */
    private function verificarPasswordSHA256(string $passwordTextoPlano, $passwordHasheadaBD): bool
    {
        // Generar hash SHA2_256 de la contraseña en texto plano
        $passwordHasheada = hash('sha256', $passwordTextoPlano, true); // true para obtener binario
        
        // Comparar con la contraseña almacenada en la BD (que está en varbinary)
        return $passwordHasheada === $passwordHasheadaBD;
    }

    public function logout(): void
    {
        Auth::logout();
        
        // Invalidate the session
        request()->session()->invalidate();
        
        // Regenerate the CSRF token
        request()->session()->regenerateToken();
    }
}
