<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntry extends Model
{
    use HasFactory;

    public const ACTION_CREATION = 'création';
    public const ACTION_MODIFICATION = 'modification';
    public const ACTION_CHANGEMENT_STATUT = 'changement_statut';
    public const ACTION_AFFECTATION = 'affectation_prestataire';
    public const ACTION_ANNULATION = 'annulation';
    public const ACTION_SUPPRESSION = 'suppression';
    public const ACTION_TACHE = 'tâche';

    protected $fillable = ['event_id', 'user_id', 'action', 'details'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            self::ACTION_CREATION => 'Création',
            self::ACTION_MODIFICATION => 'Modification',
            self::ACTION_CHANGEMENT_STATUT => 'Changement de statut',
            self::ACTION_AFFECTATION => 'Affectation de prestataire',
            self::ACTION_ANNULATION => 'Annulation',
            self::ACTION_SUPPRESSION => 'Suppression',
            self::ACTION_TACHE => 'Tâche',
            default => $this->action,
        };
    }
}
