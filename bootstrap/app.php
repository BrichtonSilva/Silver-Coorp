<?php

use App\Http\Middleware\ComptesActifs;
use App\Http\Middleware\EnTetesSecurite;
use App\Http\Middleware\VerifierPermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Refus par défaut (R01) : un visiteur non connecté est renvoyé vers l'écran de connexion.
        $middleware->redirectGuestsTo(fn (Request $request) => route('connexion', ['expiree' => 1]));

        $middleware->alias(['permission' => VerifierPermission::class]);

        $middleware->append(EnTetesSecurite::class);
        $middleware->appendToGroup('web', ComptesActifs::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
