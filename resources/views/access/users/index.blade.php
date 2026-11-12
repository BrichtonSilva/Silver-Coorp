@extends('layouts.app')

@section('title', 'Utilisateurs')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Gestion des utilisateurs</h3>
        <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">+ Nouvel utilisateur</a>
    </div>
    <div class="card-body">
        {{-- Search & filter --}}
        <form method="GET" action="{{ route('users.index') }}" class="filters" aria-label="Recherche et filtrage">
            <div class="form-group search-input">
                <label for="search" class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);">Rechercher</label>
                <input type="text" id="search" name="search" class="form-control"
                       placeholder="Rechercher par nom, identifiant ou email..."
                       value="{{ request('search') }}">
            </div>
            <div class="form-group">
                <label for="role" class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);">Rôle</label>
                <select id="role" name="role" class="form-control">
                    <option value="">Tous les rôles</option>
                    @foreach(App\Models\User::ROLES as $role)
                        <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>
                            {{ App\Models\User::ROLE_LABELS[$role] ?? $role }}
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
                        <th scope="col">Identifiant</th>
                        <th scope="col">Email</th>
                        <th scope="col">Rôle</th>
                        <th scope="col">Statut</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->login }}</td>
                        <td>{{ $user->email ?? '—' }}</td>
                        <td>
                            @if($user->isAdmin())
                                <span class="badge badge-admin">Administrateur</span>
                            @elseif($user->isCharge())
                                <span class="badge badge-charge">Chargé d'événements</span>
                            @else
                                <span class="badge badge-consultant">Consultant</span>
                            @endif
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="badge badge-active">Actif</span>
                            @else
                                <span class="badge badge-inactive">Inactif</span>
                            @endif
                        </td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-secondary btn-sm">Modifier</a>
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Confirmer la suppression de cet utilisateur ?');" style="display:inline;">
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
                                <p>Aucun utilisateur trouvé.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
        <div class="pagination" role="navigation" aria-label="Pagination">
            {!! $users->links() !!}
        </div>
        @endif
    </div>
</div>
@endsection
