<?php

namespace App\Http\Controllers\Providers;

use App\Http\Requests\Providers\StoreProviderRequest;
use App\Http\Requests\Providers\UpdateProviderRequest;
use App\Models\Provider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class ProviderController extends Controller
{
    /**
     * Display paginated provider list with search and category filter.
     */
    public function index(Request $request): View
    {
        $query = Provider::with('creator');

        // Search by name
        if ($search = $request->input('search')) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // Filter by category
        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        $providers = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('providers.index', compact('providers'));
    }

    /**
     * Show provider details.
     */
    public function show(Provider $provider): View
    {
        $provider->load('creator');

        return view('providers.show', compact('provider'));
    }

    /**
     * Show create form (write roles only: admin + charge).
     */
    public function create(): View
    {
        return view('providers.form', ['provider' => null]);
    }

    /**
     * Store a new provider.
     */
    public function store(StoreProviderRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $provider = Provider::create([
            'name' => $data['name'],
            'category' => $data['category'],
            'contact' => $data['contact'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'indicative_price' => $data['indicative_price'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('providers.index')
            ->with('success', "Le prestataire \"{$provider->name}\" a été créé avec succès.");
    }

    /**
     * Show edit form (write roles only).
     */
    public function edit(Provider $provider): View
    {
        return view('providers.form', compact('provider'));
    }

    /**
     * Update an existing provider.
     */
    public function update(UpdateProviderRequest $request, Provider $provider): RedirectResponse
    {
        $data = $request->validated();

        $provider->fill([
            'name' => $data['name'],
            'category' => $data['category'],
            'contact' => $data['contact'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'indicative_price' => $data['indicative_price'] ?? null,
        ]);

        $provider->save();

        return redirect()
            ->route('providers.index')
            ->with('success', "Le prestataire \"{$provider->name}\" a été modifié avec succès.");
    }

    /**
     * Delete a provider.
     * RG15: Prevent deletion if linked to events.
     */
    public function destroy(Provider $provider): RedirectResponse
    {
        if ($provider->hasEvents()) {
            return back()->with('error', 'Impossible de supprimer ce prestataire car il est lié à des événements.');
        }

        $provider->delete();

        return redirect()
            ->route('providers.index')
            ->with('success', "Le prestataire \"{$provider->name}\" a été supprimé avec succès.");
    }
}
