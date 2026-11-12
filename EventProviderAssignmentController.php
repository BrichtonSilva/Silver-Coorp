<?php

namespace App\Http\Controllers\Events;

use App\Http\Requests\Events\StoreAssignmentRequest;
use App\Http\Requests\Events\UpdateAssignmentRequest;
use App\Models\Event;
use App\Models\EventProviderAssignment;
use App\Models\Provider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

class EventProviderAssignmentController extends Controller
{
    /**
     * Store a new provider assignment.
     */
    public function store(StoreAssignmentRequest $request, Event $event): RedirectResponse
    {
        $data = $request->validated();

        // RG10: Check for provider overlap with other non-cancelled events
        $conflict = $this->findProviderConflict(
            $data['provider_id'],
            $event->date,
            $event->start_time,
            $event->end_time,
            $event->id
        );

        if ($conflict) {
            return back()
                ->with('error', "Ce prestataire est déjà affecté à un autre événement non annulé le même jour ({$conflict->event->client->name}, {$conflict->event->date->format('d/m/Y')} de {$conflict->event->start_time->format('H:i')} à {$conflict->event->end_time->format('H:i')}).");
        }

        $assignment = EventProviderAssignment::create([
            'event_id' => $event->id,
            'provider_id' => $data['provider_id'],
            'cost' => $data['cost'] ?? 0,
            'status' => $data['status'] ?? EventProviderAssignment::STATUS_A_CONTACTER,
        ]);

        return redirect()
            ->route('events.show', $event)
            ->with('success', "Le prestataire \"{$assignment->provider->name}\" a été affecté avec succès.");
    }

    /**
     * Update an assignment (cost and status).
     */
    public function update(UpdateAssignmentRequest $request, Event $event, EventProviderAssignment $assignment): RedirectResponse
    {
        $data = $request->validated();

        $assignment->fill([
            'cost' => $data['cost'] ?? $assignment->cost,
            'status' => $data['status'] ?? $assignment->status,
        ]);

        $assignment->save();

        return redirect()
            ->route('events.show', $event)
            ->with('success', "L'affectation de \"{$assignment->provider->name}\" a été modifiée.");
    }

    /**
     * Delete an assignment.
     */
    public function destroy(Event $event, EventProviderAssignment $assignment): RedirectResponse
    {
        $providerName = $assignment->provider->name;
        $assignment->delete();

        return redirect()
            ->route('events.show', $event)
            ->with('success', "L'affectation de \"{$providerName}\" a été supprimée.");
    }

    /**
     * RG10: Find if a provider is already assigned to a non-cancelled event
     * on the same date with overlapping time.
     */
    private function findProviderConflict(
        int $providerId,
        $date,
        $startTime,
        $endTime,
        ?int $excludeEventId = null
    ): ?EventProviderAssignment {
        $assignments = EventProviderAssignment::where('provider_id', $providerId)
            ->whereHas('event', function ($q) use ($date, $startTime, $endTime, $excludeEventId) {
                $q->whereDate('date', $date)
                  ->where('status', '!=', Event::STATUS_ANNULE);

                if ($excludeEventId) {
                    $q->where('id', '!=', $excludeEventId);
                }

                // Overlap: start < other_end AND end > other_start
                $q->where(function ($sq) use ($startTime, $endTime) {
                    $sq->whereTime('start_time', '<', $endTime)
                       ->whereTime('end_time', '>', $startTime);
                });
            })
            ->with('event.client')
            ->first();

        return $assignments;
    }
}
