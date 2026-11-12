@extends('layouts.app')

@section('title', 'Clients')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Gestion des clients</h3>
        @if(auth()->user()->canWrite())
        <a href="{{ route('clients.create') }}" class="btn btn-primary btn-sm">+ Nouveau client</a>
        @endif
    </div>
    <div class="card-body">
        {{-- Search & filter --}}
        <form method="GET" action="{{ route('clients.index') }}" class="filters" aria-label="Recherche et filtrage">
            <div class="form-group search-input">
                <label for="search" class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);">Rechercher</label>
                <input type="text" id="search" name="search" class="form-control"
                       placeholder="Rechercher par nom, email ou téléphone..."
                       value="{{ request('search') }}">
            </div>
            <div class="form-group">
                <label for="type" class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);">Type</label>
                <select id="type" name="type" class="form-control">
                    <option value="">Tous les types</option>
                    @foreach(App\Models\Client::TYPES as $typeOption)
                        <option value="{{ $typeOption }}" {{ request('type') === $typeOption ? 'selected' : '' }}>
                            {{ App\Models\Client::TYPE_LABELS[$typeOption] ?? $typeOption }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">Filtrer</button>
        </form>

        {{-- Table --}}
        <div class="table-wrapper">
            <table role="table">
                <thead>
                    <tr>
                        <th scope="col">Nom</th>
                        <th scope="col">Type</th>
                        <th scope="col">Email</th>
                        <th scope="col">Téléphone</th>
                        <th scope="col">Créé par</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                    <tr>
                        <td>
                            <a href="{{ route('clients.show', $client) }}" style="color:var(--color-primary);text-decoration:none;font-weight:500;">
                                {{ $client->name }}
                            </a>
                        </td>
                        <td>
                            @if($client->type === App\Models\Client::TYPE_PARTICULIER)
                                <span class="badge badge-particulier">Particulier</span>
                            @else
                                <span class="badge badge-entreprise">Entreprise</span>
                            @endif
                        </td>
                        <td>{{ $client->email ?? '—' }}</td>
                        <td>{{ $client->phone ?? '—' }}</td>
                        <td>{{ $client->creator?->name ?? '—' }}</td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('clients.show', $client) }}" class="btn btn-secondary btn-sm">Voir</a>
                                @if(auth()->user()->canWrite())
                                <a href="{{ route('clients.edit', $client) }}" class="btn btn-secondary btn-sm">Modifier</a>
                                <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Confirmer la suppression de ce client ?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <p>Aucun client trouvé.</p>
                                @if(auth()->user()->canWrite())
                                <a href="{{ route('clients.create') }}" class="btn btn-primary btn-sm">Créer le premier client</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($clients->hasPages())
        <div class="pagination" role="navigation" aria-label="Pagination">
            {!! $clients->links() !!}
        </div>
        @endif
    </div>
</div>
@endsection
