<?php

namespace App\Modules\Access;

use App\Modules\Shared\Dto\UtilisateurDTO;
use App\Modules\Shared\Role;
use Database\Factories\UtilisateurFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Entité Utilisateur (M1). Propriété exclusive du module Access : les autres modules
 * passent par les contrats de service et reçoivent un UtilisateurDTO.
 */
class Utilisateur extends Authenticatable
{
    use HasFactory;

    protected $table = 'utilisateurs';

    protected $fillable = ['nom', 'identifiant', 'mot_de_passe', 'role', 'actif', 'derniere_connexion'];

    protected $hidden = ['mot_de_passe'];

    protected static function newFactory(): UtilisateurFactory
    {
        return UtilisateurFactory::new();
    }

    protected function casts(): array
    {
        return [
            'role' => Role::class,
            'actif' => 'boolean',
            'derniere_connexion' => 'datetime',
            'mot_de_passe' => 'hashed',
        ];
    }

    /** R02 : l'identifiant est toujours stocké en minuscules, sans espaces autour. */
    public static function normaliserIdentifiant(string $identifiant): string
    {
        return mb_strtolower(trim($identifiant));
    }

    protected function identifiant(): Attribute
    {
        return Attribute::make(set: fn (string $valeur) => self::normaliserIdentifiant($valeur));
    }

    // Le mot de passe est dans la colonne « mot_de_passe », et il n'y a pas de jeton « se souvenir de moi ».
    public function getAuthPassword(): string
    {
        return $this->mot_de_passe;
    }

    public function getAuthPasswordName(): string
    {
        return 'mot_de_passe';
    }

    public function getRememberTokenName(): string
    {
        return '';
    }

    public function versDto(): UtilisateurDTO
    {
        return new UtilisateurDTO($this->id, $this->nom, $this->role, $this->actif);
    }
}
