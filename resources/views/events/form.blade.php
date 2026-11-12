@extends('layouts.app')

@section('title', $event ? 'Modifier l\'événement' : 'Nouvel événement')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>{{ $event ? 'Modifier : ' . $event->client?->name : 'Créer un nouvel événement' }}</h3>
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

        <form method="POST" action="{{ $event ? route('events.update', $event) : route('events.store') }}" aria-label="Formulaire événement">
            @csrf
            @if($event) @method('PUT') @endif

            <div class="detail-grid">
                <div>
                    <div class="form-group">
                        <label for="client_id">Client <span class="required" aria-hidden="true">*</span></label>
                        <select id="client_id" name="client_id" class="form-control" required aria-required="true">
                            <option value="">Sélectionner un client</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id', $event->client_id ?? '') == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_id') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="type">Type d'événement <span class="required" aria-hidden="true">*</span></label>
                        <select id="type" name="type" class="form-control" required aria-required="true">
                            @foreach(App\Models\Event::TYPES as $typeOption)
                                <option value="{{ $typeOption }}" {{ old('type', $event->type ?? '') === $typeOption ? 'selected' : '' }}>
                                    {{ App\Models\Event::TYPE_LABELS[$typeOption] ?? $typeOption }}
                                </option>
                            @endforeach
                        </select>
                        @error('type') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="date">Date <span class="required" aria-hidden="true">*</span></label>
                        <input type="date" id="date" name="date" class="form-control"
                               value="{{ old('date', isset($event) ? $event->date->format('Y-m-d') : '') }}"
                               required aria-required="true">
                        @error('date') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label for="start_time">Heure de début <span class="required" aria-hidden="true">*</span></label>
                            <input type="time" id="start_time" name="start_time" class="form-control"
                                   value="{{ old('start_time', isset($event) ? \Illuminate\Support\Carbon::parse($event->start_time)->format('H:i') : '') }}"
                                   required aria-required="true">
                            @error('start_time') <div class="form-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label for="end_time">Heure de fin <span class="required" aria-hidden="true">*</span></label>
                            <input type="time" id="end_time" name="end_time" class="form-control"
                                   value="{{ old('end_time', isset($event) ? \Illuminate\Support\Carbon::parse($event->end_time)->format('H:i') : '') }}"
                                   required aria-required="true">
                            @error('end_time') <div class="form-error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label for="venue_id">Lieu <span class="required" aria-hidden="true">*</span></label>
                        <select id="venue_id" name="venue_id" class="form-control" required aria-required="true">
                            <option value="">Sélectionner un lieu</option>
                            @foreach($venues as $venue)
                                <option value="{{ $venue->id }}" {{ old('venue_id', $event->venue_id ?? '') == $venue->id ? 'selected' : '' }}>
                                    {{ $venue->name }} (cap. {{ $venue->capacity }})
                                </option>
                            @endforeach
                        </select>
                        @error('venue_id') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="guest_count">Nombre d'invités <span class="required" aria-hidden="true">*</span></label>
                        <input type="number" id="guest_count" name="guest_count" class="form-control" min="1"
                               value="{{ old('guest_count', $event->guest_count ?? '') }}"
                               required aria-required="true">
                        @error('guest_count') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="budget">Budget (€) <span class="required" aria-hidden="true">*</span></label>
                        <input type="number" id="budget" name="budget" class="form-control" min="0" step="0.01"
                               value="{{ old('budget', $event->budget ?? '') }}"
                               required aria-required="true">
                        @error('budget') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="charged_user_id">Chargé d'événements <span class="required" aria-hidden="true">*</span></label>
                        <select id="charged_user_id" name="charged_user_id" class="form-control" required aria-required="true">
                            <option value="">Sélectionner un chargé</option>
                            @foreach($charges as $charge)
                                <option value="{{ $charge->id }}" {{ old('charged_user_id', $event->charged_user_id ?? '') == $charge->id ? 'selected' : '' }}>
                                    {{ $charge->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('charged_user_id') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">{{ $event ? 'Enregistrer les modifications' : 'Créer l\'événement' }}</button>
                <a href="{{ route('events.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
