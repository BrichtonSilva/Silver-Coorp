<?php

namespace App\Modules\Shared\Contracts;

use App\Modules\Shared\Dto\UtilisateurDTO;
use App\Modules\Shared\Role;

interface UtilisateursActifs
{
    /**
     * Utilisateurs actifs, triés par nom (liste des responsables possibles).
     *
     * @param  list<Role>|null  $roles  filtre facultatif (null = tous les rôles)
     * @return list<UtilisateurDTO>
     */
    public function liste(?array $roles = null): array;
}
