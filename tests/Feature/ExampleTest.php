<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_la_racine_renvoie_vers_le_tableau_de_bord(): void
    {
        $this->get('/')->assertRedirect('/tableau-de-bord');
    }
}
