<?php

namespace App\Services;

use App\Models\JournalEntry;

class JournalService
{
    public static function log(
        string $action,
        ?int $eventId = null,
        ?string $details = null,
        ?int $userId = null
    ): JournalEntry {
        return JournalEntry::create([
            'event_id' => $eventId,
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'details' => $details,
        ]);
    }
}
