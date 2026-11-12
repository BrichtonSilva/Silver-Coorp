@extends('layouts.app')

@section('title', $venue->name)

@section('content')
<div class="card">
    <div class="card-header">
        <h3>{{ $venue->name }}</h3>
        @if(auth()->user()->canWrite())
        <a href="{{ route('venues.edit', $venue) }}" class="btn btn-secondary btn-sm">Modifier</a>
        @endif
    </div>
    <div class="card-body">
        <div class="detail-grid">
            <div>
                <div class="form-group">
                    <label class="detail-label">Type</label>
                    <p>
                        @if($venue->type === App\Models\Venue::TYPE_INTERNE)
                            <span class="badge badge-particulier">Interne</span>
                        @else
                            <span class="badge badge-entreprise">Externe</span>
                        @endif
                    </p>
                </div>

                <div class="form-group">
                    <label class="detail-label">Capacité</label>
                    <p>{{ $venue->capacity }} personnes</p>
                </div>

                <div class="form-group">
                    <label class="detail-label">Prix de location</label>
                    <p>{{ number_format($venue->rental_price, 2, ',', ' ') }} €</p>
                </div>
            </div>

            <div>
                <div class="form-group">
                    <label class="detail-label">Adresse</label>
                    <p>{{ $venue->address ?? '—' }}</p>
                </div>

                <div class="form-group">
                    <label class="detail-label">Créé par</label>
                    <p>{{ $venue->creator?->name ?? '—' }}</p>
                </div>

                <div class="form-group">
                    <label class="detail-label">Date de création</label>
                    <p>{{ $venue->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <div style="margin-top:24px;">
            <a href="{{ route('venues.index') }}" class="btn btn-secondary">Retour à la liste</a>
        </div>
    </div>
</div>
@endsection
