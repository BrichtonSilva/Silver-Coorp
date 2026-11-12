@extends('layouts.app')

@section('title', 'Prestataires')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Gestion des prestataires</h3>
        @if(auth()->user()->canWrite())
        <a href="{{ route('providers.create') }}" class="btn btn-primary btn-sm">+ Nouveau prestataire</a>
        @endif
    </div>
    <div class="card-body">
        {{-- Search & filter --}}
        <form method="GET" action="{{ route('providers.index') }}" class="filters" aria-label="Recherche et filtrage">
            <div class="form-group search-input">
                <label for="search" class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);">Rechercher</label>
                <input type="text" id="search" name="search" class="form-control"
                       placeholder="Rechercher par nom..."
                       value="{{ request('search') }}">
            </div>
            <div class="form-group">
                <label for="category" class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);">Catégorie</label>
                <select id="category" name="category" class="form-control">
                    <option value="">Toutes les catégories</option>
                    @foreach(App\Models\Provider::CATEGORIES as $categoryOption)
                        <option value="{{ $categoryOption }}" {{ request('category') === $categoryOption ? 'selected' : '' }}>
                            {{ App\Models\Provider::CATEGORY_LABELS[$categoryOption] ?? $categoryOption }}
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
                        <th scope="col">Catégorie</th>
                        <th scope="col">Contact</th>
                        <th scope="col">Téléphone</th>
                        <th scope="col">Email</th>
                        <th scope="col">Prix indicatif</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($providers as $provider)
                    <tr>
                        <td>
                            <a href="{{ route('providers.show', $provider) }}" class="link">
                                {{ $provider->name }}
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-{{ $provider->category }}">
                                {{ App\Models\Provider::CATEGORY_LABELS[$provider->category] ?? $provider->category }}
                            </span>
                        </td>
                        <td>{{ $provider->contact ?? '—' }}</td>
                        <td>{{ $provider->phone ?? '—' }}</td>
                        <td>{{ $provider->email ?? '—' }}</td>
                        <td>{{ $provider->indicative_price ? number_format($provider->indicative_price, 2, ',', ' ') . ' €' : '—' }}</td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('providers.show', $provider) }}" class="btn btn-secondary btn-sm">Voir</a>
                                @if(auth()->user()->canWrite())
                                <a href="{{ route('providers.edit', $provider) }}" class="btn btn-secondary btn-sm">Modifier</a>
                                <form method="POST" action="{{ route('providers.destroy', $provider) }}" onsubmit="return confirm('Confirmer la suppression de ce prestataire ?');" style="display:inline;">
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
                        <td colspan="7">
                            <div class="empty-state">
                                <p>Aucun prestataire trouvé.</p>
                                @if(auth()->user()->canWrite())
                                <a href="{{ route('providers.create') }}" class="btn btn-primary btn-sm">Créer le premier prestataire</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($providers->hasPages())
        <div class="pagination" role="navigation" aria-label="Pagination">
            {!! $providers->links() !!}
        </div>
        @endif
    </div>
</div>
@endsection
