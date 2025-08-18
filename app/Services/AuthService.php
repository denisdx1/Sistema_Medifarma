<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthService
{
    public function attempt(array $credentials): bool
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user) {
            return false;
        }

        if (! Hash::check($credentials['password'], $user->password)) {
            return false;
        }

        Auth::login($user);

        return true;
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
