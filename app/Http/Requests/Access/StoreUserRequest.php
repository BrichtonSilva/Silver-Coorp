<?php

namespace App\Http\Requests\Access;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'login' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) {
                $exists = User::whereRaw('LOWER(login) = ?', [strtolower(trim($value))])->exists();
                if ($exists) {
                    $fail('Cet identifiant est déjà utilisé.');
                }
            }],
            'email' => ['nullable', 'email', 'max:255'],
            'role' => ['required', Rule::in(User::ROLES)],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'login.required' => "L'identifiant est obligatoire.",
            'role.required' => 'Le rôle est obligatoire.',
            'role.in' => 'Le rôle doit être administrateur, chargé d\'événements ou consultant.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit comporter au moins 8 caractères.',
            'email.email' => "L'adresse e-mail n'est pas valide.",
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active') && !is_bool($this->is_active)) {
            $this->merge(['is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN)]);
        }
    }
}
