<?php

namespace Database\Factories;

use App\Modules\Access\Utilisateur;
use App\Modules\Shared\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Utilisateur> */
class UtilisateurFactory extends Factory
{
    protected $model = Utilisateur::class;

    public const MOT_DE_PASSE = 'Mot-de-passe-test-1';

    public function definition(): array
    {
        return [
            'nom' => fake()->name(),
            'identifiant' => fake()->unique()->userName().fake()->unique()->numberBetween(1, 99999),
            'mot_de_passe' => self::MOT_DE_PASSE,
            'role' => Role::Consultant,
            'actif' => true,
        ];
    }

    public function administrateur(): static
    {
        return $this->state(['role' => Role::Administrateur]);
    }

    public function chargeEvenements(): static
    {
        return $this->state(['role' => Role::ChargeEvenements]);
    }

    public function consultant(): static
    {
        return $this->state(['role' => Role::Consultant]);
    }

    public function inactif(): static
    {
        return $this->state(['actif' => false]);
    }
}
