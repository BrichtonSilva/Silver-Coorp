@extends('layouts.app')

@section('title', 'Événements')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Gestion des événements</h3>
        @if(auth()->user()->canWrite())
        <a href="{{ route('events.create') }}" class="btn btn-primary btn-sm">+ Nouvel événement</a>
        @endif
    </div>
    <div class="card-body">
        {{-- Search & filters --}}
        <form method="GET" action="{{ route('events.index') }}" class="filters" aria-label="Recherche et filtrage">
            <div class="form-group search-input">
                <label for="search" class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);">Rechercher</label>
                <input type="text" id="search" name="search" class="form-control"
                       placeholder="Rechercher par client..."
                       value="{{ request('search') }}">
            </div>
            <div class="form-group">
                <label for="type" class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);">Type</label>
                <select id="type" name="type" class="form-control">
                    <option value="">Tous les types</option>
                    @foreach(App\Models\Event::TYPES as $typeOption)
                        <option value="{{ $typeOption }}" {{ request('type') === $typeOption ? 'selected' : '' }}>
                            {{ App\Models\Event::TYPE_LABELS[$typeOption] ?? $typeOption }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="status" class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);">Statut</label>
                <select id="status" name="status" class="form-control">
                    <option value="">Tous les statuts</option>
                    @foreach(App\Models\Event::STATUSES as $statusOption)
                        <option value="{{ $statusOption }}" {{ request('status') === $statusOption ? 'selected' : '' }}>
                            {{ App\Models\Event::STATUS_LABELS[$statusOption] ?? $statusOption }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="venue" class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);">Lieu</label>
                <select id="venue" name="venue" class="form-control">
                    <option value="">Tous les lieux</option>
                    @foreach($venues as $venueOption)
                        <option value="{{ $venueOption->id }}" {{ request('venue') == $venueOption->id ? 'selected' : '' }}>
                            {{ $venueOption->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="date_from" class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);">Date début</label>
                <input type="date" id="date_from" name="date_from" class="form-control"
                       value="{{ request('date_from') }}" placeholder="Du">
            </div>
            <div class="form-group">
                <label for="date_to" class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);">Date fin</label>
                <input type="date" id="date_to" name="date_to" class="form-control"
                       value="{{ request('date_to') }}" placeholder="Au">
            </div>
            <button type="submit" class="btn btn-secondary">Filtrer</button>
            @if(request()->hasAny(['search', 'type', 'status', 'venue', 'date_from', 'date_to', 'date']))
                <a href="{{ route('events.index') }}" class="btn btn-secondary">Réinitialiser</a>
            @endif
        </form>

        {{-- Table --}}
        <div class="table-wrapper">
            <table role="table">
                <thead>
                    <tr>
                        <th scope="col">Client</th>
                        <th scope="col">Type</th>
                        <th scope="col">Date</th>
                        <th scope="col">Horaire</th>
                        <th scope="col">Lieu</th>
                        <th scope="col">Invités</th>
                        <th scope="col">Statut</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                    <tr>
                        <td>
                            <a href="{{ route('events.show', $event) }}" class="link">
                                {{ $event->client?->name ?? '—' }}
                            </a>
                        </td>
                        <td>
                            @php
                                $typeColors = [
                                    'mariage' => 'badge-pink',
                                    'séminaire' => 'badge-info',
                                    'conférence' => 'badge-indigo',
                                    'concert' => 'badge-warning',
                                    'autre' => 'badge-neutral',
                                ];
                                $tc = $typeColors[$event->type] ?? $typeColors['autre'];
                            @endphp
                            <span class="badge {{ $tc }}">
                                {{ App\Models\Event::TYPE_LABELS[$event->type] ?? $event->type }}
                            </span>
                        </td>
                        <td>{{ $event->date->format('d/m/Y') }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($event->start_time)->format('H:i') }} – {{ \Illuminate\Support\Carbon::parse($event->end_time)->format('H:i') }}</td>
                        <td>{{ $event->venue?->name ?? '—' }}</td>
                        <td>{{ $event->guest_count }}</td>
                        <td>
                            @if($event->status === App\Models\Event::STATUS_BROUILLON)
                                <span class="badge badge-neutral">Brouillon</span>
                            @elseif($event->status === App\Models\Event::STATUS_CONFIRME)
                                <span class="badge badge-info">Confirmé</span>
                            @elseif($event->status === App\Models\Event::STATUS_TERMINE)
                                <span class="badge badge-success">Terminé</span>
                            @elseif($event->status === App\Models\Event::STATUS_ANNULE)
                                <span class="badge badge-danger">Annulé</span>
                            @endif
                        </td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('events.show', $event) }}" class="btn btn-secondary btn-sm">Voir</a>
                                @if(auth()->user()->canWrite())
                                    @if($event->isDraft())
                                    <form method="POST" action="{{ route('events.confirm', $event) }}" style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Confirmer cet événement ?');">Confirmer</button>
                                    </form>
                                    @endif
                                    @if(!$event->isTerminated() && !$event->isCancelled())
                                    <a href="{{ route('events.edit', $event) }}" class="btn btn-secondary btn-sm">Modifier</a>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <p>Aucun événement trouvé.</p>
                                @if(auth()->user()->canWrite())
                                <a href="{{ route('events.create') }}" class="btn btn-primary btn-sm">Créer le premier événement</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($events->hasPages())
        <div class="pagination" role="navigation" aria-label="Pagination">
            {!! $events->links() !!}
        </div>
        @endif
    </div>
</div>
@endsection
