<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Eventia') — Gestion d'événements</title>
    <style>
        /* Reset & base */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --color-primary: #4f46e5;
            --color-primary-dark: #3730a3;
            --color-danger: #dc2626;
            --color-success: #16a34a;
            --color-warning: #d97706;
            --color-bg: #f8fafc;
            --color-surface: #ffffff;
            --color-border: #e2e8f0;
            --color-text: #1e293b;
            --color-text-muted: #64748b;
            --color-text-light: #94a3b8;
            --radius: 8px;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --shadow: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.07), 0 2px 4px rgba(0,0,0,0.06);
        }
        body {
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            background: var(--color-bg);
            color: var(--color-text);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Accessibility: skip link */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 0;
            background: var(--color-primary);
            color: #fff;
            padding: 8px 16px;
            border-radius: 0 0 var(--radius) 0;
            text-decoration: none;
            z-index: 100;
        }
        .skip-link:focus { top: 0; }

        /* Layout */
        .layout-wrapper { display: flex; min-height: 100vh; }
        .sidebar {
            width: 250px;
            background: var(--color-surface);
            border-right: 1px solid var(--color-border);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--color-border);
        }
        .sidebar-header h1 {
            font-size: 1.5rem;
            color: var(--color-primary);
            font-weight: 700;
        }
        .sidebar-header p {
            font-size: 0.8rem;
            color: var(--color-text-muted);
            margin-top: 2px;
        }
        .sidebar-nav {
            flex: 1;
            padding: 12px;
            overflow-y: auto;
        }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: var(--radius);
            text-decoration: none;
            color: var(--color-text);
            font-size: 0.9rem;
            margin-bottom: 4px;
            transition: background 0.15s;
        }
        .sidebar-nav a:hover { background: var(--color-bg); }
        .sidebar-nav a.active { background: var(--color-primary); color: #fff; }
        .sidebar-nav a:focus-visible {
            outline: 2px solid var(--color-primary);
            outline-offset: 2px;
        }
        .sidebar-footer {
            padding: 14px 20px;
            border-top: 1px solid var(--color-border);
            font-size: 0.8rem;
        }
        .sidebar-footer .user-name { font-weight: 600; }
        .sidebar-footer .user-role { color: var(--color-text-muted); }
        .sidebar-footer form { margin-top: 8px; }

        /* Main content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .topbar {
            background: var(--color-surface);
            border-bottom: 1px solid var(--color-border);
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .topbar h2 { font-size: 1.2rem; font-weight: 600; }
        .content { padding: 24px; flex: 1; }

        /* Cards */
        .card {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 20px;
        }
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--color-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-header h3 { font-size: 1.1rem; font-weight: 600; }
        .card-body { padding: 20px; }

        /* Tables */
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 16px; text-align: left; border-bottom: 1px solid var(--color-border); }
        th { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--color-text-muted); font-weight: 600; }
        tbody tr:hover { background: var(--color-bg); }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-admin { background: #ede9fe; color: #5b21b6; }
        .badge-charge { background: #dbeafe; color: #1e40af; }
        .badge-consultant { background: #d1fae5; color: #065f46; }
        .badge-active { background: #d1fae5; color: #065f46; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }
        .badge-particulier { background: #fef3c7; color: #92400e; }
        .badge-entreprise { background: #e0e7ff; color: #3730a3; }
        .badge-traiteur { background: #fef3c7; color: #92400e; }
        .badge-sonorisation { background: #dbeafe; color: #1e40af; }
        .badge-décoration { background: #fce7f3; color: #9d174d; }
        .badge-photographe { background: #d1fae5; color: #065f46; }
        .badge-autre { background: #f3f4f6; color: #374151; }

        /* Forms */
        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .form-group label .required { color: var(--color-danger); }
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
            font-size: 0.95rem;
            background: var(--color-surface);
            color: var(--color-text);
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(79,70,229,0.15);
        }
        .form-control:focus-visible { outline: none; }
        .form-error { color: var(--color-danger); font-size: 0.8rem; margin-top: 4px; }
        .form-help { color: var(--color-text-muted); font-size: 0.8rem; margin-top: 4px; }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 18px;
            border: none;
            border-radius: var(--radius);
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.15s, opacity 0.15s;
        }
        .btn:focus-visible { outline: 2px solid var(--color-primary); outline-offset: 2px; }
        .btn-primary { background: var(--color-primary); color: #fff; }
        .btn-primary:hover { background: var(--color-primary-dark); }
        .btn-danger { background: var(--color-danger); color: #fff; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-secondary { background: var(--color-border); color: var(--color-text); }
        .btn-secondary:hover { background: #cbd5e1; }
        .btn-sm { padding: 6px 12px; font-size: 0.8rem; }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }

        /* Alerts */
        .alert {
            padding: 12px 16px;
            border-radius: var(--radius);
            margin-bottom: 16px;
            font-size: 0.9rem;
        }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

        /* Pagination */
        .pagination { margin-top: 16px; display: flex; gap: 4px; flex-wrap: wrap; }
        .pagination a, .pagination span {
            padding: 6px 12px;
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
            text-decoration: none;
            font-size: 0.85rem;
            color: var(--color-text);
        }
        .pagination a:hover { background: var(--color-bg); }
        .pagination .current { background: var(--color-primary); color: #fff; border-color: var(--color-primary); }

        /* Search & filters */
        .filters {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
            align-items: end;
        }
        .filters .form-group { margin-bottom: 0; }
        .filters .search-input { flex: 1; min-width: 200px; }

        /* Actions cell */
        .actions { display: flex; gap: 6px; flex-wrap: wrap; }

        /* Empty state */
        .empty-state { text-align: center; padding: 40px 20px; color: var(--color-text-muted); }
        .empty-state p { margin-bottom: 12px; }

        /* Login page */
        .login-page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .login-card {
            background: var(--color-surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .login-card h1 { color: var(--color-primary); text-align: center; margin-bottom: 8px; font-size: 2rem; }
        .login-card .subtitle { text-align: center; color: var(--color-text-muted); margin-bottom: 28px; font-size: 0.9rem; }
        .login-card .form-group:last-child { margin-bottom: 0; }
        .login-card .btn { width: 100%; justify-content: center; margin-top: 8px; }

        /* Menu mobile */
        .menu-toggle {
            display: none;
            align-items: center;
            gap: 6px;
            background: var(--color-surface);
            color: var(--color-text);
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
            padding: 8px 12px;
            font-size: 0.95rem;
            font-family: inherit;
            cursor: pointer;
        }
        .menu-toggle:focus-visible { outline: 2px solid var(--color-primary); outline-offset: 2px; }
        .topbar-title { display: flex; align-items: center; gap: 12px; }
        .sidebar-backdrop { display: none; }

        /* Responsive */
        @media (max-width: 768px) {
            .menu-toggle { display: inline-flex; }
            .sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                z-index: 90;
                overflow-y: auto;
                visibility: hidden;
                transform: translateX(-100%);
                transition: transform 0.2s ease, visibility 0.2s;
            }
            .sidebar.open { visibility: visible; transform: translateX(0); }
            .sidebar-backdrop.open {
                display: block;
                position: fixed;
                inset: 0;
                z-index: 80;
                background: rgba(0, 0, 0, 0.4);
            }
            .topbar { padding: 12px 16px; }
            .content { padding: 16px; }
            .filters { flex-direction: column; }
        }
    </style>
</head>
<body>
    <a href="#main-content" class="skip-link">Aller au contenu principal</a>

    @auth
    <div class="layout-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar" role="navigation" aria-label="Navigation principale">
            <div class="sidebar-header">
                <h1>Eventia</h1>
                <p>Gestion d'événements</p>
            </div>
            <nav class="sidebar-nav">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}">
                    Tableau de bord
                </a>
                <a href="{{ route('clients.index') }}" class="{{ request()->routeIs('clients.*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('clients.*') ? 'page' : 'false' }}">
                    Clients
                </a>
                <a href="{{ route('providers.index') }}" class="{{ request()->routeIs('providers.*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('providers.*') ? 'page' : 'false' }}">
                    Prestataires
                </a>
                <a href="{{ route('venues.index') }}" class="{{ request()->routeIs('venues.*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('venues.*') ? 'page' : 'false' }}">
                    Lieux
                </a>
                <a href="{{ route('events.index') }}" class="{{ request()->routeIs('events.*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('events.*') ? 'page' : 'false' }}">
                    Événements
                </a>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('users.*') ? 'page' : 'false' }}">
                    Utilisateurs
                </a>
                @endif
            </nav>
            <div class="sidebar-footer">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">{{ App\Models\User::ROLE_LABELS[auth()->user()->role] ?? auth()->user()->role }}</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm">Se déconnecter</button>
                </form>
            </div>
        </aside>
        <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

        <!-- Main content -->
        <div class="main-content">
            <header class="topbar">
                <div class="topbar-title">
                    <button type="button" class="menu-toggle" id="menu-toggle" aria-label="Ouvrir le menu" aria-controls="sidebar" aria-expanded="false">&#9776; Menu</button>
                    <h2>@yield('title', 'Eventia')</h2>
                </div>
            </header>
            <main id="main-content" class="content" role="main">
                @if(session('success'))
                    <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-error" role="alert">{{ session('error') }}</div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    @else
    <main id="main-content" role="main">
        @if(session('error'))
            <div class="alert alert-error" role="alert" style="max-width:400px;margin:20px auto;">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>
    @endauth

    @auth
    <script>
        (function () {
            var toggle = document.getElementById('menu-toggle');
            var sidebar = document.getElementById('sidebar');
            var backdrop = document.getElementById('sidebar-backdrop');
            if (!toggle || !sidebar || !backdrop) { return; }

            function setOpen(open) {
                sidebar.classList.toggle('open', open);
                backdrop.classList.toggle('open', open);
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                toggle.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
            }

            toggle.addEventListener('click', function () {
                setOpen(!sidebar.classList.contains('open'));
            });
            backdrop.addEventListener('click', function () { setOpen(false); });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') { setOpen(false); }
            });
        })();
    </script>
    @endauth
</body>
</html>
