@extends('layouts.app')

@section('title', 'Lieux')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Gestion des lieux</h3>
        @if(auth()->user()->canWrite())
        <a href="{{ route('venues.create') }}" class="btn btn-primary btn-sm">+ Nouveau lieu</a>
        @endif
    </div>
    <div class="card-body">
        {{-- Search & filter --}}
        <form method="GET" action="{{ route('venues.index') }}" class="filters" aria-label="Recherche et filtrage">
            <div class="form-group search-input">
                <label for="search" class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);">Rechercher</label>
                <input type="text" id="search" name="search" class="form-control"
                       placeholder="Rechercher par nom..."
                       value="{{ request('search') }}">
            </div>
            <div class="form-group">
                <label for="type" class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);">Type</label>
                <select id="type" name="type" class="form-control">
                    <option value="">Tous les types</option>
                    @foreach(App\Models\Venue::TYPES as $typeOption)
                        <option value="{{ $typeOption }}" {{ request('type') === $typeOption ? 'selected' : '' }}>
                            {{ App\Models\Venue::TYPE_LABELS[$typeOption] ?? $typeOption }}
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
                        <th scope="col">Capacité</th>
                        <th scope="col">Prix de location</th>
                        <th scope="col">Adresse</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($venues as $venue)
                    <tr>
                        <td>
                            <a href="{{ route('venues.show', $venue) }}" class="link">
                                {{ $venue->name }}
                            </a>
                        </td>
                        <td>
                            @if($venue->type === App\Models\Venue::TYPE_INTERNE)
                                <span class="badge badge-particulier">Interne</span>
                            @else
                                <span class="badge badge-entreprise">Externe</span>
                            @endif
                        </td>
                        <td>{{ $venue->capacity }} pers.</td>
                        <td>{{ number_format($venue->rental_price, 2, ',', ' ') }} €</td>
                        <td>{{ $venue->address ?? '—' }}</td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('venues.show', $venue) }}" class="btn btn-secondary btn-sm">Voir</a>
                                @if(auth()->user()->canWrite())
                                <a href="{{ route('venues.edit', $venue) }}" class="btn btn-secondary btn-sm">Modifier</a>
                                <form method="POST" action="{{ route('venues.destroy', $venue) }}" onsubmit="return confirm('Confirmer la suppression de ce lieu ?');" style="display:inline;">
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
                                <p>Aucun lieu trouvé.</p>
                                @if(auth()->user()->canWrite())
                                <a href="{{ route('venues.create') }}" class="btn btn-primary btn-sm">Créer le premier lieu</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($venues->hasPages())
        <div class="pagination" role="navigation" aria-label="Pagination">
            {!! $venues->links() !!}
        </div>
        @endif
    </div>
</div>
@endsection
