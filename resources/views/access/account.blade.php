@extends('layouts.app')

@section('title', 'Mon compte')

@section('content')
<div class="account-grid">
    <div class="card">
        <div class="card-header"><h3>Mon profil</h3></div>
        <div class="card-body">
            <div class="account-id">
                <div class="avatar avatar-lg" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
                <div>
                    <div class="account-name">{{ $user->name }}</div>
                    <span class="badge badge-info">{{ App\Models\User::ROLE_LABELS[$user->role] ?? $user->role }}</span>
                </div>
            </div>
            <dl class="account-list">
                <div><dt class="detail-label">Identifiant</dt><dd>{{ $user->login }}</dd></div>
                <div><dt class="detail-label">Email</dt><dd>{{ $user->email ?: '—' }}</dd></div>
            </dl>
            <p class="form-help">Pour modifier votre nom, votre identifiant ou votre rôle, contactez un administrateur.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3>Changer mon mot de passe</h3></div>
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

            <form method="POST" action="{{ route('account.password') }}" id="password-form" aria-label="Changer mon mot de passe">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="current_password">Mot de passe actuel <span class="required" aria-hidden="true">*</span></label>
                    <div class="input-wrap">
                        <input type="password" id="current_password" name="current_password" class="form-control"
                               required autocomplete="current-password" aria-required="true" maxlength="255">
                        <button type="button" class="pw-toggle" data-target="current_password" aria-label="Afficher le mot de passe" aria-pressed="false">
                            <svg class="icon eye-on" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg><svg class="icon eye-off" hidden viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 19c-6.5 0-10-7-10-7a18.5 18.5 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 5c6.5 0 10 7 10 7a18.5 18.5 0 0 1-2.16 3.19"/><path d="M1 1l22 22"/></svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Nouveau mot de passe <span class="required" aria-hidden="true">*</span></label>
                    <div class="input-wrap">
                        <input type="password" id="password" name="password" class="form-control"
                               required minlength="8" maxlength="255" autocomplete="new-password" aria-required="true"
                               aria-describedby="pw-help">
                        <button type="button" class="pw-toggle" data-target="password" aria-label="Afficher le mot de passe" aria-pressed="false">
                            <svg class="icon eye-on" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg><svg class="icon eye-off" hidden viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 19c-6.5 0-10-7-10-7a18.5 18.5 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 5c6.5 0 10 7 10 7a18.5 18.5 0 0 1-2.16 3.19"/><path d="M1 1l22 22"/></svg>
                        </button>
                    </div>
                    <div class="pw-meter" aria-hidden="true"><span id="pw-meter-bar"></span></div>
                    <div class="form-help" id="pw-help">Minimum 8 caractères, différent de l'actuel. <span id="pw-strength"></span></div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmer le nouveau mot de passe <span class="required" aria-hidden="true">*</span></label>
                    <div class="input-wrap">
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                               required minlength="8" maxlength="255" autocomplete="new-password" aria-required="true">
                        <button type="button" class="pw-toggle" data-target="password_confirmation" aria-label="Afficher le mot de passe" aria-pressed="false">
                            <svg class="icon eye-on" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg><svg class="icon eye-off" hidden viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 19c-6.5 0-10-7-10-7a18.5 18.5 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 5c6.5 0 10 7 10 7a18.5 18.5 0 0 1-2.16 3.19"/><path d="M1 1l22 22"/></svg>
                        </button>
                    </div>
                    <div class="form-error" id="pw-mismatch" role="status" hidden>Les deux mots de passe ne correspondent pas.</div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="password-submit">Enregistrer le mot de passe</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .account-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.4fr); gap: 20px; align-items: start; }
    .account-id { display: flex; align-items: center; gap: 14px; margin-bottom: 20px; }
    .avatar-lg { width: 56px; height: 56px; font-size: 1.3rem; }
    .account-name { font-weight: 700; font-size: 1.1rem; margin-bottom: 4px; }
    .account-list { display: grid; gap: 14px; margin-bottom: 16px; }
    .account-list dd { margin: 2px 0 0; word-break: break-word; }
    .pw-meter { height: 5px; border-radius: 999px; background: var(--color-surface-2); overflow: hidden; margin-top: 8px; }
    .pw-meter span { display: block; height: 100%; width: 0; border-radius: inherit; background: var(--color-danger); transition: width 0.25s ease, background 0.25s ease; }
    @media (max-width: 900px) { .account-grid { grid-template-columns: 1fr; } }
</style>

<script>
    (function () {
        // Afficher / masquer chaque champ
        document.querySelectorAll('.pw-toggle[data-target]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.getAttribute('data-target'));
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                btn.setAttribute('aria-label', show ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
                var on = btn.querySelector('.eye-on'), off = btn.querySelector('.eye-off');
                if (show) { on.setAttribute('hidden', ''); off.removeAttribute('hidden'); }
                else { off.setAttribute('hidden', ''); on.removeAttribute('hidden'); }
                input.focus();
            });
        });

        var pw = document.getElementById('password');
        var conf = document.getElementById('password_confirmation');
        var bar = document.getElementById('pw-meter-bar');
        var label = document.getElementById('pw-strength');
        var mismatch = document.getElementById('pw-mismatch');

        // Indicateur de robustesse (indicatif : seule la longueur minimale est exigée par le serveur)
        function strength(v) {
            var s = 0;
            if (v.length >= 8) { s++; }
            if (v.length >= 12) { s++; }
            if (/[a-z]/.test(v) && /[A-Z]/.test(v)) { s++; }
            if (/\d/.test(v)) { s++; }
            if (/[^A-Za-z0-9]/.test(v)) { s++; }
            return Math.min(s, 4);
        }
        pw.addEventListener('input', function () {
            var v = pw.value, s = v ? strength(v) : 0;
            var texts = ['', 'Faible', 'Moyen', 'Bon', 'Excellent'];
            var colors = ['var(--color-danger)', 'var(--color-danger)', 'var(--color-warning)', 'var(--color-success)', 'var(--color-success)'];
            bar.style.width = (s * 25) + '%';
            bar.style.background = colors[s];
            label.textContent = v ? '· Robustesse : ' + texts[s] : '';
            check();
        });

        function check() {
            var bad = conf.value.length > 0 && conf.value !== pw.value;
            if (bad) { mismatch.removeAttribute('hidden'); } else { mismatch.setAttribute('hidden', ''); }
            return !bad;
        }
        conf.addEventListener('input', check);

        document.getElementById('password-form').addEventListener('submit', function (e) {
            if (!check()) { e.preventDefault(); conf.focus(); return; }
            var b = document.getElementById('password-submit');
            b.disabled = true; b.textContent = 'Enregistrement…';
        });
        window.addEventListener('pageshow', function () {
            var b = document.getElementById('password-submit');
            b.disabled = false; b.textContent = 'Enregistrer le mot de passe';
        });
    })();
</script>
@endsection
