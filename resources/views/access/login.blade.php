@extends('layouts.app')

@section('title', 'Connexion')

@section('content')
<div class="login-page">
    <div class="login-card">
        <h1>Eventia</h1>
        <p class="subtitle">Gestion d'événements — Connectez-vous</p>

        @if(session('error'))
            <div class="alert alert-error" role="alert">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-error" role="alert">
                <ul style="list-style:none;padding:0;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" aria-label="Formulaire de connexion">
            @csrf
            <div class="form-group">
                <label for="login">Identifiant <span class="required" aria-hidden="true">*</span></label>
                <input type="text" id="login" name="login" class="form-control" value="{{ old('login') }}"
                       required autofocus autocomplete="username" aria-required="true"
                       maxlength="255">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe <span class="required" aria-hidden="true">*</span></label>
                <input type="password" id="password" name="password" class="form-control"
                       required autocomplete="current-password" aria-required="true"
                       maxlength="255">
            </div>
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:6px;font-weight:400;font-size:0.85rem;">
                    <input type="checkbox" name="remember" value="1" style="width:auto;">
                    Se souvenir de moi
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Se connecter</button>
        </form>
    </div>
</div>
@endsection
