<?php

namespace App\Console\Commands;

use App\Modules\Access\Utilisateur;
use App\Modules\Shared\Role;
use App\Modules\Shared\Rules\MotDePasseConforme;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

/**
 * Amorçage du premier administrateur (ADR-004). Le mot de passe est lu dans l'environnement
 * (EVENTIA_ADMIN_MOT_DE_PASSE) : il n'est jamais écrit dans le dépôt ni passé en argument.
 */
class CreerAdministrateur extends Command
{
    protected $signature = 'eventia:creer-administrateur {identifiant : Identifiant de connexion} {nom : Nom affiché}';

    protected $description = "Crée un compte administrateur (mot de passe lu dans la variable d'environnement EVENTIA_ADMIN_MOT_DE_PASSE)";

    public function handle(): int
    {
        $motDePasse = (string) env('EVENTIA_ADMIN_MOT_DE_PASSE', '');
        if ($motDePasse === '') {
            $this->error('Définissez la variable d\'environnement EVENTIA_ADMIN_MOT_DE_PASSE (12 caractères minimum).');

            return self::FAILURE;
        }

        $identifiant = Utilisateur::normaliserIdentifiant($this->argument('identifiant'));

        $validation = Validator::make(
            ['identifiant' => $identifiant, 'nom' => $this->argument('nom'), 'mot_de_passe' => $motDePasse],
            [
                'identifiant' => ['required', 'max:100', 'unique:utilisateurs,identifiant'],
                'nom' => ['required', 'max:100'],
                'mot_de_passe' => [new MotDePasseConforme],
            ],
        );

        if ($validation->fails()) {
            foreach ($validation->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        Utilisateur::create([
            'nom' => $this->argument('nom'),
            'identifiant' => $identifiant,
            'mot_de_passe' => $motDePasse,
            'role' => Role::Administrateur,
            'actif' => true,
        ]);

        $this->info("Administrateur « {$identifiant} » créé.");

        return self::SUCCESS;
    }
}
