<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es requerido',
            'name.max' => 'El nombre no puede tener más de :max caracteres',
            'email.required' => 'El email es requerido',
            'email.email' => 'El email debe ser una dirección válida',
            'email.max' => 'El email no puede tener más de :max caracteres',
            'password.required' => 'La contraseña es requerida',
            'password.min' => 'La contraseña debe tener al menos :min caracteres',
            'roles.array' => 'Los roles deben ser una lista',
            'roles.*.string' => 'Cada rol debe ser una cadena de texto',
        ];
    }
}
