@extends('layouts.app')

@section('title', 'Connexion')

@section('content')
@php
    // Si le formulaire revient avec une erreur (ou un identifiant déjà saisi), on ouvre directement le 4e panneau (formulaire).
    $startSlide = ($errors->any() || session('error') || old('login')) ? 3 : 0;
@endphp

<style>
    /* ===== Connexion en 4 panneaux qui défilent de gauche à droite ===== */
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

    /* Panneau 3 : cartes d'exemple */
    .sl-sub { text-align: center; max-width: 560px; opacity: 0.9; margin-top: -14px; }
    .ex-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; width: 100%; max-width: 780px; }
    .ex-card {
        background: hsl(var(--h, 245) 45% 14% / 0.55); color: var(--ink, #fff); text-align: left;
        border: 1px solid rgba(255,255,255,0.22); border-radius: 16px; padding: 18px;
        box-shadow: 0 18px 40px rgba(0,0,0,0.35);
        backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
        display: flex; flex-direction: column; gap: 8px;
        animation: bob 6s ease-in-out infinite; animation-delay: calc(var(--d, 0s) * -20);
    }
    .ex-card header { display: flex; align-items: center; gap: 10px; margin-bottom: 4px; }
    .ex-card header strong { flex: 1; min-width: 0; font-size: 0.98rem; line-height: 1.25; }
    .ex-card .dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; background: var(--accent, #fde68a); box-shadow: 0 0 10px var(--accent, #fde68a); }
    .ex-badge { flex-shrink: 0; padding: 3px 10px; border-radius: 999px; font-size: 0.72rem; font-weight: 650; border: 1px solid transparent; }
    .ex-badge.ok { background: hsl(var(--h, 245) 90% 80% / 0.2); color: var(--accent, #fde68a); border-color: hsl(var(--h, 245) 90% 80% / 0.35); }
    .ex-badge.draft { background: rgba(255,255,255,0.12); color: rgba(255,255,255,0.85); border-color: rgba(255,255,255,0.3); }
    .ex-meta { display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: rgba(255,255,255,0.82); }
    .ex-meta .icon, .ex-note .icon { width: 15px; height: 15px; flex-shrink: 0; color: var(--accent, #fde68a); }
    .ex-progress { height: 7px; border-radius: 999px; background: rgba(255,255,255,0.18); overflow: hidden; margin-top: 6px; }
    .ex-progress span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, var(--accent, #fde68a), hsl(var(--h, 245) 95% 92%)); }
    .ex-note { display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: rgba(255,255,255,0.7); }

    /* Indicateur de position : fine barre en 3 segments, en haut (ne cache jamais le contenu) */
    .slides-dots {
        position: absolute; top: 12px; left: 0; right: 0; z-index: 5;
        width: calc(100% - 32px); max-width: 520px; margin-inline: auto;
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px;
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
    .slides[data-active="3"] .slides-dot { background: var(--color-border); }
    .slides[data-active="3"] .slides-dot:hover { background: var(--color-text-light); }
    .slides[data-active="3"] .slides-dot[aria-current="true"] { background: var(--color-primary); }
    .slides[data-active="3"] .slides-dot:focus-visible { outline-color: var(--color-primary); }

    /* Le mot qui change reste collé à la virgule (pas de saut de ligne avant « , ») */
    .nb { white-space: nowrap; }

    /* ===== Photos de fond (panneaux 1 et 2) =====
       Fichiers à déposer dans public/images/login/ : accueil.jpg et atouts.jpg.
       Le voile indigo garde le texte lisible ; si une photo est absente, l'ancien dégradé reste visible. */
    /* Les couleurs suivent la photo : le script en bas de page lit chaque image et règle
       --h (teinte dominante), --a1 / --a2 (densité du voile), --accent, --ink et --btn-fg.
       Valeurs par défaut = indigo d'origine (photo absente ou illisible). */
    .slides .auth-aside.has-photo {
        --h: 245; --a1: 0.74; --a2: 0.34;
        --veil: radial-gradient(ellipse 75% 65% at 50% 50%, hsl(var(--h) 47% 20% / var(--a1)) 0%, hsl(var(--h) 47% 20% / var(--a2)) 100%);
        background:
            var(--veil),
            var(--photo) center / cover no-repeat,
            linear-gradient(145deg, #312e81 0%, #4f46e5 55%, #7c3aed 100%);
        color: var(--ink, #fff);
        animation: none;
        transition: color 0.4s ease;
    }
    /* Panneau 2 : plus de contenu, donc voile plus régulier */
    .slides #slide-atouts.has-photo {
        --veil: linear-gradient(180deg, hsl(var(--h) 47% 20% / calc(var(--a1) - 0.2)) 0%, hsl(var(--h) 47% 20% / var(--a1)) 55%, hsl(var(--h) 47% 20% / calc(var(--a1) - 0.15)) 100%);
    }
    .slides .has-photo .rotator { color: var(--accent, #fde68a); }
    .slides .has-photo .sl-btn:not(.sl-btn-ghost) { color: var(--btn-fg, #3730a3); }
    .slides .has-photo .auth-features .fi { color: var(--accent, #fff); }
    .slides .has-photo .auth-orb { display: none; }
    .slides .has-photo .auth-stage h2,
    .slides .has-photo .auth-wordmark,
    .slides .has-photo .auth-lead,
    .slides .has-photo .sl-title { text-shadow: 0 2px 18px rgba(15,23,42,0.5); }
    .slides .has-photo .auth-features li { background: rgba(15,23,42,0.38); border-color: rgba(255,255,255,0.22); }
    .slides .has-photo .float-card { box-shadow: 0 18px 40px rgba(0,0,0,0.4); }

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
        .ex-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
        .ex-card { padding: 12px; gap: 6px; animation: none; }
        .ex-card header { flex-wrap: wrap; gap: 6px 8px; margin-bottom: 2px; }
        .ex-card header strong { flex: 1 1 calc(100% - 20px); font-size: 0.9rem; }
        .ex-badge { order: 3; font-size: 0.68rem; padding: 2px 8px; }
        .ex-meta { font-size: 0.78rem; gap: 6px; }
        .ex-meta .icon, .ex-note .icon { width: 13px; height: 13px; }
        .ex-progress { height: 6px; margin-top: 4px; }
        .ex-note { font-size: 0.72rem; gap: 6px; align-items: flex-start; }
        .sl-sub { margin-top: -8px; font-size: 0.92rem; }
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
        <section class="slide auth-aside has-photo" id="slide-accueil" aria-label="Accueil, 1 sur 4" style="--photo: url('{{ asset('images/login/accueil.jpg') }}');">
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
                    <button type="button" class="sl-btn sl-btn-ghost" data-go="3">Aller à la connexion</button>
                </div>
            </div>
        </section>

        {{-- Panneau 2 : points forts --}}
        <section class="slide auth-aside has-photo" id="slide-atouts" aria-label="Points forts, 2 sur 4" style="--photo: url('{{ asset('images/login/atouts.jpg') }}');">
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

                <div class="sl-actions">
                    <button type="button" class="sl-btn sl-btn-ghost" data-go="0">
                        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                        Retour
                    </button>
                    <button type="button" class="sl-btn" data-go="2">
                        Voir des exemples
                        <svg class="icon nudge" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                </div>
            </div>
        </section>

        {{-- Panneau 3 : cartes d'exemple --}}
        <section class="slide auth-aside has-photo" id="slide-exemples" aria-label="Exemples, 3 sur 4" style="--photo: url('{{ asset('images/login/exemples.jpg') }}');">
            <span class="auth-orb o1" aria-hidden="true"></span>
            <span class="auth-orb o3" aria-hidden="true"></span>

            <div class="sl-brand" aria-hidden="true">
                <span class="sl-brand-mark"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></span>
                <span>Eventia</span>
            </div>

            <div class="sl-body">
                <h2 class="sl-title">Vos événements, d'un coup d'œil</h2>
                <p class="sl-sub">Statut, lieu, date et avancement de la checklist : tout est suivi pour vous.</p>

                <div class="ex-grid" aria-hidden="true">
                    <article class="ex-card" style="--d:.05s">
                        <header>
                            <span class="dot"></span>
                            <strong>WPoint Tour Tiakola</strong>
                            <span class="ex-badge ok">Confirmé</span>
                        </header>
                        <p class="ex-meta"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>Décembre 2026</p>
                        <p class="ex-meta"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>Tournée</p>
                        <div class="ex-progress" role="img" aria-label="Checklist à 80 %"><span style="width:80%"></span></div>
                        <p class="ex-note"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>8 tâches sur 10 terminées</p>
                    </article>
                    <article class="ex-card" style="--d:.15s">
                        <header>
                            <span class="dot"></span>
                            <strong>Festichill 2027</strong>
                            <span class="ex-badge ok">Confirmé</span>
                        </header>
                        <p class="ex-meta"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>2027</p>
                        <p class="ex-meta"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>Majestic Cinéma</p>
                        <div class="ex-progress" role="img" aria-label="Checklist à 55 %"><span style="width:55%"></span></div>
                        <p class="ex-note"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>6 tâches sur 11 terminées</p>
                    </article>
                    <article class="ex-card" style="--d:.25s">
                        <header>
                            <span class="dot"></span>
                            <strong>Chill and Groove 2027</strong>
                            <span class="ex-badge ok">Confirmé</span>
                        </header>
                        <p class="ex-meta"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>2027</p>
                        <p class="ex-meta"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>Palais des Congrès</p>
                        <div class="ex-progress" role="img" aria-label="Checklist à 35 %"><span style="width:35%"></span></div>
                        <p class="ex-note"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>4 tâches sur 11 terminées</p>
                    </article>
                    <article class="ex-card" style="--d:.35s">
                        <header>
                            <span class="dot"></span>
                            <strong>Yardland 2027</strong>
                            <span class="ex-badge draft">Brouillon</span>
                        </header>
                        <p class="ex-meta"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>2027</p>
                        <p class="ex-meta"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>Stade de France</p>
                        <div class="ex-progress" role="img" aria-label="Checklist à 15 %"><span style="width:15%"></span></div>
                        <p class="ex-note"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>2 tâches sur 13 terminées</p>
                    </article>
                </div>

                <div class="sl-actions">
                    <button type="button" class="sl-btn sl-btn-ghost" data-go="1">
                        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                        Retour
                    </button>
                    <button type="button" class="sl-btn" data-go="3">
                        Se connecter
                        <svg class="icon nudge" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                </div>
            </div>
        </section>

        {{-- Panneau 4 : formulaire --}}
        <section class="slide auth-main" id="slide-connexion" aria-label="Connexion, 4 sur 4">
            <div class="auth-card">
                <button type="button" class="sl-back" data-go="2">
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
        <button type="button" class="slides-dot" data-go="2" aria-label="Exemples"></button>
        <button type="button" class="slides-dot" data-go="3" aria-label="Connexion"></button>
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
            if (i === 3 && !instant && finePointer) { setTimeout(focusForm, reduce ? 0 : 450); }
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

        // Panneau de départ : le formulaire (4e) s'il y a une erreur, sinon l'accueil
        var start = parseInt(root.getAttribute('data-start') || '0', 10);
        if (window.location.hash === '#connexion') { start = 3; }
        setActive(start);
        if (start > 0) {
            track.scrollTo({ left: start * track.clientWidth, behavior: 'instant' });
            if (start === 3 && finePointer) { focusForm(); }
        }

        // ----- Couleurs accordées aux photos -----
        // Lit chaque photo (hébergée sur le même site) pour en tirer la teinte dominante et la luminosité,
        // puis règle le voile, l'accent du slogan, la couleur du texte et celle des boutons.
        function tuneToPhoto(section) {
            var m = /url\(\s*["']?([^"')]+)["']?\s*\)/.exec(section.style.getPropertyValue('--photo'));
            if (!m) { return; }
            var img = new Image();
            img.onload = function () {
                try {
                    var W = 48, H = 27, c = document.createElement('canvas');
                    c.width = W; c.height = H;
                    var ctx = c.getContext('2d', { willReadFrequently: true });
                    ctx.drawImage(img, 0, 0, W, H);
                    var d = ctx.getImageData(0, 0, W, H).data;
                    var bins = [], sx = [], sy = [], i, lum = 0, weight = 0, n = W * H;
                    for (i = 0; i < 12; i++) { bins.push(0); sx.push(0); sy.push(0); }
                    for (i = 0; i < d.length; i += 4) {
                        var r = d[i] / 255, g = d[i + 1] / 255, b = d[i + 2] / 255;
                        var mx = Math.max(r, g, b), mn = Math.min(r, g, b), l = (mx + mn) / 2, delta = mx - mn;
                        lum += 0.2126 * r + 0.7152 * g + 0.0722 * b;
                        if (delta < 0.04) { continue; }                       // gris : pas de teinte
                        var h = mx === r ? ((g - b) / delta) % 6 : (mx === g ? (b - r) / delta + 2 : (r - g) / delta + 4);
                        h = ((h * 60) + 360) % 360;
                        var w = (delta / (1 - Math.abs(2 * l - 1) + 0.001)) * (1 - Math.abs(2 * l - 1));  // couleurs vives d'abord
                        var k = Math.floor(h / 30) % 12, rad = h * Math.PI / 180;
                        bins[k] += w; sx[k] += w * Math.cos(rad); sy[k] += w * Math.sin(rad); weight += w;
                    }
                    var best = 0;
                    for (i = 1; i < 12; i++) { if (bins[i] > bins[best]) { best = i; } }
                    var lumAvg = lum / n;                                      // 0 = sombre, 1 = clair
                    var hue = 245;                                             // indigo si la photo est presque grise
                    if (weight > n * 0.02) {
                        hue = Math.round(((Math.atan2(sy[best], sx[best]) * 180 / Math.PI) + 360) % 360);
                    }
                    // Plus la photo est claire, plus le voile est dense pour garder le texte lisible
                    var a1 = Math.min(0.88, Math.max(0.68, 0.6 + 0.35 * lumAvg));
                    var a2 = Math.min(0.5, Math.max(0.26, 0.2 + 0.3 * lumAvg));
                    var st = section.style;
                    st.setProperty('--h', String(hue));
                    st.setProperty('--a1', a1.toFixed(2));
                    st.setProperty('--a2', a2.toFixed(2));
                    st.setProperty('--accent', 'hsl(' + hue + ' 95% 82%)');
                    st.setProperty('--ink', 'hsl(' + hue + ' 70% 97%)');
                    st.setProperty('--btn-fg', 'hsl(' + hue + ' 65% 24%)');
                } catch (e) { /* lecture impossible : on garde l'indigo d'origine */ }
            };
            img.src = m[1];
        }
        document.querySelectorAll('.has-photo').forEach(tuneToPhoto);

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
