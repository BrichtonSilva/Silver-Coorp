<?php

namespace App\Http\Requests\Access;

use App\Modules\Shared\Rules\MotDePasseConforme;
use Illuminate\Foundation\Http\FormRequest;

class MotDePasseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['mot_de_passe' => ['required', 'string', 'max:200', new MotDePasseConforme]];
    }
}
