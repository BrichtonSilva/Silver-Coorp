<?php

namespace App\Http\Requests\Access;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'login' => ['required', 'string', 'max:255', function ($attribute, $value, $fail) use ($userId) {
                $exists = User::whereRaw('LOWER(login) = ?', [strtolower(trim($value))])
                    ->where('id', '!=', $userId)
                    ->exists();
                if ($exists) {
                    $fail('Cet identifiant est déjà utilisé.');
                }
            }],
            'email' => ['nullable', 'email', 'max:255'],
            'role' => ['required', Rule::in(User::ROLES)],
            'is_active' => ['boolean'],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'login.required' => "L'identifiant est obligatoire.",
            'role.required' => 'Le rôle est obligatoire.',
            'role.in' => 'Le rôle doit être administrateur, chargé d\'événements ou consultant.',
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
