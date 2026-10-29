<?php

namespace App\Http\Middleware;

use App\Modules\Shared\Contracts\UtilisateurCourant;
use App\Modules\Shared\Contracts\VerifierDroit;
use App\Modules\Shared\Permission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Première barrière des droits (R08) : « permission:utilisateurs.gerer ». Une valeur inconnue est refusée. */
class VerifierPermission
{
    public function __construct(
        private readonly VerifierDroit $droits,
        private readonly UtilisateurCourant $courant,
    ) {}

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $attendue = Permission::tryFrom($permission);

        if ($attendue === null || ! $this->droits->autorise($this->courant->obtenir(), $attendue)) {
            abort(403);
        }

        return $next($request);
    }
}
