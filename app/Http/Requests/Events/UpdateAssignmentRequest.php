<?php

namespace App\Http\Requests\Events;

use App\Models\EventProviderAssignment;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->canWrite();
    }

    public function rules(): array
    {
        return [
            'cost' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:' . implode(',', EventProviderAssignment::STATUSES)],
        ];
    }

    public function messages(): array
    {
        return [
            'cost.required' => 'Le coût est obligatoire.',
            'cost.numeric' => 'Le coût doit être un nombre.',
            'cost.min' => 'Le coût doit être supérieur ou égal à 0.',
            'status.required' => 'Le statut est obligatoire.',
            'status.in' => 'Le statut sélectionné n\'est pas valide.',
        ];
    }
}
