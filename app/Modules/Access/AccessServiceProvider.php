<?php

namespace App\Modules\Access;

use App\Modules\Access\Services\GestionUtilisateurs;
use App\Modules\Access\Services\JournalApplicatif;
use App\Modules\Access\Services\ServiceAnnuaire;
use App\Modules\Access\Services\ServiceAuthentification;
use App\Modules\Access\Services\ServiceDroits;
use App\Modules\Access\Services\ServiceUtilisateurCourant;
use App\Modules\Shared\Contracts\Authentifier;
use App\Modules\Shared\Contracts\EnregistreurJournal;
use App\Modules\Shared\Contracts\UtilisateurCourant;
use App\Modules\Shared\Contracts\UtilisateurParIdentifiantTechnique;
use App\Modules\Shared\Contracts\UtilisateursActifs;
use App\Modules\Shared\Contracts\VerifierDroit;
use App\Modules\Shared\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AccessServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(VerifierDroit::class, ServiceDroits::class);
        $this->app->singleton(Authentifier::class, ServiceAuthentification::class);
        $this->app->singleton(UtilisateurCourant::class, ServiceUtilisateurCourant::class);
        $this->app->singleton(ServiceAnnuaire::class);
        $this->app->singleton(UtilisateurParIdentifiantTechnique::class, ServiceAnnuaire::class);
        $this->app->singleton(UtilisateursActifs::class, ServiceAnnuaire::class);
        $this->app->singleton(GestionUtilisateurs::class);

        // Provisoire : remplacé par le module M11 (journal d'historique).
        $this->app->singleton(EnregistreurJournal::class, JournalApplicatif::class);
    }

    public function boot(): void
    {
        // Une habilitation par permission : utilisable dans les gabarits (@can('lieux.gerer')).
        foreach (Permission::cases() as $permission) {
            Gate::define($permission->value, function (Utilisateur $utilisateur) use ($permission) {
                return $this->app->make(VerifierDroit::class)->autorise($utilisateur->versDto(), $permission);
            });
        }
    }
}
