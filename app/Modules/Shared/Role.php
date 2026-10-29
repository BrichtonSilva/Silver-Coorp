<?php

namespace App\Modules\Shared;

/** Les trois rôles de l'application (R07). */
enum Role: string
{
    case Administrateur = 'administrateur';
    case ChargeEvenements = 'charge_evenements';
    case Consultant = 'consultant';

    public function libelle(): string
    {
        return match ($this) {
            self::Administrateur => 'Administrateur',
            self::ChargeEvenements => "Chargé d'événements",
            self::Consultant => 'Consultant',
        };
    }
}
