<?php

namespace Tests\Unit\Access;

use App\Modules\Access\MatriceDroits;
use App\Modules\Shared\Permission;
use App\Modules\Shared\Role;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** RG17 / R08 : les 24 combinaisons permission x rôle du cahier des charges §3. */
class MatriceDroitsTest extends TestCase
{
    public static function combinaisons(): array
    {
        // [permission, administrateur, chargé d'événements, consultant]
        $tableau = [
            [Permission::Consulter, true, true, true],
            [Permission::Exporter, true, true, true],
            [Permission::EvenementsEcrire, true, true, false],
            [Permission::AffectationsChecklistGerer, true, true, false],
            [Permission::ClientsPrestatairesGerer, true, true, false],
            [Permission::JournalConsulter, true, true, false],
            [Permission::LieuxGerer, true, false, false],
            [Permission::UtilisateursGerer, true, false, false],
        ];

        $cas = [];
        foreach ($tableau as [$permission, $admin, $charge, $consultant]) {
            $cas["{$permission->value} / administrateur"] = [Role::Administrateur, $permission, $admin];
            $cas["{$permission->value} / charge"] = [Role::ChargeEvenements, $permission, $charge];
            $cas["{$permission->value} / consultant"] = [Role::Consultant, $permission, $consultant];
        }

        return $cas;
    }

    #[DataProvider('combinaisons')]
    public function test_RG17_matrice_des_droits(Role $role, Permission $permission, bool $attendu): void
    {
        $this->assertSame($attendu, MatriceDroits::autorise($role, $permission));
    }

    public function test_R09_le_consultant_n_a_aucune_ecriture(): void
    {
        $ecritures = array_filter(
            MatriceDroits::permissionsDe(Role::Consultant),
            fn (Permission $p) => ! in_array($p, [Permission::Consulter, Permission::Exporter], true),
        );

        $this->assertSame([], $ecritures);
    }

    public function test_R07_les_trois_roles_exactement(): void
    {
        $this->assertSame(['administrateur', 'charge_evenements', 'consultant'], array_map(fn (Role $r) => $r->value, Role::cases()));
    }
}
