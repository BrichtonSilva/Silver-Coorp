<?php

namespace App\Modules\Shared\Dto;

use App\Modules\Shared\Role;

/** Représentation d'un utilisateur transmise aux autres modules (jamais le modèle Eloquent). */
final readonly class UtilisateurDTO
{
    public function __construct(
        public int $id,
        public string $nom,
        public Role $role,
        public bool $actif,
    ) {}
}
