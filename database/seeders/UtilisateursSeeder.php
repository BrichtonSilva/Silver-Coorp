<?php

namespace Database\Seeders;

use App\Modules\Access\Utilisateur;
use App\Modules\Shared\Role;
use Illuminate\Database\Seeder;

/** Comptes de démonstration (développement et préproduction uniquement). */
class UtilisateursSeeder extends Seeder
{
    public const MOT_DE_PASSE_DEMO = 'Demo-Eventia-2026';

    public function run(): void
    {
        $comptes = [
            ['admin', 'Awa Administratrice', Role::Administrateur, true],
            ['charge', "Marc Chargé d'événements", Role::ChargeEvenements, true],
            ['charge2', "Nadia Chargée d'événements", Role::ChargeEvenements, true],
            ['consultant', 'Paul Consultant', Role::Consultant, true],
            ['ancien', 'Ancien Collaborateur', Role::ChargeEvenements, false],
        ];

        foreach ($comptes as [$identifiant, $nom, $role, $actif]) {
            Utilisateur::updateOrCreate(
                ['identifiant' => $identifiant],
                ['nom' => $nom, 'mot_de_passe' => self::MOT_DE_PASSE_DEMO, 'role' => $role, 'actif' => $actif],
            );
        }
    }
}
