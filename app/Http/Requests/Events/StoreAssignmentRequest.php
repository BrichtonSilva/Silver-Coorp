<?php

namespace App\Http\Requests\Events;

use App\Models\EventProviderAssignment;
use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->canWrite();
    }

    public function rules(): array
    {
        return [
            'provider_id' => ['required', 'exists:providers,id'],
            'cost' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:' . implode(',', EventProviderAssignment::STATUSES)],
        ];
    }

    public function messages(): array
    {
        return [
            'provider_id.required' => 'Le prestataire est obligatoire.',
            'provider_id.exists' => 'Le prestataire sélectionné n\'existe pas.',
            'cost.required' => 'Le coût est obligatoire.',
            'cost.numeric' => 'Le coût doit être un nombre.',
            'cost.min' => 'Le coût doit être supérieur ou égal à 0.',
            'status.required' => 'Le statut est obligatoire.',
            'status.in' => 'Le statut sélectionné n\'est pas valide.',
        ];
    }
}
