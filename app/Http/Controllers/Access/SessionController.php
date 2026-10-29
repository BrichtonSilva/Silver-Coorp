<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class SessionController extends Controller
{
    /** Prolonger la session après l'avertissement d'expiration (WCAG 2.2.1) : la requête suffit à renouveler l'activité. */
    public function prolonger(): Response
    {
        return response()->noContent();
    }
}
