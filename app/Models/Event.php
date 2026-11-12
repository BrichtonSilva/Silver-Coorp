<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    public const TYPE_MARIAGE = 'mariage';
    public const TYPE_SEMINAIRE = 'séminaire';
    public const TYPE_CONFERENCE = 'conférence';
    public const TYPE_CONCERT = 'concert';
    public const TYPE_AUTRE = 'autre';

    public const TYPES = [
        self::TYPE_MARIAGE,
        self::TYPE_SEMINAIRE,
        self::TYPE_CONFERENCE,
        self::TYPE_CONCERT,
        self::TYPE_AUTRE,
    ];

    public const TYPE_LABELS = [
        self::TYPE_MARIAGE => 'Mariage',
        self::TYPE_SEMINAIRE => 'Séminaire',
        self::TYPE_CONFERENCE => 'Conférence',
        self::TYPE_CONCERT => 'Concert',
        self::TYPE_AUTRE => 'Autre',
    ];

    public const STATUS_BROUILLON = 'brouillon';
    public const STATUS_CONFIRME = 'confirmé';
    public const STATUS_TERMINE = 'terminé';
    public const STATUS_ANNULE = 'annulé';

    public const STATUSES = [
        self::STATUS_BROUILLON,
        self::STATUS_CONFIRME,
        self::STATUS_TERMINE,
        self::STATUS_ANNULE,
    ];

    public const STATUS_LABELS = [
        self::STATUS_BROUILLON => 'Brouillon',
        self::STATUS_CONFIRME => 'Confirmé',
        self::STATUS_TERMINE => 'Terminé',
        self::STATUS_ANNULE => 'Annulé',
    ];

    protected $fillable = [
        'client_id',
        'type',
        'date',
        'start_time',
        'end_time',
        'venue_id',
        'guest_count',
        'budget',
        'status',
        'cancellation_reason',
        'charged_user_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'budget' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function chargedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'charged_user_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(EventProviderAssignment::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Calculate checklist progress percentage.
     * RG13
     */
    public function checklistProgress(): int
    {
        $total = $this->tasks()->count();
        if ($total === 0) {
            return 0;
        }
        $done = $this->tasks()->where('is_done', true)->count();
        return (int) round(($done / $total) * 100);
    }

    /**
     * Get overdue tasks.
     * RG14
     */
    public function overdueTasks()
    {
        return $this->tasks()
            ->where('is_done', false)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->get();
    }

    /**
     * Calculate real expenses: sum of non-cancelled assignment costs + venue rental price.
     * RG12
     */
    public function realExpenses(): float
    {
        $assignmentsCost = $this->assignments()
            ->where('status', '!=', EventProviderAssignment::STATUS_ANNULE)
            ->sum('cost');

        $venueCost = $this->venue?->rental_price ?? 0;

        return (float) $assignmentsCost + (float) $venueCost;
    }

    /**
     * Calculate remaining budget.
     */
    public function budgetRemaining(): float
    {
        return (float) $this->budget - $this->realExpenses();
    }

    /**
     * Check if real expenses exceed budget.
     */
    public function isOverBudget(): bool
    {
        return $this->realExpenses() > (float) $this->budget;
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class)->orderBy('created_at', 'desc');
    }

    /**
     * Check if the event is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_ANNULE;
    }

    /**
     * Check if the event is terminated.
     */
    public function isTerminated(): bool
    {
        return $this->status === self::STATUS_TERMINE;
    }

    /**
     * Check if the event is confirmed.
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRME;
    }

    /**
     * Check if the event is a draft.
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_BROUILLON;
    }

    /**
     * Auto-terminate events whose date has passed.
     */
    public static function autoTerminate(): int
    {
        return static::where('status', self::STATUS_CONFIRME)
            ->where('date', '<', now()->toDateString())
            ->update(['status' => self::STATUS_TERMINE]);
    }
}
