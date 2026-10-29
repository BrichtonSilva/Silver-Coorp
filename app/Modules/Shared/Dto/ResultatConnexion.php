<?php

namespace App\Modules\Shared\Dto;

/** Résultat d'une tentative de connexion : succès, échec indifférencié, ou blocage temporaire. */
final readonly class ResultatConnexion
{
    private function __construct(
        public bool $succes,
        public int $secondesAttente = 0,
    ) {}

    public static function reussi(): self
    {
        return new self(true);
    }

    public static function echec(): self
    {
        return new self(false);
    }

    public static function bloque(int $secondes): self
    {
        return new self(false, max(1, $secondes));
    }

    public function estBloque(): bool
    {
        return ! $this->succes && $this->secondesAttente > 0;
    }
}
