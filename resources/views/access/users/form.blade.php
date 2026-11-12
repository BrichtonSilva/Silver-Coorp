@extends('layouts.app')

@section('title', $user ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>{{ $user ? 'Modifier : ' . $user->name : 'Créer un nouvel utilisateur' }}</h3>
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

        <form method="POST" action="{{ $user ? route('users.update', $user) : route('users.store') }}" aria-label="Formulaire utilisateur">
            @csrf
            @if($user) @method('PUT') @endif

            <div class="form-group">
                <label for="name">Nom complet <span class="required" aria-hidden="true">*</span></label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}"
                       required maxlength="255" aria-required="true">
                @error('name') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="login">Identifiant <span class="required" aria-hidden="true">*</span></label>
                <input type="text" id="login" name="login" class="form-control" value="{{ old('login', $user->login ?? '') }}"
                       required maxlength="255" aria-required="true" autocomplete="username">
                <div class="form-help">L'identifiant est insensible à la casse.</div>
                @error('login') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}"
                       maxlength="255" autocomplete="email">
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="role">Rôle <span class="required" aria-hidden="true">*</span></label>
                <select id="role" name="role" class="form-control" required aria-required="true">
                    @foreach(App\Models\User::ROLES as $roleOption)
                        <option value="{{ $roleOption }}" {{ old('role', $user->role ?? '') === $roleOption ? 'selected' : '' }}>
                            {{ App\Models\User::ROLE_LABELS[$roleOption] ?? $roleOption }}
                        </option>
                    @endforeach
                </select>
                @error('role') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="password">
                    Mot de passe
                    @if(!$user) <span class="required" aria-hidden="true">*</span> @endif
                    @if($user) <span class="form-help" style="display:inline">(laisser vide pour ne pas changer)</span> @endif
                </label>
                <input type="password" id="password" name="password" class="form-control"
                       {{ $user ? '' : 'required' }} minlength="8" maxlength="255"
                       aria-required="{{ $user ? 'false' : 'true' }}" autocomplete="new-password">
                <div class="form-help">Minimum 8 caractères.</div>
                @error('password') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label style="display:flex;align-items:center;gap:8px;">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $user ? $user->is_active : true) ? 'checked' : '' }}
                           style="width:auto;">
                    Compte actif
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">{{ $user ? 'Enregistrer les modifications' : 'Créer l\'utilisateur' }}</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
