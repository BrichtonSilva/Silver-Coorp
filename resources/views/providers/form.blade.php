@extends('layouts.app')

@section('title', $provider ? 'Modifier le prestataire' : 'Nouveau prestataire')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>{{ $provider ? 'Modifier : ' . $provider->name : 'Créer un nouveau prestataire' }}</h3>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-error" role="alert">
                <ul style="list-style:none;padding:0;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $provider ? route('providers.update', $provider) : route('providers.store') }}" aria-label="Formulaire prestataire">
            @csrf
            @if($provider) @method('PUT') @endif

            <div class="form-group">
                <label for="name">Nom <span class="required" aria-hidden="true">*</span></label>
                <input type="text" id="name" name="name" class="form-control"
                       value="{{ old('name', $provider->name ?? '') }}"
                       required maxlength="255" aria-required="true">
                @error('name') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="category">Catégorie <span class="required" aria-hidden="true">*</span></label>
                <select id="category" name="category" class="form-control" required aria-required="true">
                    @foreach(App\Models\Provider::CATEGORIES as $categoryOption)
                        <option value="{{ $categoryOption }}" {{ old('category', $provider->category ?? '') === $categoryOption ? 'selected' : '' }}>
                            {{ App\Models\Provider::CATEGORY_LABELS[$categoryOption] ?? $categoryOption }}
                        </option>
                    @endforeach
                </select>
                @error('category') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="contact">Contact</label>
                <input type="text" id="contact" name="contact" class="form-control"
                       value="{{ old('contact', $provider->contact ?? '') }}"
                       maxlength="255">
                @error('contact') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="phone">Téléphone</label>
                <input type="tel" id="phone" name="phone" class="form-control"
                       value="{{ old('phone', $provider->phone ?? '') }}"
                       maxlength="50" autocomplete="tel">
                @error('phone') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="{{ old('email', $provider->email ?? '') }}"
                       maxlength="255" autocomplete="email">
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="indicative_price">Prix indicatif (€)</label>
                <input type="number" id="indicative_price" name="indicative_price" class="form-control"
                       value="{{ old('indicative_price', $provider->indicative_price ?? '') }}"
                       min="0" step="0.01">
                @error('indicative_price') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div style="display:flex;gap:10px;margin-top:24px;">
                <button type="submit" class="btn btn-primary">{{ $provider ? 'Enregistrer les modifications' : 'Créer le prestataire' }}</button>
                <a href="{{ route('providers.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
