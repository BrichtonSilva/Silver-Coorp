<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provider extends Model
{
    use HasFactory;

    public const CATEGORY_TRAITEUR = 'traiteur';
    public const CATEGORY_SONORISATION = 'sonorisation';
    public const CATEGORY_DECORATION = 'décoration';
    public const CATEGORY_PHOTOGRAPHE = 'photographe';
    public const CATEGORY_AUTRE = 'autre';

    public const CATEGORIES = [
        self::CATEGORY_TRAITEUR,
        self::CATEGORY_SONORISATION,
        self::CATEGORY_DECORATION,
        self::CATEGORY_PHOTOGRAPHE,
        self::CATEGORY_AUTRE,
    ];

    public const CATEGORY_LABELS = [
        self::CATEGORY_TRAITEUR => 'Traiteur',
        self::CATEGORY_SONORISATION => 'Sonorisation',
        self::CATEGORY_DECORATION => 'Décoration',
        self::CATEGORY_PHOTOGRAPHE => 'Photographe',
        self::CATEGORY_AUTRE => 'Autre',
    ];

    protected $fillable = ['name', 'category', 'contact', 'phone', 'email', 'indicative_price', 'created_by'];

    protected function casts(): array
    {
        return [
            'indicative_price' => 'decimal:2',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(EventProviderAssignment::class);
    }

    /**
     * Check if the provider is linked to at least one event via assignments.
     */
    public function hasEvents(): bool
    {
        return $this->assignments()->whereHas('event', function ($q) {
            $q->where('status', '!=', Event::STATUS_ANNULE);
        })->exists();
    }
}
