<?php

namespace App\Modules\Access\Services;

use App\Modules\Access\Utilisateur;
use App\Modules\Shared\Contracts\UtilisateurCourant;
use App\Modules\Shared\Dto\UtilisateurDTO;
use Illuminate\Support\Facades\Auth;

final class ServiceUtilisateurCourant implements UtilisateurCourant
{
    public function obtenir(): ?UtilisateurDTO
    {
        $utilisateur = Auth::user();

        return $utilisateur instanceof Utilisateur ? $utilisateur->versDto() : null;
    }
}
