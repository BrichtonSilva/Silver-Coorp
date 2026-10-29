<?php

namespace Tests\Feature\Access;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Tests\TestCase;

/** R01 : refus par défaut. Une route sans authentification ni permission déclarée fait échouer ce test. */
class RoutesProtegeesTest extends TestCase
{
    private const ROUTES_PUBLIQUES = ['/', 'connexion', 'up'];

    public function test_R01_toute_route_a_une_authentification_et_une_permission(): void
    {
        $sansProtection = [];

        /** @var Route $route */
        foreach (RouteFacade::getRoutes() as $route) {
            $uri = $route->uri();

            if (in_array($uri, self::ROUTES_PUBLIQUES, true) || str_starts_with($uri, 'storage/')) {
                continue;
            }

            $middlewares = $route->gatherMiddleware();
            $authentifiee = in_array('auth', $middlewares, true);
            $permission = count(array_filter($middlewares, fn ($m) => is_string($m) && str_starts_with($m, 'permission:'))) > 0;

            if (! $authentifiee || ! $permission) {
                $sansProtection[] = implode('|', $route->methods()).' '.$uri;
            }
        }

        $this->assertSame([], $sansProtection, 'Routes sans authentification ou sans permission déclarée.');
    }

    public function test_R01_aucune_route_de_suppression_de_compte(): void
    {
        foreach (RouteFacade::getRoutes() as $route) {
            if ($route->uri() === '/') {
                continue; // la redirection d'accueil accepte toutes les méthodes
            }

            $this->assertNotContains('DELETE', $route->methods(), 'Aucune suppression : '.$route->uri());
        }
    }

    public function test_une_permission_inconnue_est_refusee(): void
    {
        RouteFacade::middleware(['web', 'auth', 'permission:inexistante'])->get('/__test-permission', fn () => 'ok');

        $this->actingAs(\App\Modules\Access\Utilisateur::factory()->administrateur()->create())
            ->get('/__test-permission')->assertForbidden();
    }
}
