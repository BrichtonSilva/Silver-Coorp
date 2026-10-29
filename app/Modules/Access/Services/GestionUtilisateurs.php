<?php

namespace App\Modules\Access\Services;

use App\Modules\Access\Utilisateur;
use App\Modules\Shared\Contracts\EnregistreurJournal;
use App\Modules\Shared\Contracts\VerifierDroit;
use App\Modules\Shared\Dto\UtilisateurDTO;
use App\Modules\Shared\Permission;
use App\Modules\Shared\Role;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Cas d'usage de gestion des comptes (F2, US2). Chaque méthode revérifie le droit (double barrière, R08).
 */
final class GestionUtilisateurs
{
    public function __construct(
        private readonly VerifierDroit $droits,
        private readonly EnregistreurJournal $journal,
    ) {}

    /** @param array{nom: string, identifiant: string, mot_de_passe: string, role: string} $donnees */
    public function creer(UtilisateurDTO $acteur, array $donnees): Utilisateur
    {
        $this->droits->exiger($acteur, Permission::UtilisateursGerer);

        try {
            $utilisateur = Utilisateur::create([
                'nom' => $donnees['nom'],
                'identifiant' => $donnees['identifiant'],
                'mot_de_passe' => $donnees['mot_de_passe'],
                'role' => $donnees['role'],
                'actif' => true,
            ]);
        } catch (UniqueConstraintViolationException) {
            throw $this->identifiantDejaUtilise();
        }

        $this->journal->enregistrer('compte.cree', $acteur, null, ['compte_id' => $utilisateur->id, 'role' => $utilisateur->role->value]);

        return $utilisateur;
    }

    /** @param array{nom: string, identifiant: string, role: string} $donnees */
    public function modifier(UtilisateurDTO $acteur, Utilisateur $cible, array $donnees): Utilisateur
    {
        $this->droits->exiger($acteur, Permission::UtilisateursGerer);

        $nouveauRole = Role::from($donnees['role']);

        try {
            DB::transaction(function () use ($cible, $donnees, $nouveauRole) {
                $verrouille = Utilisateur::whereKey($cible->id)->lockForUpdate()->firstOrFail();

                if ($verrouille->role === Role::Administrateur && $verrouille->actif && $nouveauRole !== Role::Administrateur) {
                    $this->garantirUnAutreAdministrateurActif($verrouille, 'role', 'Le dernier administrateur actif ne peut pas être rétrogradé.');
                }

                $verrouille->update([
                    'nom' => $donnees['nom'],
                    'identifiant' => $donnees['identifiant'],
                    'role' => $nouveauRole,
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            throw $this->identifiantDejaUtilise();
        }

        $cible->refresh();
        $this->journal->enregistrer('compte.modifie', $acteur, null, ['compte_id' => $cible->id, 'role' => $cible->role->value]);

        return $cible;
    }

    public function desactiver(UtilisateurDTO $acteur, Utilisateur $cible): void
    {
        $this->droits->exiger($acteur, Permission::UtilisateursGerer);

        if ($acteur->id === $cible->id) {
            throw ValidationException::withMessages(['compte' => 'Vous ne pouvez pas désactiver votre propre compte.']);
        }

        DB::transaction(function () use ($cible) {
            $verrouille = Utilisateur::whereKey($cible->id)->lockForUpdate()->firstOrFail();

            if ($verrouille->role === Role::Administrateur && $verrouille->actif) {
                $this->garantirUnAutreAdministrateurActif($verrouille, 'compte', 'Le dernier administrateur actif ne peut pas être désactivé.');
            }

            $verrouille->update(['actif' => false]);
        });

        $this->fermerLesSessions($cible);
        $this->journal->enregistrer('compte.desactive', $acteur, null, ['compte_id' => $cible->id]);
    }

    public function reactiver(UtilisateurDTO $acteur, Utilisateur $cible): void
    {
        $this->droits->exiger($acteur, Permission::UtilisateursGerer);

        $cible->update(['actif' => true]);
        $this->journal->enregistrer('compte.reactive', $acteur, null, ['compte_id' => $cible->id]);
    }

    public function reinitialiserMotDePasse(UtilisateurDTO $acteur, Utilisateur $cible, string $nouveauMotDePasse): void
    {
        $this->droits->exiger($acteur, Permission::UtilisateursGerer);

        $cible->update(['mot_de_passe' => $nouveauMotDePasse]);
        $this->fermerLesSessions($cible);

        // Le mot de passe n'est jamais journalisé (R06, R13).
        $this->journal->enregistrer('compte.mot_de_passe_reinitialise', $acteur, null, ['compte_id' => $cible->id]);
    }

    /** R15 : doit rester au moins un administrateur actif en dehors de la cible (verrou pris dans la transaction appelante). */
    private function garantirUnAutreAdministrateurActif(Utilisateur $cible, string $champ, string $message): void
    {
        $autres = Utilisateur::where('role', Role::Administrateur->value)
            ->where('actif', true)
            ->where('id', '!=', $cible->id)
            ->lockForUpdate()
            ->pluck('id');

        if ($autres->isEmpty()) {
            throw ValidationException::withMessages([$champ => $message]);
        }
    }

    /** R03 : un compte désactivé (ou dont le mot de passe change) perd ses sessions ouvertes. */
    private function fermerLesSessions(Utilisateur $cible): void
    {
        DB::table('sessions')->where('user_id', $cible->id)->delete();
    }

    private function identifiantDejaUtilise(): ValidationException
    {
        return ValidationException::withMessages(['identifiant' => 'Cet identifiant est déjà utilisé.']);
    }
}
