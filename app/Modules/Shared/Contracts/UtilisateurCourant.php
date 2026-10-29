<?php

namespace App\Modules\Shared\Contracts;

use App\Modules\Shared\Dto\UtilisateurDTO;

interface UtilisateurCourant
{
    public function obtenir(): ?UtilisateurDTO;
}
