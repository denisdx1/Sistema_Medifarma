<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string', 'min:3'],
        ];
    }

    public function messages(): array
    {
        return [
            'login.required' => 'El usuario (login) es requerido',
            'login.string' => 'El usuario debe ser una cadena de texto válida',
            'password.required' => 'La contraseña es requerida',
            'password.min' => 'La contraseña debe tener al menos 3 caracteres',
        ];
    }
}
