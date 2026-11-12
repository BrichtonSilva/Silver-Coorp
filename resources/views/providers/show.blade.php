@extends('layouts.app')

@section('title', $provider->name)

@section('content')
<div class="card">
    <div class="card-header">
        <h3>{{ $provider->name }}</h3>
        @if(auth()->user()->canWrite())
        <a href="{{ route('providers.edit', $provider) }}" class="btn btn-secondary btn-sm">Modifier</a>
        @endif
    </div>
    <div class="card-body">
        <div class="detail-grid">
            <div>
                <div class="form-group">
                    <label class="detail-label">Catégorie</label>
                    <p>
                        <span class="badge badge-{{ $provider->category }}">
                            {{ App\Models\Provider::CATEGORY_LABELS[$provider->category] ?? $provider->category }}
                        </span>
                    </p>
                </div>

                <div class="form-group">
                    <label class="detail-label">Contact</label>
                    <p>{{ $provider->contact ?? '—' }}</p>
                </div>

                <div class="form-group">
                    <label class="detail-label">Téléphone</label>
                    <p>{{ $provider->phone ?? '—' }}</p>
                </div>
            </div>

            <div>
                <div class="form-group">
                    <label class="detail-label">Email</label>
                    <p>{{ $provider->email ?? '—' }}</p>
                </div>

                <div class="form-group">
                    <label class="detail-label">Prix indicatif</label>
                    <p>{{ $provider->indicative_price ? number_format($provider->indicative_price, 2, ',', ' ') . ' €' : '—' }}</p>
                </div>

                <div class="form-group">
                    <label class="detail-label">Créé par</label>
                    <p>{{ $provider->creator?->name ?? '—' }}</p>
                </div>

                <div class="form-group">
                    <label class="detail-label">Date de création</label>
                    <p>{{ $provider->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <div style="margin-top:24px;">
            <a href="{{ route('providers.index') }}" class="btn btn-secondary">Retour à la liste</a>
        </div>
    </div>
</div>
@endsection
