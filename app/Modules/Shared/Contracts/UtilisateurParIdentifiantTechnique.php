<?php

namespace App\Modules\Shared\Contracts;

use App\Modules\Shared\Dto\UtilisateurDTO;

interface UtilisateurParIdentifiantTechnique
{
    public function obtenir(int $id): ?UtilisateurDTO;

    /** @param  list<int>  $ids  @return array<int, UtilisateurDTO> indexé par identifiant */
    public function obtenirPlusieurs(array $ids): array;
}
