@extends('layouts.app')

@section('title', 'Connexion')

@section('content')
<div class="auth-shell">
    {{-- Panneau de présentation (masqué sur petit écran) --}}
    <section class="auth-aside" aria-label="Présentation d'Eventia">
        <div class="auth-brand">
            <div class="brand-mark"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></div>
            <span>Eventia</span>
        </div>

        <div class="auth-hero">
            <h2>Organisez chaque événement, sans stress.</h2>
            <p>Clients, lieux, prestataires et budget réunis au même endroit, pour que rien ne soit oublié.</p>
            <ul class="auth-features">
                <li>
                    <span class="fi"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M9 16l2 2 4-4"/></svg></span>
                    <div><strong>Zéro double réservation</strong><span>Un lieu ou un prestataire ne peut pas être pris deux fois en même temps.</span></div>
                </li>
                <li>
                    <span class="fi"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 12V8H6a2 2 0 0 1 0-4h12v4"/><path d="M4 6v12a2 2 0 0 0 2 2h14v-4"/><path d="M18 12a2 2 0 0 0 0 4h4v-4z"/></svg></span>
                    <div><strong>Budget sous contrôle</strong><span>Dépenses réelles, reste à dépenser et alerte de dépassement.</span></div>
                </li>
                <li>
                    <span class="fi"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></span>
                    <div><strong>Checklist de préparation</strong><span>Chaque tâche a un responsable, une échéance et un suivi.</span></div>
                </li>
                <li>
                    <span class="fi"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg></span>
                    <div><strong>Alertes en temps réel</strong><span>Prestataire non confirmé, tâche en retard : tout est signalé.</span></div>
                </li>
            </ul>
        </div>

        <div class="auth-floats" aria-hidden="true">
            <div class="float-card"><span class="dot" style="background:#ec4899;"></span><div><strong>Mariage Bernard</strong><small>26 nov. · Salle des fêtes</small></div></div>
            <div class="float-card"><span class="dot" style="background:#22c55e;"></span><div><strong>Séminaire Tech Solutions</strong><small>12 déc. · Confirmé</small></div></div>
        </div>

        <p class="auth-foot">Eventia · Gestion d'événements</p>
    </section>

    {{-- Formulaire --}}
    <div class="auth-main">
        <div class="auth-card">
            <div class="auth-mobile-brand">
                <div class="brand-mark"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></div>
                <span>Eventia</span>
            </div>

            <h1>Connexion</h1>
            <p class="subtitle">Accédez à votre espace de gestion d'événements.</p>

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

            <form method="POST" action="{{ route('login') }}" id="login-form" aria-label="Formulaire de connexion">
                @csrf
                <div class="form-group">
                    <label for="login">Identifiant <span class="required" aria-hidden="true">*</span></label>
                    <input type="text" id="login" name="login" class="form-control" value="{{ old('login') }}"
                           required autofocus autocomplete="username" aria-required="true"
                           maxlength="255">
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe <span class="required" aria-hidden="true">*</span></label>
                    <div class="input-wrap">
                        <input type="password" id="password" name="password" class="form-control"
                               required autocomplete="current-password" aria-required="true"
                               aria-describedby="caps-hint" maxlength="255">
                        <button type="button" class="pw-toggle" id="pw-toggle" aria-label="Afficher le mot de passe" aria-pressed="false" aria-controls="password">
                            <svg class="icon eye-on" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg><svg class="icon eye-off" hidden viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 19c-6.5 0-10-7-10-7a18.5 18.5 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 5c6.5 0 10 7 10 7a18.5 18.5 0 0 1-2.16 3.19"/><path d="M1 1l22 22"/></svg>
                        </button>
                    </div>
                    <div class="caps-hint" id="caps-hint" role="status">Verrouillage des majuscules activé</div>
                </div>
                <div class="form-group">
                    <label class="remember">
                        <input type="checkbox" name="remember" value="1">
                        Se souvenir de moi
                    </label>
                </div>
                <button type="submit" class="btn btn-primary btn-submit" id="login-submit"><span class="btn-label">Se connecter</span></button>
            </form>

            @if(config('demo.enabled'))
            <div class="demo-box">
                <h2>Comptes de démonstration</h2>
                <p>Touchez un compte pour remplir le formulaire, puis « Se connecter ».</p>
                <div class="demo-list">
                    @foreach(config('demo.accounts') as $account)
                    <button type="button" class="demo-chip" data-login="{{ $account['login'] }}" data-password="{{ $account['password'] }}">
                        <span><strong>{{ $account['name'] }}</strong><small>{{ $account['role'] }}</small></span>
                        <code>{{ $account['login'] }}</code>
                    </button>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
    (function () {
        var pw = document.getElementById('password');
        var toggle = document.getElementById('pw-toggle');
        var caps = document.getElementById('caps-hint');
        var form = document.getElementById('login-form');
        var submit = document.getElementById('login-submit');
        var label = submit.querySelector('.btn-label');

        // Afficher / masquer le mot de passe
        toggle.addEventListener('click', function () {
            var show = pw.type === 'password';
            pw.type = show ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
            toggle.setAttribute('aria-label', show ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
            var on = toggle.querySelector('.eye-on');
            var off = toggle.querySelector('.eye-off');
            if (show) { on.setAttribute('hidden', ''); off.removeAttribute('hidden'); }
            else { off.setAttribute('hidden', ''); on.removeAttribute('hidden'); }
            pw.focus();
        });

        // Verrouillage des majuscules
        function checkCaps(e) {
            if (e.getModifierState) { caps.classList.toggle('show', e.getModifierState('CapsLock')); }
        }
        pw.addEventListener('keydown', checkCaps);
        pw.addEventListener('keyup', checkCaps);
        pw.addEventListener('blur', function () { caps.classList.remove('show'); });

        // Bouton en attente pendant l'envoi
        form.addEventListener('submit', function () {
            submit.classList.add('loading');
            submit.setAttribute('aria-busy', 'true');
            label.textContent = 'Connexion…';
        });
        window.addEventListener('pageshow', function () {
            submit.classList.remove('loading');
            submit.removeAttribute('aria-busy');
            label.textContent = 'Se connecter';
        });

        // Comptes de démonstration
        document.querySelectorAll('.demo-chip').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('login').value = btn.getAttribute('data-login');
                pw.value = btn.getAttribute('data-password');
                pw.focus();
            });
        });
    })();
</script>
@endsection
