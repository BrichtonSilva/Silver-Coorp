<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventProviderAssignment extends Model
{
    use HasFactory;

    public const STATUS_A_CONTACTER = 'a_contacter';
    public const STATUS_CONTACTE = 'contacte';
    public const STATUS_CONFIRME = 'confirme';
    public const STATUS_ANNULE = 'annule';

    public const STATUSES = [
        self::STATUS_A_CONTACTER,
        self::STATUS_CONTACTE,
        self::STATUS_CONFIRME,
        self::STATUS_ANNULE,
    ];

    public const STATUS_LABELS = [
        self::STATUS_A_CONTACTER => 'À contacter',
        self::STATUS_CONTACTE => 'Contacté',
        self::STATUS_CONFIRME => 'Confirmé',
        self::STATUS_ANNULE => 'Annulé',
    ];

    protected $fillable = ['event_id', 'provider_id', 'cost', 'status'];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_ANNULE;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRME;
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
