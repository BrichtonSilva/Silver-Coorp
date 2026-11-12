<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'login', 'email', 'role', 'is_active', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'administrateur';
    public const ROLE_CHARGE = 'charge_evenements';
    public const ROLE_CONSULTANT = 'consultant';

    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_CHARGE,
        self::ROLE_CONSULTANT,
    ];

    public const ROLE_LABELS = [
        self::ROLE_ADMIN => 'Administrateur',
        self::ROLE_CHARGE => "Chargé d'événements",
        self::ROLE_CONSULTANT => 'Consultant',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Find a user by login (case-insensitive).
     */
    public static function findByLogin(string $login): ?self
    {
        return static::whereRaw('LOWER(login) = ?', [strtolower(trim($login))])->first();
    }

    /**
     * Check if the user has a given role.
     */
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isCharge(): bool
    {
        return $this->role === self::ROLE_CHARGE;
    }

    public function isConsultant(): bool
    {
        return $this->role === self::ROLE_CONSULTANT;
    }

    /**
     * Can this user write (create/update/delete) on business entities?
     */
    public function canWrite(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN, self::ROLE_CHARGE);
    }

    /**
     * Clients relation (for future use).
     */
    public function clients()
    {
        return $this->hasMany(Client::class, 'created_by');
    }

    /**
     * Events for which this user is the charged events manager.
     */
    public function events()
    {
        return $this->hasMany(Event::class, 'charged_user_id');
    }
}
