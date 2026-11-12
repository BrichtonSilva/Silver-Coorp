@extends('layouts.app')

@section('title', $venue ? 'Modifier le lieu' : 'Nouveau lieu')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>{{ $venue ? 'Modifier : ' . $venue->name : 'Créer un nouveau lieu' }}</h3>
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

        <form method="POST" action="{{ $venue ? route('venues.update', $venue) : route('venues.store') }}" aria-label="Formulaire lieu">
            @csrf
            @if($venue) @method('PUT') @endif

            <div class="form-group">
                <label for="name">Nom <span class="required" aria-hidden="true">*</span></label>
                <input type="text" id="name" name="name" class="form-control"
                       value="{{ old('name', $venue->name ?? '') }}"
                       required maxlength="255" aria-required="true">
                @error('name') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="address">Adresse</label>
                <textarea id="address" name="address" class="form-control" rows="3" maxlength="1000">{{ old('address', $venue->address ?? '') }}</textarea>
                @error('address') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="detail-grid">
                <div class="form-group">
                    <label for="capacity">Capacité <span class="required" aria-hidden="true">*</span></label>
                    <input type="number" id="capacity" name="capacity" class="form-control"
                           value="{{ old('capacity', $venue->capacity ?? '') }}"
                           required min="1" aria-required="true">
                    @error('capacity') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="rental_price">Prix de location (€) <span class="required" aria-hidden="true">*</span></label>
                    <input type="number" id="rental_price" name="rental_price" class="form-control"
                           value="{{ old('rental_price', $venue->rental_price ?? '') }}"
                           required min="0" step="0.01" aria-required="true">
                    @error('rental_price') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="type">Type <span class="required" aria-hidden="true">*</span></label>
                <select id="type" name="type" class="form-control" required aria-required="true">
                    @foreach(App\Models\Venue::TYPES as $typeOption)
                        <option value="{{ $typeOption }}" {{ old('type', $venue->type ?? '') === $typeOption ? 'selected' : '' }}>
                            {{ App\Models\Venue::TYPE_LABELS[$typeOption] ?? $typeOption }}
                        </option>
                    @endforeach
                </select>
                @error('type') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">{{ $venue ? 'Enregistrer les modifications' : 'Créer le lieu' }}</button>
                <a href="{{ route('venues.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
