<?php

namespace App\Modules\Shared\Contracts;

use App\Modules\Shared\Dto\UtilisateurDTO;
use App\Modules\Shared\Permission;

interface VerifierDroit
{
    public function autorise(?UtilisateurDTO $utilisateur, Permission $permission): bool;

    /** Seconde barrière, dans les services métier : lève une exception (403) si le droit manque. */
    public function exiger(?UtilisateurDTO $utilisateur, Permission $permission): void;
}
