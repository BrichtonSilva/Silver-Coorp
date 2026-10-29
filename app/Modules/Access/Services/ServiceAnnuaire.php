<?php

namespace App\Modules\Access\Services;

use App\Modules\Access\Utilisateur;
use App\Modules\Shared\Contracts\UtilisateurParIdentifiantTechnique;
use App\Modules\Shared\Contracts\UtilisateursActifs;
use App\Modules\Shared\Dto\UtilisateurDTO;
use App\Modules\Shared\Role;

/** Services de lecture exposés aux autres modules. */
final class ServiceAnnuaire implements UtilisateurParIdentifiantTechnique, UtilisateursActifs
{
    public function obtenir(int $id): ?UtilisateurDTO
    {
        return Utilisateur::find($id)?->versDto();
    }

    public function obtenirPlusieurs(array $ids): array
    {
        return Utilisateur::whereIn('id', $ids)->get()
            ->mapWithKeys(fn (Utilisateur $u) => [$u->id => $u->versDto()])
            ->all();
    }

    public function liste(?array $roles = null): array
    {
        $requete = Utilisateur::where('actif', true)->orderBy('nom');

        if ($roles !== null) {
            $requete->whereIn('role', array_map(fn (Role $r) => $r->value, $roles));
        }

        return $requete->get()->map(fn (Utilisateur $u) => $u->versDto())->values()->all();
    }
}
