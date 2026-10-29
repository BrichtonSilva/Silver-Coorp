<?php

namespace App\Modules\Access\Services;

use App\Modules\Access\Utilisateur;
use App\Modules\Shared\Contracts\Authentifier;
use App\Modules\Shared\Dto\ResultatConnexion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Authentification (F1, US1).
 *  - R04 : un seul résultat d'échec, quel que soit le motif, et un coût de calcul comparable.
 *  - R05 : 5 essais par couple identifiant/adresse, puis blocage de 1 à 15 minutes (durée doublée à chaque nouvel échec).
 *  - R03 : un compte désactivé ne se connecte pas.
 */
final class ServiceAuthentification implements Authentifier
{
    public const ESSAIS_MAX = 5;

    private const BLOCAGE_MIN = 60;

    private const BLOCAGE_MAX = 900;

    private static ?string $hachageFactice = null;

    public function tenter(string $identifiant, string $motDePasse, string $adresse): ResultatConnexion
    {
        $identifiant = Utilisateur::normaliserIdentifiant($identifiant);
        $cle = hash('sha256', $identifiant.'|'.$adresse);
        $maintenant = now()->getTimestamp();

        $bloqueJusqua = (int) Cache::get("connexion:blocage:$cle", 0);
        if ($bloqueJusqua > $maintenant) {
            return ResultatConnexion::bloque($bloqueJusqua - $maintenant);
        }

        $utilisateur = Utilisateur::where('identifiant', $identifiant)->first();

        // Comparaison faite même si le compte n'existe pas, pour ne pas révéler son existence par le temps de réponse.
        $motDePasseValide = Hash::check($motDePasse, $utilisateur?->mot_de_passe ?? $this->hachageFactice());

        if ($utilisateur !== null && $utilisateur->actif && $motDePasseValide) {
            Cache::forget("connexion:essais:$cle");
            Cache::forget("connexion:blocage:$cle");

            Auth::login($utilisateur);
            $utilisateur->forceFill(['derniere_connexion' => now()])->save();

            return ResultatConnexion::reussi();
        }

        $motif = match (true) {
            $utilisateur === null => 'identifiant_inconnu',
            ! $utilisateur->actif => 'compte_desactive',
            default => 'mot_de_passe_incorrect',
        };
        $this->enregistrerEchec($cle, $adresse, $motif);

        return ResultatConnexion::echec();
    }

    private function enregistrerEchec(string $cle, string $adresse, string $motif): void
    {
        $essais = (int) Cache::get("connexion:essais:$cle", 0) + 1;
        Cache::put("connexion:essais:$cle", $essais, 3600);

        if ($essais >= self::ESSAIS_MAX) {
            $delai = min(self::BLOCAGE_MAX, self::BLOCAGE_MIN * (2 ** ($essais - self::ESSAIS_MAX)));
            Cache::put("connexion:blocage:$cle", now()->getTimestamp() + $delai, $delai + 3600);
        }

        // Journal technique : ni mot de passe, ni identifiant en clair (R13) ; une empreinte courte suffit au diagnostic.
        Log::warning('Échec de connexion', [
            'empreinte' => substr($cle, 0, 12),
            'adresse' => $adresse,
            'motif' => $motif,
            'essais' => $essais,
        ]);
    }

    private function hachageFactice(): string
    {
        return self::$hachageFactice ??= Hash::make(Str::random(40));
    }
}
