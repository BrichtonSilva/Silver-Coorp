<?php

namespace App\Modules\Access\Services;

use App\Modules\Shared\Contracts\EnregistreurJournal;
use App\Modules\Shared\Dto\UtilisateurDTO;
use Illuminate\Support\Facades\Log;

/** Implémentation provisoire de la traçabilité : journal applicatif. Remplacée par le module M11. */
final class JournalApplicatif implements EnregistreurJournal
{
    public function enregistrer(string $action, ?UtilisateurDTO $auteur, ?int $evenementId = null, array $detail = []): void
    {
        Log::info('Action tracée', [
            'action' => $action,
            'auteur_id' => $auteur?->id,
            'evenement_id' => $evenementId,
            'detail' => $detail,
        ]);
    }
}
