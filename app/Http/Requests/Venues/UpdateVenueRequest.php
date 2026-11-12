<?php

namespace App\Http\Requests\Venues;

use App\Models\Venue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVenueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'capacity' => ['required', 'integer', 'min:1'],
            'type' => ['required', Rule::in(Venue::TYPES)],
            'rental_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'capacity.required' => 'La capacité est obligatoire.',
            'capacity.integer' => 'La capacité doit être un nombre entier.',
            'capacity.min' => 'La capacité doit être d\'au moins 1 personne.',
            'type.required' => 'Le type est obligatoire.',
            'type.in' => 'Le type doit être interne ou externe.',
            'rental_price.required' => 'Le prix de location est obligatoire.',
            'rental_price.numeric' => 'Le prix de location doit être un nombre.',
            'rental_price.min' => 'Le prix de location ne peut pas être négatif.',
        ];
    }
}
