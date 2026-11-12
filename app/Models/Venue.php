<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venue extends Model
{
    use HasFactory;

    public const TYPE_INTERNE = 'interne';
    public const TYPE_EXTERNE = 'externe';

    public const TYPES = [
        self::TYPE_INTERNE,
        self::TYPE_EXTERNE,
    ];

    public const TYPE_LABELS = [
        self::TYPE_INTERNE => 'Interne',
        self::TYPE_EXTERNE => 'Externe',
    ];

    protected $fillable = ['name', 'address', 'capacity', 'type', 'rental_price', 'created_by'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Check if the venue is linked to at least one event.
     */
    public function hasEvents(): bool
    {
        return $this->events()->exists();
    }
}
