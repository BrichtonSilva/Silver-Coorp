<?php

namespace App\Http\Requests\Access;

use Illuminate\Foundation\Http\FormRequest;

class ConnexionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identifiant' => ['required', 'string', 'max:100'],
            'mot_de_passe' => ['required', 'string', 'max:200'],
        ];
    }
}
