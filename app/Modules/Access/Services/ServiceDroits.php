<?php

namespace App\Modules\Access\Services;

use App\Modules\Access\MatriceDroits;
use App\Modules\Shared\Contracts\VerifierDroit;
use App\Modules\Shared\Dto\UtilisateurDTO;
use App\Modules\Shared\Permission;
use Illuminate\Auth\Access\AuthorizationException;

final class ServiceDroits implements VerifierDroit
{
    public function autorise(?UtilisateurDTO $utilisateur, Permission $permission): bool
    {
        return $utilisateur !== null
            && $utilisateur->actif
            && MatriceDroits::autorise($utilisateur->role, $permission);
    }

    public function exiger(?UtilisateurDTO $utilisateur, Permission $permission): void
    {
        if (! $this->autorise($utilisateur, $permission)) {
            throw new AuthorizationException("Vous n'avez pas le droit d'effectuer cette action.");
        }
    }
}
