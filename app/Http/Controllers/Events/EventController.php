<?php

namespace App\Http\Controllers\Events;

use App\Http\Requests\Events\StoreEventRequest;
use App\Http\Requests\Events\UpdateEventRequest;
use App\Models\Client;
use App\Models\Event;
use App\Models\User;
use App\Models\Venue;
use App\Services\JournalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * Display paginated event list with search and filters.
     */
    public function index(Request $request): View
    {
        // Auto-terminate confirmed events whose date has passed
        Event::autoTerminate();

        $query = Event::with(['client', 'venue', 'chargedUser']);

        // Search by client name
        if ($search = $request->input('search')) {
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        // Filter by type
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        // Filter by date (exact)
        if ($date = $request->input('date')) {
            $query->whereDate('date', $date);
        }

        // Filter by date range
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('date', '<=', $dateTo);
        }

        // Filter by venue
        if ($venue = $request->input('venue')) {
            $query->where('venue_id', $venue);
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $events = $query->orderBy('date')->orderBy('start_time')->paginate(10)->withQueryString();
        $venues = Venue::orderBy('name')->get();
        $charges = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_CHARGE])->orderBy('name')->get();

        return view('events.index', compact('events', 'venues', 'charges'));
    }

    /**
     * Show event details.
     */
    public function show(Event $event): View
    {
        $event->load(['client', 'venue', 'chargedUser']);

        return view('events.show', compact('event'));
    }

    /**
     * Show create form (write roles only).
     */
    public function create(): View
    {
        $clients = Client::orderBy('name')->get();
        $venues = Venue::orderBy('name')->get();
        $charges = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_CHARGE])->orderBy('name')->get();

        return view('events.form', [
            'event' => null,
            'clients' => $clients,
            'venues' => $venues,
            'charges' => $charges,
        ]);
    }

    /**
     * Store a new event.
     */
    public function store(StoreEventRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $event = Event::create([
            'client_id' => $data['client_id'],
            'type' => $data['type'],
            'date' => $data['date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'venue_id' => $data['venue_id'],
            'guest_count' => $data['guest_count'],
            'budget' => $data['budget'],
            'status' => Event::STATUS_BROUILLON,
            'charged_user_id' => $data['charged_user_id'],
        ]);

        JournalService::log(
            'création',
            $event->id,
            "Événement créé : {$event->type} pour le client {$event->client?->name}."
        );

        return redirect()
            ->route('events.index')
            ->with('success', "L'événement a été créé avec succès.");
    }

    /**
     * Show edit form (write roles only).
     */
    public function edit(Event $event): View|RedirectResponse
    {
        if ($event->isTerminated() || $event->isCancelled()) {
            return redirect()
                ->route('events.index')
                ->with('error', 'Impossible de modifier un événement terminé ou annulé.');
        }

        $clients = Client::orderBy('name')->get();
        $venues = Venue::orderBy('name')->get();
        $charges = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_CHARGE])->orderBy('name')->get();

        return view('events.form', compact('event', 'clients', 'venues', 'charges'));
    }

    /**
     * Update an existing event.
     */
    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        if ($event->isTerminated() || $event->isCancelled()) {
            return redirect()
                ->route('events.index')
                ->with('error', 'Impossible de modifier un événement terminé ou annulé.');
        }

        $data = $request->validated();

        $event->fill([
            'client_id' => $data['client_id'],
            'type' => $data['type'],
            'date' => $data['date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'venue_id' => $data['venue_id'],
            'guest_count' => $data['guest_count'],
            'budget' => $data['budget'],
            'charged_user_id' => $data['charged_user_id'],
        ]);

        $event->save();

        JournalService::log(
            'modification',
            $event->id,
            "Événement modifié : {$event->type}."
        );

        return redirect()
            ->route('events.index')
            ->with('success', "L'événement a été modifié avec succès.");
    }

    /**
     * Confirm a draft event.
     */
    public function confirm(Event $event): RedirectResponse
    {
        if (!$event->isDraft()) {
            return back()->with('error', 'Seul un événement en brouillon peut être confirmé.');
        }

        $event->update(['status' => Event::STATUS_CONFIRME]);

        JournalService::log(
            'changement_statut',
            $event->id,
            "Statut changé de brouillon à confirmé."
        );

        return redirect()
            ->route('events.index')
            ->with('success', "L'événement a été confirmé avec succès.");
    }

    /**
     * Cancel an event with reason.
     */
    public function cancel(Request $request, Event $event): RedirectResponse
    {
        $request->validate([
            'cancellation_reason' => ['required', 'string', 'min:5'],
        ], [
            'cancellation_reason.required' => 'Le motif d\'annulation est obligatoire.',
            'cancellation_reason.min' => 'Le motif d\'annulation doit faire au moins 5 caractères.',
        ]);

        if ($event->isCancelled()) {
            return back()->with('error', 'Cet événement est déjà annulé.');
        }

        $event->update([
            'status' => Event::STATUS_ANNULE,
            'cancellation_reason' => $request->input('cancellation_reason'),
        ]);

        JournalService::log(
            'annulation',
            $event->id,
            "Événement annulé. Motif : {$request->input('cancellation_reason')}"
        );

        return redirect()
            ->route('events.index')
            ->with('success', "L'événement a été annulé avec succès.");
    }
}
