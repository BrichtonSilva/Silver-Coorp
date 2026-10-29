<?php

namespace App\Http\Requests\Access;

use App\Modules\Access\Utilisateur;
use App\Modules\Shared\Role;
use App\Modules\Shared\Rules\MotDePasseConforme;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Création et modification d'un compte (R02, R06, R07). Le droit est contrôlé par le middleware puis par le service. */
class UtilisateurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('identifiant'))) {
            $this->merge(['identifiant' => Utilisateur::normaliserIdentifiant($this->input('identifiant'))]);
        }
    }

    public function rules(): array
    {
        $cible = $this->route('utilisateur');
        $unique = Rule::unique('utilisateurs', 'identifiant');
        if ($cible instanceof Utilisateur) {
            $unique->ignore($cible->id);
        }

        $regles = [
            'nom' => ['required', 'string', 'max:100'],
            'identifiant' => ['required', 'string', 'max:100', 'regex:/^[\p{L}\p{N}._@-]+$/u', $unique],
            'role' => ['required', Rule::enum(Role::class)],
        ];

        if (! $cible instanceof Utilisateur) {
            $regles['mot_de_passe'] = ['required', 'string', 'max:200', new MotDePasseConforme];
        }

        return $regles;
    }

    public function messages(): array
    {
        return [
            'identifiant.regex' => "L'identifiant ne peut contenir que des lettres, chiffres et les caractères . _ @ -",
            'identifiant.unique' => 'Cet identifiant est déjà utilisé.',
        ];
    }
}
