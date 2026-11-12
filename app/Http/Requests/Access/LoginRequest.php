<?php

namespace App\Http\Requests\Access;

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
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'login.required' => "L'identifiant est obligatoire.",
            'password.required' => 'Le mot de passe est obligatoire.',
        ];
    }
}
