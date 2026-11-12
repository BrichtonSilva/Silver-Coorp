<?php

namespace App\Http\Controllers\Venues;

use App\Http\Requests\Venues\StoreVenueRequest;
use App\Http\Requests\Venues\UpdateVenueRequest;
use App\Models\Venue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class VenueController extends Controller
{
    /**
     * Display paginated venue list with search.
     */
    public function index(Request $request): View
    {
        $query = Venue::with('creator');

        // Search by name
        if ($search = $request->input('search')) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // Filter by type
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $venues = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('venues.index', compact('venues'));
    }

    /**
     * Show venue details.
     */
    public function show(Venue $venue): View
    {
        $venue->load('creator');

        return view('venues.show', compact('venue'));
    }

    /**
     * Show create form (write roles only: admin + charge).
     */
    public function create(): View
    {
        return view('venues.form', ['venue' => null]);
    }

    /**
     * Store a new venue.
     */
    public function store(StoreVenueRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $venue = Venue::create([
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
            'capacity' => $data['capacity'],
            'type' => $data['type'],
            'rental_price' => $data['rental_price'],
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('venues.index')
            ->with('success', "Le lieu \"{$venue->name}\" a été créé avec succès.");
    }

    /**
     * Show edit form (write roles only).
     */
    public function edit(Venue $venue): View
    {
        return view('venues.form', compact('venue'));
    }

    /**
     * Update an existing venue.
     */
    public function update(UpdateVenueRequest $request, Venue $venue): RedirectResponse
    {
        $data = $request->validated();

        $venue->fill([
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
            'capacity' => $data['capacity'],
            'type' => $data['type'],
            'rental_price' => $data['rental_price'],
        ]);

        $venue->save();

        return redirect()
            ->route('venues.index')
            ->with('success', "Le lieu \"{$venue->name}\" a été modifié avec succès.");
    }

    /**
     * Delete a venue.
     * RG15: Prevent deletion if linked to events.
     */
    public function destroy(Venue $venue): RedirectResponse
    {
        if ($venue->hasEvents()) {
            return back()->with('error', 'Impossible de supprimer ce lieu car il est lié à des événements.');
        }

        $venue->delete();

        return redirect()
            ->route('venues.index')
            ->with('success', "Le lieu \"{$venue->name}\" a été supprimé avec succès.");
    }
}
