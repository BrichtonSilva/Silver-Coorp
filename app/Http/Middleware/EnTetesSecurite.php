<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** En-têtes de sécurité posés sur toutes les réponses (OWASP A05). */
class EnTetesSecurite
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('csp_nonce', $nonce);

        $reponse = $next($request);

        $reponse->headers->set('Content-Security-Policy',
            "default-src 'self'; script-src 'self' 'nonce-{$nonce}'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; "
            ."form-action 'self'; frame-ancestors 'none'; base-uri 'self'");
        $reponse->headers->set('X-Content-Type-Options', 'nosniff');
        $reponse->headers->set('Referrer-Policy', 'same-origin');
        $reponse->headers->set('X-Frame-Options', 'DENY');

        if ($request->isSecure()) {
            $reponse->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $reponse;
    }
}
