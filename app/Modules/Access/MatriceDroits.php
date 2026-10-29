<?php

namespace App\Modules\Access;

use App\Modules\Shared\Permission;
use App\Modules\Shared\Role;

/**
 * Matrice des droits (cahier des charges §3, RG17) : source unique de vérité.
 * Ne jamais recopier ces règles dans un gabarit ou un contrôleur.
 */
final class MatriceDroits
{
    /** @return list<Permission> */
    public static function permissionsDe(Role $role): array
    {
        return match ($role) {
            Role::Administrateur => Permission::cases(),
            Role::ChargeEvenements => [
                Permission::Consulter,
                Permission::Exporter,
                Permission::EvenementsEcrire,
                Permission::AffectationsChecklistGerer,
                Permission::ClientsPrestatairesGerer,
                Permission::JournalConsulter,
            ],
            Role::Consultant => [
                Permission::Consulter,
                Permission::Exporter,
            ],
        };
    }

    public static function autorise(Role $role, Permission $permission): bool
    {
        return in_array($permission, self::permissionsDe($role), true);
    }
}
