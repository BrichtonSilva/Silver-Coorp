<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Http\Requests\Access\MotDePasseRequest;
use App\Http\Requests\Access\UtilisateurRequest;
use App\Modules\Access\Services\GestionUtilisateurs;
use App\Modules\Access\Utilisateur;
use App\Modules\Shared\Contracts\UtilisateurCourant;
use App\Modules\Shared\Role;

class UtilisateurController extends Controller
{
    public function __construct(
        private readonly GestionUtilisateurs $gestion,
        private readonly UtilisateurCourant $courant,
    ) {}

    public function index()
    {
        return view('access.utilisateurs.index', [
            'utilisateurs' => Utilisateur::orderBy('nom')->paginate(15),
        ]);
    }

    public function creer()
    {
        return view('access.utilisateurs.formulaire', ['utilisateur' => new Utilisateur(['role' => Role::ChargeEvenements]), 'roles' => Role::cases()]);
    }

    public function enregistrer(UtilisateurRequest $request)
    {
        $this->gestion->creer($this->courant->obtenir(), $request->validated());

        return redirect()->route('utilisateurs.index')->with('statut', 'Le compte a été créé.');
    }

    public function modifier(Utilisateur $utilisateur)
    {
        return view('access.utilisateurs.formulaire', ['utilisateur' => $utilisateur, 'roles' => Role::cases()]);
    }

    public function mettreAJour(UtilisateurRequest $request, Utilisateur $utilisateur)
    {
        $this->gestion->modifier($this->courant->obtenir(), $utilisateur, $request->validated());

        return redirect()->route('utilisateurs.index')->with('statut', 'Le compte a été modifié.');
    }

    public function desactiver(Utilisateur $utilisateur)
    {
        $this->gestion->desactiver($this->courant->obtenir(), $utilisateur);

        return redirect()->route('utilisateurs.index')->with('statut', 'Le compte a été désactivé.');
    }

    public function reactiver(Utilisateur $utilisateur)
    {
        $this->gestion->reactiver($this->courant->obtenir(), $utilisateur);

        return redirect()->route('utilisateurs.index')->with('statut', 'Le compte a été réactivé.');
    }

    public function formulaireMotDePasse(Utilisateur $utilisateur)
    {
        return view('access.utilisateurs.mot-de-passe', ['utilisateur' => $utilisateur]);
    }

    public function reinitialiserMotDePasse(MotDePasseRequest $request, Utilisateur $utilisateur)
    {
        $this->gestion->reinitialiserMotDePasse($this->courant->obtenir(), $utilisateur, $request->validated('mot_de_passe'));

        return redirect()->route('utilisateurs.index')->with('statut', 'Le mot de passe a été réinitialisé. Les sessions du compte ont été fermées.');
    }
}
