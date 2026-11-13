<?php

return [

    /*
    | Affiche des boutons « comptes de démonstration » sur la page de connexion.
    | À laisser désactivé (false) sur un site réellement exposé : les mots de passe
    | ci-dessous sont publics.
    */
    'enabled' => env('SHOW_DEMO_ACCOUNTS', false),

    'accounts' => [
        ['name' => 'Admin Eventia', 'role' => 'Administrateur', 'login' => 'admin', 'password' => 'admin123'],
        ['name' => 'Marie Dupont', 'role' => 'Chargée d\'événements', 'login' => 'marie.dupont', 'password' => 'charge123'],
        ['name' => 'Pierre Martin', 'role' => 'Consultant', 'login' => 'pierre.martin', 'password' => 'consult123'],
        ['name' => 'Jean Inactif', 'role' => 'Compte désactivé', 'login' => 'jean.inactif', 'password' => 'inactive123'],
    ],

];
