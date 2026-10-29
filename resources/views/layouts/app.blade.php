<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titre', 'Eventia') - Eventia</title>
    <style>
        :root { --encre:#1b1f24; --fond:#f5f6f8; --carte:#fff; --bord:#6b7280; --bord-doux:#d1d5db; --accent:#1d4ed8; --accent-texte:#fff; --danger:#b42318; --ok:#17603b; --avert:#8a4b00; }
        *, *::before, *::after { box-sizing: border-box; }
        body { margin:0; font:16px/1.5 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; color:var(--encre); background:var(--fond); }
        a { color:var(--accent); }
        :focus-visible { outline:3px solid #f59e0b; outline-offset:2px; }
        .evitement { position:absolute; left:-999px; top:0; background:#000; color:#fff; padding:.75rem 1rem; z-index:100; }
        .evitement:focus { left:0; }
        header.haut { background:#0f172a; color:#fff; }
        header.haut .barre { max-width:1200px; margin:0 auto; padding:.75rem 1rem; display:flex; flex-wrap:wrap; gap:1rem; align-items:center; justify-content:space-between; }
        header.haut a { color:#fff; }
        .marque { font-weight:700; font-size:1.25rem; text-decoration:none; }
        nav ul { list-style:none; margin:0; padding:0; display:flex; flex-wrap:wrap; gap:.25rem; }
        nav a { display:inline-block; padding:.5rem .75rem; min-height:24px; border-radius:6px; text-decoration:none; }
        nav a[aria-current="page"] { background:#1e293b; text-decoration:underline; }
        nav a:hover { background:#1e293b; }
        .compte { display:flex; align-items:center; gap:.75rem; flex-wrap:wrap; }
        main { max-width:1200px; margin:0 auto; padding:1.5rem 1rem 3rem; }
        h1 { font-size:1.6rem; margin:.25rem 0 1rem; }
        h2 { font-size:1.2rem; margin:1.5rem 0 .5rem; }
        .carte { background:var(--carte); border:1px solid var(--bord-doux); border-radius:10px; padding:1.25rem; }
        .grille { display:grid; gap:1rem; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); }
        table { width:100%; border-collapse:collapse; background:var(--carte); }
        caption { text-align:left; font-weight:600; padding:.5rem 0; }
        th, td { text-align:left; padding:.6rem .75rem; border-bottom:1px solid var(--bord-doux); vertical-align:top; }
        thead th { background:#eef1f6; border-bottom:2px solid var(--bord-doux); }
        .tableau-defilant { overflow-x:auto; }
        label { display:block; font-weight:600; margin:1rem 0 .25rem; }
        .aide { color:#4b5563; font-size:.9rem; margin:.15rem 0; }
        input[type=text], input[type=password], input[type=date], input[type=time], input[type=number], input[type=email], input[type=search], select, textarea {
            width:100%; max-width:34rem; padding:.55rem .65rem; border:2px solid var(--bord); border-radius:6px; font:inherit; background:#fff; color:inherit; min-height:44px; }
        input[aria-invalid=true], select[aria-invalid=true], textarea[aria-invalid=true] { border-color:var(--danger); }
        .erreur { color:var(--danger); font-weight:600; margin:.25rem 0 0; }
        .bouton { display:inline-block; padding:.6rem 1rem; min-height:44px; border:2px solid var(--accent); border-radius:6px; background:var(--accent); color:var(--accent-texte); font:inherit; font-weight:600; cursor:pointer; text-decoration:none; }
        .bouton.secondaire { background:#fff; color:var(--accent); }
        .bouton.danger { background:var(--danger); border-color:var(--danger); }
        .actions { display:flex; flex-wrap:wrap; gap:.5rem; margin-top:1.25rem; align-items:center; }
        .actions form { display:inline; margin:0; }
        .message { padding:.75rem 1rem; border-radius:8px; margin:0 0 1rem; border:1px solid; }
        .message.ok { background:#e7f6ee; border-color:#17603b; color:#0f3d26; }
        .message.erreur-bloc { background:#fdecea; border-color:var(--danger); color:#7a1710; }
        .badge { display:inline-block; padding:.1rem .55rem; border-radius:999px; font-size:.85rem; font-weight:700; border:1px solid currentColor; }
        .badge.brouillon { color:#4b5563; } .badge.confirme { color:var(--ok); } .badge.termine { color:#374151; background:#e5e7eb; }
        .badge.annule { color:var(--danger); } .badge.inactif { color:var(--danger); } .badge.actif { color:var(--ok); }
        .badge.alerte { color:var(--avert); }
        .filtre { display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end; }
        .filtre > div { flex:1 1 11rem; } .filtre label { margin-top:0; }
        .pagination { margin-top:1rem; }
        .progres { background:#e5e7eb; border-radius:999px; height:.75rem; overflow:hidden; } .progres span { display:block; height:100%; background:var(--accent); }
        dl.fiche { display:grid; grid-template-columns:max-content 1fr; gap:.4rem 1.25rem; margin:0; } dl.fiche dt { font-weight:600; } dl.fiche dd { margin:0; }
        dialog { border:2px solid var(--encre); border-radius:10px; max-width:28rem; padding:1.25rem; }
        @media print { header.haut, .actions, .evitement, nav { display:none !important; } body { background:#fff; } }
    </style>
</head>
<body>
<a class="evitement" href="#contenu">Aller au contenu principal</a>

<header class="haut">
    <div class="barre">
        <a class="marque" href="{{ auth()->check() ? route('tableau-de-bord') : route('connexion') }}">Eventia</a>

        @auth
            @php
                $menu = [
                    ['Tableau de bord', 'tableau-de-bord', 'consulter'],
                    ['Événements', 'evenements.index', 'consulter'],
                    ['Calendrier', 'calendrier', 'consulter'],
                    ['Clients', 'clients.index', 'consulter'],
                    ['Prestataires', 'prestataires.index', 'consulter'],
                    ['Lieux', 'lieux.index', 'consulter'],
                    ['Utilisateurs', 'utilisateurs.index', 'utilisateurs.gerer'],
                ];
            @endphp
            <nav aria-label="Navigation principale">
                <ul>
                    @foreach ($menu as [$libelle, $route, $permission])
                        @if (\Illuminate\Support\Facades\Route::has($route))
                            @can($permission)
                                <li><a href="{{ route($route) }}" @if (request()->routeIs(explode('.', $route)[0].'*')) aria-current="page" @endif>{{ $libelle }}</a></li>
                            @endcan
                        @endif
                    @endforeach
                </ul>
            </nav>
            <div class="compte">
                <span>{{ auth()->user()->nom }} <span class="badge" style="color:#fff">{{ auth()->user()->role->libelle() }}</span></span>
                <form method="post" action="{{ route('deconnexion') }}">
                    @csrf
                    <button type="submit" class="bouton secondaire">Se déconnecter</button>
                </form>
            </div>
        @endauth
    </div>
</header>

<main id="contenu" tabindex="-1">
    @if (session('statut'))
        <div class="message ok" role="status">{{ session('statut') }}</div>
    @endif

    @include('partials.resume-erreurs')

    @yield('contenu')

    <p class="aide" style="margin-top:2rem">Besoin d'aide ? Contactez l'administrateur de l'application.</p>
</main>

@auth
    <dialog id="avertissement-session" aria-labelledby="titre-session">
        <h2 id="titre-session">Votre session va expirer</h2>
        <p>Pour votre sécurité, vous allez être déconnecté dans moins de deux minutes par manque d'activité.</p>
        <div class="actions">
            <button type="button" class="bouton" id="prolonger-session">Rester connecté</button>
        </div>
    </dialog>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        (function () {
            var duree = {{ (int) config('session.lifetime') }} * 60 * 1000;
            var avance = 2 * 60 * 1000;
            var boite = document.getElementById('avertissement-session');
            var minuteur;
            function armer() {
                clearTimeout(minuteur);
                minuteur = setTimeout(function () { if (boite && boite.showModal) { boite.showModal(); } }, Math.max(duree - avance, 1000));
            }
            document.getElementById('prolonger-session').addEventListener('click', function () {
                fetch('{{ route('session.prolonger') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, credentials: 'same-origin' })
                    .finally(function () { boite.close(); armer(); });
            });
            armer();
        })();
    </script>
@endauth
</body>
</html>
