<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Http\Requests\Access\ConnexionRequest;
use App\Modules\Shared\Contracts\Authentifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConnexionController extends Controller
{
    public const MESSAGE_ECHEC = 'Identifiant ou mot de passe incorrect.';

    public function afficher(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('tableau-de-bord');
        }

        return view('access.connexion', ['expiree' => $request->boolean('expiree')]);
    }

    public function connecter(ConnexionRequest $request, Authentifier $authentification)
    {
        $resultat = $authentification->tenter(
            $request->input('identifiant'),
            $request->input('mot_de_passe'),
            $request->ip() ?? '0.0.0.0',
        );

        if ($resultat->estBloque()) {
            $minutes = (int) ceil($resultat->secondesAttente / 60);

            return response()->view('access.connexion', [
                'expiree' => false,
                'identifiant' => $request->input('identifiant'),
                'erreurConnexion' => "Trop de tentatives. Réessayez dans {$minutes} minute".($minutes > 1 ? 's' : '').'.',
            ], 429);
        }

        if (! $resultat->succes) {
            // Message unique : ne révèle ni l'existence du compte, ni son état (R04).
            return redirect()->route('connexion')
                ->withInput($request->only('identifiant'))
                ->withErrors(['connexion' => self::MESSAGE_ECHEC]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('tableau-de-bord'));
    }

    public function deconnecter(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('connexion')->with('statut', 'Vous êtes déconnecté.');
    }
}
