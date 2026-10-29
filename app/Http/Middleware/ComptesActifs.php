<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** R03 : un compte désactivé pendant une session ouverte est déconnecté à la requête suivante. */
class ComptesActifs
{
    public function handle(Request $request, Closure $next): Response
    {
        $utilisateur = Auth::user();

        if ($utilisateur !== null && ! $utilisateur->actif) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('connexion')
                ->withErrors(['connexion' => 'Votre compte a été désactivé. Contactez un administrateur.']);
        }

        return $next($request);
    }
}
