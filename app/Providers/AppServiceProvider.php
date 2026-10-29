<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Interface en français uniquement (cahier des charges §11.2).
        $this->app->setLocale('fr');
        Carbon::setLocale('fr');

        Paginator::defaultView('pagination.eventia');
        Paginator::defaultSimpleView('pagination.eventia');
    }
}
