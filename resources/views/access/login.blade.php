@extends('layouts.app')

@section('title', 'Connexion')

@section('content')
@php
    // Si le formulaire revient avec une erreur (ou un identifiant déjà saisi), on ouvre directement le 3e panneau.
    $startSlide = ($errors->any() || session('error') || old('login')) ? 2 : 0;
@endphp

<style>
    /* ===== Connexion en 3 panneaux qui défilent de gauche à droite ===== */
    .slides { position: relative; height: 100vh; height: 100dvh; overflow: hidden; background: var(--color-bg); }
    .slides-track {
        display: flex; height: 100%;
        overflow-x: auto; overflow-y: hidden;
        scroll-snap-type: x mandatory;
        overscroll-behavior-x: contain;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }
    .slides-track::-webkit-scrollbar { display: none; }
    .slide {
        flex: 0 0 100%; min-width: 0; height: 100%;
        scroll-snap-align: start; scroll-snap-stop: always;
        overflow-x: hidden; overflow-y: auto;
    }

    /* Panneaux 1 et 2 : on réutilise le fond dégradé, on centre le contenu sans le couper */
    .slides .auth-aside {
        overflow-x: hidden; overflow-y: auto;
        justify-content: flex-start;
        padding: 36px 24px 32px;
        gap: 0;
    }
    .sl-body { margin-block: auto; width: 100%; display: flex; flex-direction: column; align-items: center; gap: 28px; position: relative; z-index: 1; }
    .slides .auth-lead { display: block; max-width: 440px; }
    .slides .auth-stage h2 { font-size: clamp(1.6rem, 4.2vw, 2.4rem); }

    /* Boutons de navigation */
    .sl-actions { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; }
    .sl-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 13px 24px; border-radius: 999px;
        border: 1px solid rgba(255,255,255,0.55);
        background: #fff; color: #3730a3;
        font: inherit; font-weight: 650; font-size: 0.95rem;
        cursor: pointer; box-shadow: 0 10px 24px rgba(15,23,42,0.25);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .sl-btn:hover { transform: translateY(-2px); box-shadow: 0 14px 28px rgba(15,23,42,0.3); }
    .sl-btn:focus-visible { outline: 3px solid #fff; outline-offset: 3px; }
    .sl-btn-ghost { background: rgba(255,255,255,0.12); color: #fff; box-shadow: none; backdrop-filter: blur(6px); }
    .sl-btn .icon { width: 18px; height: 18px; }
    .sl-btn .nudge { animation: sl-nudge 1.6s ease-in-out infinite; }
    @keyframes sl-nudge { 0%, 100% { transform: translateX(0); } 50% { transform: translateX(5px); } }

    /* Panneau 2 : petit logo discret en haut */
    .sl-brand {
        position: relative; z-index: 1; align-self: center; flex-shrink: 0;
        display: inline-flex; align-items: center; gap: 10px;
        font-weight: 700; font-size: 1.05rem; letter-spacing: -0.3px;
        opacity: 0.85; margin-bottom: 18px;
    }
    .sl-brand-mark {
        width: 34px; height: 34px; border-radius: 10px; display: grid; place-items: center;
        background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.35);
    }
    .sl-brand-mark .icon { width: 18px; height: 18px; }
    .sl-title { font-size: clamp(1.4rem, 3.4vw, 2rem); line-height: 1.2; letter-spacing: -0.5px; text-align: center; max-width: 560px; }
    .slides .auth-features li > div > span { display: block; }

    /* Cartes d'exemple (toujours visibles dans ce panneau) */
    .sl-cards { display: flex; gap: 14px; flex-wrap: wrap; justify-content: center; width: 100%; }
    .sl-cards .float-card:nth-child(3) { animation-delay: -1.5s; }

    /* Indicateur de position : fine barre en 3 segments, en haut (ne cache jamais le contenu) */
    .slides-dots {
        position: absolute; top: 12px; left: 0; right: 0; z-index: 5;
        width: calc(100% - 32px); max-width: 520px; margin-inline: auto;
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px;
    }
    .slides-dot {
        position: relative; height: 4px; padding: 0; border: 0; border-radius: 999px;
        background: rgba(255,255,255,0.35); cursor: pointer;
        transition: background 0.25s ease;
    }
    .slides-dot::after { content: ''; position: absolute; inset: -12px 0; }
    .slides-dot:hover { background: rgba(255,255,255,0.6); }
    .slides-dot[aria-current="true"] { background: #fff; }
    .slides-dot:focus-visible { outline: 2px solid #fff; outline-offset: 4px; }
    .slides[data-active="2"] .slides-dot { background: var(--color-border); }
    .slides[data-active="2"] .slides-dot:hover { background: var(--color-text-light); }
    .slides[data-active="2"] .slides-dot[aria-current="true"] { background: var(--color-primary); }
    .slides[data-active="2"] .slides-dot:focus-visible { outline-color: var(--color-primary); }

    /* Le mot qui change reste collé à la virgule (pas de saut de ligne avant « , ») */
    .nb { white-space: nowrap; }

    /* Panneau 3 : formulaire */
    .slides .auth-main {
        position: relative; display: flex; flex-direction: column; align-items: center; justify-content: flex-start;
        padding: 36px 22px 32px;
    }
    .slides .auth-card { margin-block: auto; }
    .slides .auth-mobile-brand { display: flex; }
    .sl-back {
        display: inline-flex; align-items: center; gap: 6px; margin: 0 0 14px -4px; padding: 6px 10px 6px 4px;
        background: none; border: 0; border-radius: 8px; color: var(--color-text-muted);
        font: inherit; font-size: 0.9rem; cursor: pointer;
    }
    .sl-back:hover { color: var(--color-text); background: var(--color-surface-2); }
    .sl-back:focus-visible { outline: 2px solid var(--color-primary); outline-offset: 2px; }
    .sl-back .icon { width: 16px; height: 16px; }

    @media (max-width: 560px) {
        .slides .auth-aside { padding: 32px 16px 28px; }
        .slides .auth-features { grid-template-columns: 1fr; gap: 8px; }
        .slides .auth-features li { flex-direction: row; align-items: center; gap: 12px; padding: 10px 12px; }
        .slides .auth-features li > div > span { font-size: 0.78rem; }
        .sl-cards { gap: 10px; }
        .sl-cards .float-card { min-width: 0; width: 100%; padding: 9px 14px; animation: none; }
        .sl-cards .float-card:nth-child(3) { display: none; }
        .sl-body { gap: 18px; }
        .sl-title { font-size: 1.35rem; }
        .sl-btn { padding: 12px 20px; }
    }
    @media (max-height: 640px) {
        .sl-brand { margin-bottom: 8px; }
        .sl-body { gap: 18px; }
    }
</style>

<div class="slides" id="slides" data-start="{{ $startSlide }}" data-active="{{ $startSlide }}">
    <div class="slides-track" id="slides-track">

        {{-- Panneau 1 : logo Eventia et slogan --}}
        <section class="slide auth-aside" id="slide-accueil" aria-label="Accueil, 1 sur 3">
            <span class="auth-orb o1" aria-hidden="true"></span>
            <span class="auth-orb o2" aria-hidden="true"></span>
            <span class="auth-orb o3" aria-hidden="true"></span>

            <div class="sl-body">
                <div class="auth-stage">
                    <div class="auth-logo" aria-hidden="true">
                        <span class="ring r1"></span><span class="ring r2"></span>
                        <span class="auth-logo-mark"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/><path d="M8 15l2.5 2.5L16 13"/></svg></span>
                        <span class="spark s1"></span><span class="spark s2"></span><span class="spark s3"></span>
                    </div>
                    <p class="auth-wordmark">Eventia</p>
                    <h2>Organisez chaque <span class="nb"><span class="rotator" id="rotator">événement</span>,</span> sans stress.</h2>
                    <p class="auth-lead">Clients, lieux, prestataires et budget réunis au même endroit, pour que rien ne soit oublié.</p>
                </div>

                <div class="sl-actions">
                    <button type="button" class="sl-btn" data-go="1">
                        Découvrir Eventia
                        <svg class="icon nudge" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                    <button type="button" class="sl-btn sl-btn-ghost" data-go="2">Aller à la connexion</button>
                </div>
            </div>
        </section>

        {{-- Panneau 2 : points forts et cartes d'exemple --}}
        <section class="slide auth-aside" id="slide-atouts" aria-label="Points forts, 2 sur 3">
            <span class="auth-orb o1" aria-hidden="true"></span>
            <span class="auth-orb o2" aria-hidden="true"></span>

            <div class="sl-brand" aria-hidden="true">
                <span class="sl-brand-mark"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></span>
                <span>Eventia</span>
            </div>

            <div class="sl-body">
                <h2 class="sl-title">Tout ce qu'il faut pour réussir vos événements</h2>

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

                <div class="sl-cards" aria-hidden="true">
                    <div class="float-card"><span class="dot" style="background:#ec4899;"></span><div><strong>Mariage Bernard</strong><small>26 nov. · Salle des fêtes</small></div></div>
                    <div class="float-card"><span class="dot" style="background:#22c55e;"></span><div><strong>Séminaire Tech Solutions</strong><small>12 déc. · Confirmé</small></div></div>
                    <div class="float-card"><span class="dot" style="background:#f59e0b;"></span><div><strong>Concert de fin d'année</strong><small>18 déc. · Brouillon</small></div></div>
                </div>

                <div class="sl-actions">
                    <button type="button" class="sl-btn sl-btn-ghost" data-go="0">
                        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                        Retour
                    </button>
                    <button type="button" class="sl-btn" data-go="2">
                        Se connecter
                        <svg class="icon nudge" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                </div>
            </div>
        </section>

        {{-- Panneau 3 : formulaire --}}
        <section class="slide auth-main" id="slide-connexion" aria-label="Connexion, 3 sur 3">
            <div class="auth-card">
                <button type="button" class="sl-back" data-go="1">
                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                    Retour
                </button>

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
                               required autocomplete="username" aria-required="true"
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
        </section>
    </div>

    <div class="slides-dots" role="group" aria-label="Aller à un panneau">
        <button type="button" class="slides-dot" data-go="0" aria-label="Accueil"></button>
        <button type="button" class="slides-dot" data-go="1" aria-label="Points forts"></button>
        <button type="button" class="slides-dot" data-go="2" aria-label="Connexion"></button>
    </div>
</div>

<script>
    (function () {
        var root = document.getElementById('slides');
        var track = document.getElementById('slides-track');
        var slides = Array.prototype.slice.call(track.children);
        var dots = Array.prototype.slice.call(root.querySelectorAll('.slides-dot'));
        var loginInput = document.getElementById('login');
        var pw = document.getElementById('password');
        var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var finePointer = window.matchMedia && window.matchMedia('(pointer: fine)').matches;
        var current = 0;

        // ----- Défilement entre les panneaux -----
        function setActive(i) {
            current = i;
            root.setAttribute('data-active', String(i));
            slides.forEach(function (s, k) {
                if (k === i) { s.removeAttribute('inert'); } else { s.setAttribute('inert', ''); }
            });
            dots.forEach(function (d, k) {
                if (k === i) { d.setAttribute('aria-current', 'true'); } else { d.removeAttribute('aria-current'); }
            });
        }

        function focusForm() {
            var target = loginInput.value ? pw : loginInput;
            try { target.focus({ preventScroll: true }); } catch (e) { target.focus(); }
        }

        function go(i, instant) {
            i = Math.max(0, Math.min(slides.length - 1, i));
            setActive(i);
            track.scrollTo({ left: i * track.clientWidth, behavior: (instant || reduce) ? 'instant' : 'smooth' });
            // Sur ordinateur, on place le curseur dans le formulaire ; sur téléphone on évite d'ouvrir le clavier.
            if (i === 2 && !instant && finePointer) { setTimeout(focusForm, reduce ? 0 : 450); }
        }

        document.querySelectorAll('[data-go]').forEach(function (el) {
            el.addEventListener('click', function () { go(parseInt(el.getAttribute('data-go'), 10)); });
        });

        // Le glissement au doigt ou au pavé tactile met à jour le panneau actif
        var settle;
        track.addEventListener('scroll', function () {
            clearTimeout(settle);
            settle = setTimeout(function () {
                var i = Math.round(track.scrollLeft / track.clientWidth);
                if (i !== current) { setActive(i); }
            }, 80);
        }, { passive: true });

        // Flèches gauche / droite du clavier (sauf pendant la saisie)
        document.addEventListener('keydown', function (e) {
            var tag = (e.target && e.target.tagName || '').toLowerCase();
            if (tag === 'input' || tag === 'textarea' || tag === 'select' || e.altKey || e.ctrlKey || e.metaKey) { return; }
            if (e.key === 'ArrowRight') { go(current + 1); }
            else if (e.key === 'ArrowLeft') { go(current - 1); }
        });

        // On reste bien calé sur le panneau si la fenêtre change de taille
        window.addEventListener('resize', function () {
            track.scrollTo({ left: current * track.clientWidth, behavior: 'instant' });
        });

        // Panneau de départ : le formulaire s'il y a une erreur, sinon l'accueil
        var start = parseInt(root.getAttribute('data-start') || '0', 10);
        if (window.location.hash === '#connexion') { start = 2; }
        setActive(start);
        if (start > 0) {
            track.scrollTo({ left: start * track.clientWidth, behavior: 'instant' });
            if (start === 2 && finePointer) { focusForm(); }
        }

        // ----- Formulaire -----
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

        // Mot qui change dans le slogan
        var rot = document.getElementById('rotator');
        if (rot && !reduce) {
            var words = ['mariage', 'séminaire', 'concert', 'conférence', 'événement'], w = 0;
            setInterval(function () {
                rot.classList.add('out');
                setTimeout(function () {
                    w = (w + 1) % words.length;
                    rot.textContent = words[w];
                    rot.classList.remove('out');
                }, 280);
            }, 2600);
        }

        // Comptes de démonstration
        document.querySelectorAll('.demo-chip').forEach(function (btn) {
            btn.addEventListener('click', function () {
                loginInput.value = btn.getAttribute('data-login');
                pw.value = btn.getAttribute('data-password');
                pw.focus();
            });
        });
    })();
</script>
@endsection
