@extends('layouts.app')

@section('title', $client->name)

@section('content')
<div class="card">
    <div class="card-header">
        <h3>{{ $client->name }}</h3>
        @if(auth()->user()->canWrite())
        <a href="{{ route('clients.edit', $client) }}" class="btn btn-secondary btn-sm">Modifier</a>
        @endif
    </div>
    <div class="card-body">
        <div class="detail-grid">
            <div>
                <div class="form-group">
                    <label class="detail-label">Type</label>
                    <p>
                        @if($client->type === App\Models\Client::TYPE_PARTICULIER)
                            <span class="badge badge-particulier">Particulier</span>
                        @else
                            <span class="badge badge-entreprise">Entreprise</span>
                        @endif
                    </p>
                </div>

                <div class="form-group">
                    <label class="detail-label">Email</label>
                    <p>{{ $client->email ?? '—' }}</p>
                </div>

                <div class="form-group">
                    <label class="detail-label">Téléphone</label>
                    <p>{{ $client->phone ?? '—' }}</p>
                </div>
            </div>

            <div>
                <div class="form-group">
                    <label class="detail-label">Adresse</label>
                    <p>{{ $client->address ?? '—' }}</p>
                </div>

                <div class="form-group">
                    <label class="detail-label">Créé par</label>
                    <p>{{ $client->creator?->name ?? '—' }}</p>
                </div>

                <div class="form-group">
                    <label class="detail-label">Date de création</label>
                    <p>{{ $client->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <div style="margin-top:24px;">
            <a href="{{ route('clients.index') }}" class="btn btn-secondary">Retour à la liste</a>
        </div>
    </div>
</div>
@endsection
