<?php

namespace App\Modules\Shared\Contracts;

use App\Modules\Shared\Dto\ResultatConnexion;

interface Authentifier
{
    /** Vérifie les identifiants et ouvre la session (F1). */
    public function tenter(string $identifiant, string $motDePasse, string $adresse): ResultatConnexion;
}
