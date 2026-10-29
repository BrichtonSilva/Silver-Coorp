<?php

namespace App\Modules\Shared\Contracts;

use App\Modules\Shared\Dto\UtilisateurDTO;

/**
 * Point d'application unique de la traçabilité (RG16, R16).
 * L'événement est facultatif : les actions sur les comptes n'en ont pas (voir ADR-005).
 */
interface EnregistreurJournal
{
    /** @param array<string, scalar|null> $detail */
    public function enregistrer(string $action, ?UtilisateurDTO $auteur, ?int $evenementId = null, array $detail = []): void;
}
