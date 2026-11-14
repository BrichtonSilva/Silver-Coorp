<?php

namespace App\Http\Controllers\Access;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

/**
 * « Mon compte » : chaque utilisateur connecté (tous rôles) consulte son profil
 * et change son propre mot de passe.
 */
class AccountController extends Controller
{
    public function edit(Request $request): View
    {
        return view('access.account', ['user' => $request->user()]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required' => 'Entrez votre mot de passe actuel.',
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
            'password.required' => 'Entrez un nouveau mot de passe.',
            'password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation ne correspond pas au nouveau mot de passe.',
            'password.different' => 'Le nouveau mot de passe doit être différent de l\'actuel.',
        ]);

        /** @var User $user */
        $user = $request->user();
        $user->password = $data['password']; // haché par le cast du modèle
        $user->save();

        // Nouvelle session après un changement de secret
        $request->session()->regenerate();

        return redirect()->route('account.edit')->with('success', 'Votre mot de passe a bien été modifié.');
    }
}
