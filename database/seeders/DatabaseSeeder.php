<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->error('Les données de démonstration ne sont jamais chargées en production.');

            return;
        }

        $this->call(UtilisateursSeeder::class);
    }
}
