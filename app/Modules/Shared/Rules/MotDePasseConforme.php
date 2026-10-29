<?php

namespace App\Modules\Shared\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Politique de mot de passe (Q4) : 12 caractères minimum, mots de passe courants refusés.
 * Aucune vérification en ligne : l'application n'appelle aucun service externe (ADR-002).
 */
final class MotDePasseConforme implements ValidationRule
{
    public const LONGUEUR_MIN = 12;

    private const COURANTS = [
        'password1234', 'motdepasse123', 'motdepasse1234', '123456789012', 'azertyuiop12',
        'azertyuiopqs', 'qwertyuiop12', 'passw0rd1234', 'administrateur', 'administrator',
        'eventia12345', 'eventia2026', 'bienvenue2026', 'changeme1234', 'welcome12345',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $valeur = (string) $value;

        if (mb_strlen($valeur) < self::LONGUEUR_MIN) {
            $fail('Le mot de passe doit contenir au moins '.self::LONGUEUR_MIN.' caractères.');

            return;
        }

        if (in_array(mb_strtolower($valeur), self::COURANTS, true) || count(array_unique(mb_str_split($valeur))) < 4) {
            $fail('Ce mot de passe est trop courant ou trop simple. Choisissez-en un autre.');
        }
    }
}
