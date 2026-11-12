<?php

namespace App\Http\Requests\Providers;

use App\Models\Provider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(Provider::CATEGORIES)],
            'contact' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'indicative_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'category.required' => 'La catégorie est obligatoire.',
            'category.in' => 'La catégorie doit être traiteur, sonorisation, décoration, photographe ou autre.',
            'email.email' => "L'adresse e-mail n'est pas valide.",
            'indicative_price.numeric' => "Le prix indicatif doit être un nombre.",
            'indicative_price.min' => "Le prix indicatif doit être positif.",
        ];
    }
}
