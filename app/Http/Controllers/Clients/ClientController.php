<?php

namespace App\Http\Controllers\Clients;

use App\Http\Requests\Clients\StoreClientRequest;
use App\Http\Requests\Clients\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class ClientController extends Controller
{
    /**
     * Display paginated client list with search.
     */
    public function index(Request $request): View
    {
        $query = Client::with('creator');

        // Search by name, email, phone
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        // Filter by type
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $clients = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('clients.index', compact('clients'));
    }

    /**
     * Show client details.
     */
    public function show(Client $client): View
    {
        $client->load('creator');

        return view('clients.show', compact('client'));
    }

    /**
     * Show create form (write roles only: admin + charge).
     */
    public function create(): View
    {
        return view('clients.form', ['client' => null]);
    }

    /**
     * Store a new client.
     */
    public function store(StoreClientRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $client = Client::create([
            'name' => $data['name'],
            'type' => $data['type'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('clients.index')
            ->with('success', "Le client \"{$client->name}\" a été créé avec succès.");
    }

    /**
     * Show edit form (write roles only).
     */
    public function edit(Client $client): View
    {
        return view('clients.form', compact('client'));
    }

    /**
     * Update an existing client.
     */
    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $data = $request->validated();

        $client->fill([
            'name' => $data['name'],
            'type' => $data['type'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        $client->save();

        return redirect()
            ->route('clients.index')
            ->with('success', "Le client \"{$client->name}\" a été modifié avec succès.");
    }

    /**
     * Delete a client.
     * RG15: Prevent deletion if linked to events.
     */
    public function destroy(Client $client): RedirectResponse
    {
        if ($client->hasEvents()) {
            return back()->with('error', 'Impossible de supprimer ce client car il est lié à des événements.');
        }

        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('success', "Le client \"{$client->name}\" a été supprimé avec succès.");
    }
}
