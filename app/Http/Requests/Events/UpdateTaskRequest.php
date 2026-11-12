<?php

namespace App\Http\Requests\Events;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->canWrite();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'responsible' => ['nullable', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'L\'intitulé de la tâche est obligatoire.',
            'title.max' => 'L\'intitulé ne peut pas dépasser 255 caractères.',
            'due_date.date' => 'La date d\'échéance doit être une date valide.',
        ];
    }
}
