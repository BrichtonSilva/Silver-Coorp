<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    public const TYPE_PARTICULIER = 'particulier';
    public const TYPE_ENTREPRISE = 'entreprise';

    public const TYPES = [
        self::TYPE_PARTICULIER,
        self::TYPE_ENTREPRISE,
    ];

    public const TYPE_LABELS = [
        self::TYPE_PARTICULIER => 'Particulier',
        self::TYPE_ENTREPRISE => 'Entreprise',
    ];

    protected $fillable = ['name', 'type', 'email', 'phone', 'address', 'created_by'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Check if the client is linked to at least one event.
     */
    public function hasEvents(): bool
    {
        return $this->events()->exists();
    }
}
