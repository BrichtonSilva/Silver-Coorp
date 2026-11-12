@extends('layouts.app')

@section('title', $client ? 'Modifier le client' : 'Nouveau client')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>{{ $client ? 'Modifier : ' . $client->name : 'Créer un nouveau client' }}</h3>
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

        <form method="POST" action="{{ $client ? route('clients.update', $client) : route('clients.store') }}" aria-label="Formulaire client">
            @csrf
            @if($client) @method('PUT') @endif

            <div class="form-group">
                <label for="name">Nom / Raison sociale <span class="required" aria-hidden="true">*</span></label>
                <input type="text" id="name" name="name" class="form-control"
                       value="{{ old('name', $client->name ?? '') }}"
                       required maxlength="255" aria-required="true">
                @error('name') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="type">Type <span class="required" aria-hidden="true">*</span></label>
                <select id="type" name="type" class="form-control" required aria-required="true">
                    @foreach(App\Models\Client::TYPES as $typeOption)
                        <option value="{{ $typeOption }}" {{ old('type', $client->type ?? '') === $typeOption ? 'selected' : '' }}>
                            {{ App\Models\Client::TYPE_LABELS[$typeOption] ?? $typeOption }}
                        </option>
                    @endforeach
                </select>
                @error('type') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="{{ old('email', $client->email ?? '') }}"
                       maxlength="255" autocomplete="email">
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="phone">Téléphone</label>
                <input type="tel" id="phone" name="phone" class="form-control"
                       value="{{ old('phone', $client->phone ?? '') }}"
                       maxlength="50" autocomplete="tel">
                @error('phone') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="address">Adresse</label>
                <textarea id="address" name="address" class="form-control" rows="3" maxlength="1000">{{ old('address', $client->address ?? '') }}</textarea>
                @error('address') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">{{ $client ? 'Enregistrer les modifications' : 'Créer le client' }}</button>
                <a href="{{ route('clients.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
