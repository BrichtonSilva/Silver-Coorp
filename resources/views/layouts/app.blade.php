<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">
    <title>@yield('title', 'Eventia') — Gestion d'événements</title>
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('eventia-theme');
                if (saved === 'dark' || saved === 'light') { document.documentElement.setAttribute('data-theme', saved); }
            } catch (e) { /* stockage indisponible : on garde le thème du système */ }
        })();
    </script>
    <style>
        /* ===== Thème ===== */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            color-scheme: light;
            --color-primary: #4f46e5;
            --color-primary-dark: #4338ca;
            --color-primary-soft: #eef2ff;
            --btn-primary: #4f46e5;
            --btn-primary-hover: #4338ca;
            --color-danger: #dc2626;
            --color-success: #16a34a;
            --color-warning: #d97706;
            --color-bg: #f5f6fb;
            --color-surface: #ffffff;
            --color-surface-2: #f8fafc;
            --color-border: #e5e7eb;
            --color-text: #0f172a;
            --color-text-muted: #64748b;
            --color-text-light: #94a3b8;
            --radius: 12px;
            --radius-sm: 8px;
            --shadow-sm: 0 1px 2px rgba(15,23,42,0.05);
            --shadow: 0 1px 3px rgba(15,23,42,0.08), 0 1px 2px rgba(15,23,42,0.05);
            --shadow-md: 0 10px 25px rgba(15,23,42,0.10), 0 4px 10px rgba(15,23,42,0.05);
            --info-bg: #dbeafe;    --info-fg: #1e40af;
            --success-bg: #d1fae5; --success-fg: #065f46;
            --danger-bg: #fee2e2;  --danger-fg: #991b1b;
            --warning-bg: #fef3c7; --warning-fg: #92400e;
            --neutral-bg: #f1f5f9; --neutral-fg: #334155;
            --indigo-bg: #e0e7ff;  --indigo-fg: #3730a3;
            --pink-bg: #fce7f3;    --pink-fg: #9d174d;
            --violet-bg: #ede9fe;  --violet-fg: #5b21b6;
            --overdue-bg: #fef2f2;
        }
        @media (prefers-color-scheme: dark) {
            :root:not([data-theme="light"]) {
                color-scheme: dark;
                --color-primary: #a5b4fc;
                --color-primary-dark: #818cf8;
                --color-primary-soft: #1e2350;
                --color-danger: #f87171;
                --color-success: #4ade80;
                --color-warning: #fbbf24;
                --color-bg: #0b1020;
                --color-surface: #121a2f;
                --color-surface-2: #0f1629;
                --color-border: #25314f;
                --color-text: #e6eaf5;
                --color-text-muted: #9aa7c4;
                --color-text-light: #6b7a9c;
                --shadow-sm: 0 1px 2px rgba(0,0,0,0.4);
                --shadow: 0 1px 3px rgba(0,0,0,0.45);
                --shadow-md: 0 10px 25px rgba(0,0,0,0.5);
                --info-bg: #172554;    --info-fg: #93c5fd;
                --success-bg: #052e1c; --success-fg: #6ee7b7;
                --danger-bg: #3b0f12;  --danger-fg: #fca5a5;
                --warning-bg: #3a2305; --warning-fg: #fcd34d;
                --neutral-bg: #1b2440; --neutral-fg: #cbd5e1;
                --indigo-bg: #1e2457;  --indigo-fg: #a5b4fc;
                --pink-bg: #3b1030;    --pink-fg: #f9a8d4;
                --violet-bg: #2a1a57;  --violet-fg: #c4b5fd;
                --overdue-bg: #2a1215;
            }
        }
        :root[data-theme="dark"] {
            color-scheme: dark;
            --color-primary: #a5b4fc;
            --color-primary-dark: #818cf8;
            --color-primary-soft: #1e2350;
            --color-danger: #f87171;
            --color-success: #4ade80;
            --color-warning: #fbbf24;
            --color-bg: #0b1020;
            --color-surface: #121a2f;
            --color-surface-2: #0f1629;
            --color-border: #25314f;
            --color-text: #e6eaf5;
            --color-text-muted: #9aa7c4;
            --color-text-light: #6b7a9c;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.4);
            --shadow: 0 1px 3px rgba(0,0,0,0.45);
            --shadow-md: 0 10px 25px rgba(0,0,0,0.5);
            --info-bg: #172554;    --info-fg: #93c5fd;
            --success-bg: #052e1c; --success-fg: #6ee7b7;
            --danger-bg: #3b0f12;  --danger-fg: #fca5a5;
            --warning-bg: #3a2305; --warning-fg: #fcd34d;
            --neutral-bg: #1b2440; --neutral-fg: #cbd5e1;
            --indigo-bg: #1e2457;  --indigo-fg: #a5b4fc;
            --pink-bg: #3b1030;    --pink-fg: #f9a8d4;
            --violet-bg: #2a1a57;  --violet-fg: #c4b5fd;
            --overdue-bg: #2a1215;
        }
        html { -webkit-text-size-adjust: 100%; }
        body {
            font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: var(--color-bg);
            color: var(--color-text);
            line-height: 1.6;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }
        a { color: var(--color-primary); }
        .icon[hidden] { display: none; }
        .icon { width: 18px; height: 18px; flex-shrink: 0; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; }
        .link { color: var(--color-primary); text-decoration: none; font-weight: 500; }
        .link:hover { text-decoration: underline; }

        /* Accessibilité : lien d'évitement */
        .skip-link {
            position: absolute;
            top: -48px;
            left: 0;
            background: var(--btn-primary);
            color: #fff;
            padding: 10px 16px;
            border-radius: 0 0 var(--radius-sm) 0;
            text-decoration: none;
            z-index: 200;
        }
        .skip-link:focus { top: 0; }

        /* ===== Mise en page ===== */
        .layout-wrapper { display: flex; min-height: 100vh; }
        .sidebar {
            width: 260px;
            background: var(--color-surface);
            border-right: 1px solid var(--color-border);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar-header {
            padding: 22px 20px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand-mark {
            width: 40px; height: 40px;
            border-radius: 12px;
            display: grid; place-items: center;
            background: linear-gradient(135deg, #6366f1, #4338ca);
            color: #fff;
            box-shadow: 0 6px 14px rgba(79,70,229,0.35);
        }
        .brand-mark .icon { width: 22px; height: 22px; }
        .sidebar-header h1 { font-size: 1.25rem; font-weight: 700; letter-spacing: -0.2px; line-height: 1.2; }
        .sidebar-header p { font-size: 0.78rem; color: var(--color-text-muted); }
        .sidebar-nav { padding: 8px 12px; flex: 1; display: flex; flex-direction: column; gap: 2px; }
        .nav-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-text-light); padding: 12px 10px 6px; font-weight: 600; }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 10px;
            color: var(--color-text-muted);
            text-decoration: none;
            font-size: 0.93rem;
            font-weight: 500;
            transition: background 0.15s, color 0.15s;
            position: relative;
        }
        .sidebar-nav a:hover { background: var(--color-surface-2); color: var(--color-text); }
        .sidebar-nav a.active { background: var(--color-primary-soft); color: var(--color-primary); font-weight: 600; }
        .sidebar-nav a.active::before {
            content: '';
            position: absolute;
            left: -12px; top: 8px; bottom: 8px;
            width: 4px;
            border-radius: 0 4px 4px 0;
            background: var(--color-primary);
        }
        .sidebar-nav a:focus-visible { outline: 2px solid var(--color-primary); outline-offset: 2px; }
        .sidebar-footer { padding: 14px 16px; border-top: 1px solid var(--color-border); }
        .user-chip { display: flex; align-items: center; gap: 10px; margin: 0 -8px 12px; padding: 8px; border-radius: var(--radius); color: inherit; text-decoration: none; transition: background 0.15s ease; }
        .user-chip:hover, .user-chip.active { background: var(--color-surface-2); }
        .user-chip:focus-visible { outline: 2px solid var(--color-primary); outline-offset: 2px; }
        .user-chip .user-text { flex: 1; min-width: 0; }
        .user-chip-arrow { width: 16px; height: 16px; color: var(--color-text-muted); flex-shrink: 0; }
        .avatar {
            width: 38px; height: 38px; border-radius: 50%;
            display: grid; place-items: center;
            background: var(--color-primary-soft); color: var(--color-primary);
            font-weight: 700; flex-shrink: 0;
        }
        .sidebar-footer .user-name { font-weight: 600; font-size: 0.9rem; line-height: 1.2; }
        .sidebar-footer .user-role { color: var(--color-text-muted); font-size: 0.78rem; }
        .sidebar-footer form { margin: 0; }
        .sidebar-footer .btn { width: 100%; justify-content: center; }

        .main-content { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .topbar {
            background: color-mix(in srgb, var(--color-surface) 88%, transparent);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-bottom: 1px solid var(--color-border);
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 40;
        }
        .topbar-title { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .topbar h2 { font-size: 1.2rem; font-weight: 650; letter-spacing: -0.2px; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .topbar-date { display: inline-block; font-size: 0.85rem; color: var(--color-text-muted); }
        .topbar-date::first-letter { text-transform: uppercase; }
        .icon-btn {
            width: 38px; height: 38px;
            display: inline-grid; place-items: center;
            border: 1px solid var(--color-border);
            background: var(--color-surface);
            color: var(--color-text-muted);
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
        }
        .icon-btn:hover { background: var(--color-surface-2); color: var(--color-text); }
        .icon-btn:focus-visible { outline: 2px solid var(--color-primary); outline-offset: 2px; }
        .content { padding: 28px; flex: 1; width: 100%; max-width: 1240px; margin: 0 auto; }

        /* ===== Cartes ===== */
        .card {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 20px;
        }
        .card-header {
            padding: 16px 22px;
            border-bottom: 1px solid var(--color-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .card-header h3 { font-size: 1.02rem; font-weight: 650; }
        .card-body { padding: 22px; }

        /* ===== Tableaux ===== */
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 13px 16px; text-align: left; border-bottom: 1px solid var(--color-border); vertical-align: middle; }
        th { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--color-text-muted); font-weight: 600; background: var(--color-surface-2); white-space: nowrap; }
        thead th:first-child { border-top-left-radius: var(--radius-sm); }
        thead th:last-child { border-top-right-radius: var(--radius-sm); }
        tbody tr { transition: background 0.12s; }
        tbody tr:hover { background: var(--color-surface-2); }
        tbody tr:last-child td { border-bottom: none; }
        tr.row-overdue { background: var(--overdue-bg); }
        .line-through { text-decoration: line-through; color: var(--color-text-muted); }

        /* ===== Badges ===== */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 11px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }
        .badge-admin, .badge-violet { background: var(--violet-bg); color: var(--violet-fg); }
        .badge-charge, .badge-info, .badge-sonorisation { background: var(--info-bg); color: var(--info-fg); }
        .badge-consultant, .badge-active, .badge-success, .badge-photographe { background: var(--success-bg); color: var(--success-fg); }
        .badge-inactive, .badge-danger { background: var(--danger-bg); color: var(--danger-fg); }
        .badge-particulier, .badge-traiteur, .badge-warning { background: var(--warning-bg); color: var(--warning-fg); }
        .badge-entreprise, .badge-indigo { background: var(--indigo-bg); color: var(--indigo-fg); }
        .badge-décoration, .badge-pink { background: var(--pink-bg); color: var(--pink-fg); }
        .badge-autre, .badge-neutral { background: var(--neutral-bg); color: var(--neutral-fg); }
        .badge-solid { background: var(--btn-primary); color: #fff; }

        /* ===== Formulaires ===== */
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 500; font-size: 0.88rem; }
        .form-group label .required { color: var(--color-danger); }
        .form-control {
            width: 100%;
            padding: 10px 13px;
            border: 1px solid var(--color-border);
            border-radius: 10px;
            font-size: 0.95rem;
            font-family: inherit;
            background: var(--color-surface);
            color: var(--color-text);
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-control::placeholder { color: var(--color-text-light); }
        .form-control:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 4px color-mix(in srgb, var(--color-primary) 22%, transparent);
        }
        .form-control:focus-visible { outline: none; }
        select.form-control { cursor: pointer; }
        .form-error { color: var(--color-danger); font-size: 0.8rem; margin-top: 4px; }
        .form-help { color: var(--color-text-muted); font-size: 0.8rem; margin-top: 4px; }
        .form-actions { display: flex; gap: 10px; margin-top: 24px; flex-wrap: wrap; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 28px; }
        .detail-label { font-weight: 600; color: var(--color-text-muted); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .inline-form-grid { display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 12px; align-items: end; }
        .stat-box { text-align: center; padding: 16px; background: var(--color-surface-2); border: 1px solid var(--color-border); border-radius: var(--radius); }
        .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }

        /* ===== Boutons ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            border: 1px solid transparent;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 550;
            font-family: inherit;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.15s, border-color 0.15s, transform 0.05s, box-shadow 0.15s;
        }
        .btn:active { transform: translateY(1px); }
        .btn:focus-visible { outline: 2px solid var(--color-primary); outline-offset: 2px; }
        .btn-primary { background: var(--btn-primary); color: #fff; box-shadow: 0 1px 2px rgba(79,70,229,0.35); }
        .btn-primary:hover { background: var(--btn-primary-hover); }
        .btn-danger { background: #dc2626; color: #fff; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-secondary { background: var(--color-surface); color: var(--color-text); border-color: var(--color-border); }
        .btn-secondary:hover { background: var(--color-surface-2); border-color: var(--color-text-light); }
        .btn-sm { padding: 6px 12px; font-size: 0.8rem; border-radius: 8px; }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }

        /* ===== Alertes et notifications ===== */
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 0.9rem;
            border: 1px solid transparent;
        }
        .alert-success { background: var(--success-bg); color: var(--success-fg); border-color: color-mix(in srgb, var(--success-fg) 25%, transparent); }
        .alert-error { background: var(--danger-bg); color: var(--danger-fg); border-color: color-mix(in srgb, var(--danger-fg) 25%, transparent); }
        .alert-warning { background: var(--warning-bg); color: var(--warning-fg); border-color: color-mix(in srgb, var(--warning-fg) 25%, transparent); }
        .toast-stack { position: fixed; top: 74px; right: 20px; z-index: 70; display: flex; flex-direction: column; gap: 10px; width: min(380px, calc(100vw - 32px)); }
        .toast {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 13px 14px 13px 16px;
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            font-size: 0.9rem;
            border: 1px solid transparent;
            animation: toast-in 0.25s ease;
        }
        .toast-success { background: var(--success-bg); color: var(--success-fg); border-color: color-mix(in srgb, var(--success-fg) 30%, transparent); }
        .toast-error { background: var(--danger-bg); color: var(--danger-fg); border-color: color-mix(in srgb, var(--danger-fg) 30%, transparent); }
        .toast p { flex: 1; }
        .toast-close { background: none; border: none; color: inherit; cursor: pointer; opacity: 0.7; padding: 2px; display: grid; place-items: center; border-radius: 6px; }
        .toast-close:hover { opacity: 1; }
        .toast-close:focus-visible { outline: 2px solid currentColor; }
        .toast.leaving { opacity: 0; transform: translateX(16px); transition: opacity 0.25s, transform 0.25s; }
        @keyframes toast-in { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: none; } }

        /* ===== Pagination ===== */
        .pagination { margin-top: 18px; display: flex; gap: 6px; flex-wrap: wrap; }
        .pagination a, .pagination span {
            padding: 7px 13px;
            border: 1px solid var(--color-border);
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            color: var(--color-text);
            background: var(--color-surface);
        }
        .pagination a:hover { background: var(--color-surface-2); }
        .pagination .current { background: var(--btn-primary); color: #fff; border-color: var(--btn-primary); }

        /* ===== Recherche et filtres ===== */
        .filters {
            display: flex;
            gap: 12px;
            margin-bottom: 18px;
            flex-wrap: wrap;
            align-items: end;
            padding: 14px;
            background: var(--color-surface-2);
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
        }
        .filters .form-group { margin-bottom: 0; }
        .filters .search-input { flex: 1; min-width: 200px; }
        .actions { display: flex; gap: 6px; flex-wrap: wrap; }

        /* ===== États vides ===== */
        .empty-state { text-align: center; padding: 44px 20px; color: var(--color-text-muted); }
        .empty-state p { margin-bottom: 12px; }

        /* ===== Tableau de bord ===== */
        .hero {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #fff;
            border-radius: var(--radius);
            padding: 24px 26px;
            margin-bottom: 20px;
            display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;
            box-shadow: var(--shadow-md);
        }
        .hero h3 { font-size: 1.35rem; font-weight: 700; letter-spacing: -0.3px; }
        .hero p { opacity: 0.85; font-size: 0.92rem; margin-top: 2px; }
        .hero .badge { background: rgba(255,255,255,0.2); color: #fff; }
        .kpi {
            display: flex; align-items: center; gap: 14px;
            padding: 18px 20px;
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
        }
        .kpi-icon { width: 46px; height: 46px; border-radius: 12px; display: grid; place-items: center; flex-shrink: 0; }
        .kpi-icon .icon { width: 22px; height: 22px; }
        .kpi-icon.indigo { background: var(--indigo-bg); color: var(--indigo-fg); }
        .kpi-icon.green { background: var(--success-bg); color: var(--success-fg); }
        .kpi-icon.amber { background: var(--warning-bg); color: var(--warning-fg); }
        .kpi-icon.red { background: var(--danger-bg); color: var(--danger-fg); }
        .kpi-value { font-size: 1.8rem; font-weight: 700; line-height: 1.1; letter-spacing: -0.5px; }
        .kpi-label { font-size: 0.82rem; color: var(--color-text-muted); }
        .dash-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .dash-grid .card { margin-bottom: 0; }
        .bar-row { display: grid; grid-template-columns: 110px 1fr 34px; gap: 12px; align-items: center; margin-bottom: 12px; font-size: 0.88rem; }
        .bar-row:last-child { margin-bottom: 0; }
        .bar-track { height: 10px; border-radius: 999px; background: var(--color-surface-2); border: 1px solid var(--color-border); overflow: hidden; }
        .bar-fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, #6366f1, #8b5cf6); min-width: 4px; }
        .bar-fill.green { background: linear-gradient(90deg, #22c55e, #16a34a); }
        .bar-fill.amber { background: linear-gradient(90deg, #fbbf24, #f59e0b); }
        .bar-fill.slate { background: linear-gradient(90deg, #94a3b8, #64748b); }
        .bar-count { text-align: right; font-weight: 600; }
        .month-chart { display: flex; align-items: flex-end; gap: 12px; height: 150px; padding-top: 8px; }
        .month-col { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; gap: 6px; }
        .month-bar { width: 100%; max-width: 44px; border-radius: 8px 8px 0 0; background: linear-gradient(180deg, #818cf8, #4f46e5); min-height: 4px; }
        .month-bar.zero { background: var(--color-border); }
        .month-value { font-size: 0.8rem; font-weight: 600; }
        .month-name { font-size: 0.75rem; color: var(--color-text-muted); text-transform: capitalize; }
        .alert-item {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 12px 14px; margin-bottom: 10px;
            border-radius: 10px; border: 1px solid var(--color-border);
            border-left-width: 4px; background: var(--color-surface-2); font-size: 0.9rem;
        }
        .alert-item:last-child { margin-bottom: 0; }
        .alert-item.warning { border-left-color: var(--color-warning); }
        .alert-item.error { border-left-color: var(--color-danger); }

        /* ===== Page de connexion ===== */
        .auth-shell { min-height: 100vh; display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr); }
        .auth-aside {
            position: relative;
            overflow: hidden;
            color: #fff;
            padding: 56px 48px 64px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            gap: 34px;
            background:
                radial-gradient(900px 520px at 0% 0%, rgba(244,114,182,0.38), transparent 60%),
                radial-gradient(800px 600px at 100% 100%, rgba(56,189,248,0.30), transparent 60%),
                linear-gradient(145deg, #312e81 0%, #4f46e5 55%, #7c3aed 100%);
            background-size: 100% 100%, 100% 100%, 160% 160%;
            animation: auth-bg 14s ease-in-out infinite alternate;
        }
        @keyframes auth-bg { from { background-position: 0 0, 0 0, 0% 0%; } to { background-position: 0 0, 0 0, 100% 100%; } }

        /* Bulles lumineuses qui dérivent doucement */
        .auth-orb { position: absolute; border-radius: 50%; filter: blur(60px); opacity: 0.55; pointer-events: none; }
        .auth-orb.o1 { width: 340px; height: 340px; background: #f472b6; left: -90px; top: -60px; animation: orb-a 16s ease-in-out infinite alternate; }
        .auth-orb.o2 { width: 300px; height: 300px; background: #38bdf8; right: -80px; bottom: 10%; animation: orb-b 19s ease-in-out infinite alternate; }
        .auth-orb.o3 { width: 220px; height: 220px; background: #a78bfa; left: 35%; bottom: -80px; animation: orb-a 22s ease-in-out infinite alternate-reverse; }
        @keyframes orb-a { to { transform: translate(60px, 50px) scale(1.15); } }
        @keyframes orb-b { to { transform: translate(-70px, -40px) scale(1.2); } }

        .auth-stage { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: center; max-width: 520px; }

        /* Logo centré, animé */
        .auth-logo { position: relative; width: 112px; height: 112px; display: grid; place-items: center; margin-bottom: 14px; animation: logo-float 5s ease-in-out infinite; }
        .auth-logo-mark {
            position: relative; z-index: 2;
            width: 84px; height: 84px; border-radius: 26px;
            display: grid; place-items: center;
            background: linear-gradient(145deg, rgba(255,255,255,0.34), rgba(255,255,255,0.10));
            border: 1px solid rgba(255,255,255,0.5);
            box-shadow: 0 18px 44px rgba(30,27,75,0.45), inset 0 1px 0 rgba(255,255,255,0.55);
            backdrop-filter: blur(8px);
        }
        .auth-logo-mark .icon { width: 42px; height: 42px; stroke-width: 1.8; }
        .auth-logo .ring { position: absolute; inset: 14px; border-radius: 30px; border: 2px solid rgba(255,255,255,0.45); animation: ring-pulse 3.2s ease-out infinite; }
        .auth-logo .ring.r2 { animation-delay: 1.6s; }
        .auth-logo .spark { position: absolute; width: 8px; height: 8px; border-radius: 50%; background: #fff; box-shadow: 0 0 14px 3px rgba(255,255,255,0.8); animation: spark 3.6s ease-in-out infinite; }
        .auth-logo .spark.s1 { top: 6px; right: 12px; }
        .auth-logo .spark.s2 { bottom: 10px; left: 4px; width: 6px; height: 6px; animation-delay: 1.2s; }
        .auth-logo .spark.s3 { top: 40%; right: -6px; width: 5px; height: 5px; animation-delay: 2.1s; }
        @keyframes logo-float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-9px); } }
        @keyframes ring-pulse { 0% { transform: scale(0.85); opacity: 0.8; } 100% { transform: scale(1.55); opacity: 0; } }
        @keyframes spark { 0%, 100% { opacity: 0.2; transform: scale(0.6); } 50% { opacity: 1; transform: scale(1.2); } }

        .auth-wordmark { font-size: 2rem; font-weight: 800; letter-spacing: -0.8px; margin-bottom: 18px; animation: rise 0.7s 0.1s both; }
        .auth-stage h2 { font-size: 2.25rem; line-height: 1.15; letter-spacing: -0.8px; margin-bottom: 14px; animation: rise 0.7s 0.2s both; }
        .rotator { display: inline-block; color: #fde68a; transition: opacity 0.28s ease, transform 0.28s ease; }
        .rotator.out { opacity: 0; transform: translateY(8px); }
        .auth-lead { opacity: 0.9; font-size: 1.02rem; animation: rise 0.7s 0.3s both; }
        @keyframes rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }

        /* Points forts en tuiles */
        .auth-features { position: relative; z-index: 1; list-style: none; width: 100%; max-width: 560px; display: grid; grid-template-columns: 1fr 1fr; gap: 14px; text-align: left; }
        .auth-features li {
            display: flex; flex-direction: column; gap: 10px; padding: 16px;
            border-radius: 16px; background: rgba(255,255,255,0.10); border: 1px solid rgba(255,255,255,0.2);
            backdrop-filter: blur(8px);
            transition: transform 0.2s ease, background 0.2s ease, border-color 0.2s ease;
            animation: rise 0.7s both;
        }
        .auth-features li:nth-child(1) { animation-delay: 0.45s; }
        .auth-features li:nth-child(2) { animation-delay: 0.55s; }
        .auth-features li:nth-child(3) { animation-delay: 0.65s; }
        .auth-features li:nth-child(4) { animation-delay: 0.75s; }
        .auth-features li:hover { transform: translateY(-4px); background: rgba(255,255,255,0.17); border-color: rgba(255,255,255,0.4); }
        .auth-features .fi { width: 38px; height: 38px; border-radius: 11px; background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.28); display: grid; place-items: center; flex-shrink: 0; }
        .auth-features strong { display: block; font-weight: 650; line-height: 1.25; margin-bottom: 2px; }
        .auth-features span { opacity: 0.82; font-size: 0.86rem; line-height: 1.4; }

        /* Cartes d'exemple qui flottent (ordinateur uniquement) */
        .auth-floats { position: relative; z-index: 1; display: flex; gap: 14px; flex-wrap: wrap; justify-content: center; }
        .float-card {
            display: flex; align-items: center; gap: 12px; text-align: left;
            background: rgba(255,255,255,0.95); color: #0f172a;
            border-radius: 14px; padding: 12px 16px;
            box-shadow: 0 18px 40px rgba(15,23,42,0.28);
            font-size: 0.85rem; min-width: 220px;
            animation: bob 6s ease-in-out infinite;
        }
        .float-card:nth-child(2) { animation-delay: -3s; }
        @keyframes bob { 0%, 100% { transform: translateY(0) rotate(-1deg); } 50% { transform: translateY(-8px) rotate(1deg); } }
        .float-card .dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
        .float-card small { display: block; color: #64748b; }
        .auth-foot { position: absolute; left: 0; right: 0; bottom: 20px; z-index: 1; font-size: 0.8rem; opacity: 0.7; }

        .auth-main { display: flex; align-items: center; justify-content: center; padding: 32px 24px; background: var(--color-bg); }
        .auth-card { width: 100%; max-width: 410px; animation: rise 0.7s 0.15s both; }
        .auth-mobile-brand { display: none; align-items: center; gap: 12px; font-weight: 700; font-size: 1.3rem; margin-bottom: 28px; }
        .auth-card h1 { font-size: 1.85rem; letter-spacing: -0.5px; margin-bottom: 6px; }
        .auth-card .subtitle { color: var(--color-text-muted); margin-bottom: 26px; font-size: 0.95rem; }
        .auth-card .form-control { padding: 12px 14px; }
        .auth-card .btn-submit { width: 100%; padding: 13px 18px; font-size: 0.95rem; margin-top: 6px; }
        .auth-card .btn-submit { position: relative; overflow: hidden; }
        .auth-card .btn-submit::after { content: ''; position: absolute; top: 0; bottom: 0; left: -60%; width: 40%; background: linear-gradient(100deg, transparent, rgba(255,255,255,0.35), transparent); transform: skewX(-20deg); transition: left 0.6s ease; }
        .auth-card .btn-submit:hover::after { left: 120%; }
        .auth-card .btn.loading { opacity: 0.75; pointer-events: none; }
        .input-wrap { position: relative; }
        .input-wrap .form-control { padding-right: 48px; }
        .pw-toggle {
            position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
            width: 36px; height: 36px; display: grid; place-items: center;
            border: none; background: transparent; color: var(--color-text-muted);
            border-radius: 8px; cursor: pointer;
        }
        .pw-toggle:hover { background: var(--color-surface-2); color: var(--color-text); }
        .pw-toggle:focus-visible { outline: 2px solid var(--color-primary); outline-offset: 1px; }
        .caps-hint { display: none; margin-top: 6px; color: var(--color-warning); font-size: 0.8rem; font-weight: 500; }
        .caps-hint.show { display: block; }
        .form-group .remember, .remember { display: flex; align-items: center; gap: 8px; margin-bottom: 0; font-weight: 400; font-size: 0.88rem; cursor: pointer; }
        .remember input { width: 16px; height: 16px; accent-color: var(--btn-primary); }
        .demo-box { margin-top: 26px; padding: 16px; border: 1px dashed var(--color-border); border-radius: 14px; background: var(--color-surface); }
        .demo-box h2 { font-size: 0.85rem; font-weight: 650; margin-bottom: 2px; }
        .demo-box > p { font-size: 0.78rem; color: var(--color-text-muted); margin-bottom: 12px; }
        .demo-list { display: grid; gap: 8px; }
        .demo-chip {
            display: flex; align-items: center; justify-content: space-between; gap: 10px;
            width: 100%; text-align: left; padding: 9px 12px;
            border: 1px solid var(--color-border); border-radius: 10px;
            background: var(--color-surface-2); color: var(--color-text);
            font-family: inherit; font-size: 0.85rem; cursor: pointer;
            transition: border-color 0.15s, background 0.15s;
        }
        .demo-chip:hover { border-color: var(--color-primary); background: var(--color-primary-soft); }
        .demo-chip:focus-visible { outline: 2px solid var(--color-primary); outline-offset: 2px; }
        .demo-chip strong { display: block; font-weight: 600; }
        .demo-chip small { color: var(--color-text-muted); }
        .demo-chip code { font-size: 0.78rem; color: var(--color-text-muted); }

        /* ===== Menu mobile ===== */
        .menu-toggle {
            display: none;
            align-items: center;
            gap: 6px;
            background: var(--color-surface);
            color: var(--color-text);
            border: 1px solid var(--color-border);
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 0.95rem;
            font-family: inherit;
            cursor: pointer;
        }
        .menu-toggle:focus-visible { outline: 2px solid var(--color-primary); outline-offset: 2px; }
        .sidebar-backdrop { display: none; }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
        }

        /* ===== Tablette et téléphone ===== */
        @media (max-width: 1180px) {
            .auth-floats { display: none; }
        }
        /* Écran d'ordinateur peu haut : on garde l'essentiel visible sans défiler */
        @media (min-width: 901px) and (max-height: 960px) {
            .auth-floats { display: none; }
            .auth-aside { padding-top: 36px; padding-bottom: 56px; gap: 26px; }
        }
        @media (max-width: 1024px) {
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
            .topbar-date { display: none; }
        }
        @media (max-width: 900px) {
            .auth-shell { grid-template-columns: 1fr; }
            /* Le panneau de présentation reste visible : il passe au-dessus du formulaire */
            .auth-aside { padding: 30px 22px 34px; gap: 22px; justify-content: flex-start; }
            .auth-logo { width: 92px; height: 92px; margin-bottom: 8px; }
            .auth-logo-mark { width: 68px; height: 68px; border-radius: 21px; }
            .auth-logo-mark .icon { width: 34px; height: 34px; }
            .auth-logo .ring { inset: 11px; border-radius: 24px; }
            .auth-wordmark { font-size: 1.6rem; margin-bottom: 10px; }
            .auth-stage h2 { font-size: 1.65rem; margin-bottom: 10px; }
            .auth-lead { font-size: 0.95rem; }
            .auth-features { gap: 10px; }
            .auth-features li { padding: 12px; border-radius: 14px; gap: 8px; }
            .auth-features .fi { width: 34px; height: 34px; }
            .auth-features strong { font-size: 0.92rem; }
            .auth-features span { font-size: 0.8rem; }
            .auth-foot { display: none; }
            .auth-mobile-brand { display: none; }
            .auth-main { align-items: flex-start; }
        }
        @media (max-width: 768px) {
            .menu-toggle { display: inline-flex; }
            .sidebar {
                position: fixed;
                top: 0; bottom: 0; left: 0;
                height: auto;
                z-index: 90;
                overflow-y: auto;
                visibility: hidden;
                transform: translateX(-100%);
                transition: transform 0.2s ease, visibility 0.2s;
                box-shadow: var(--shadow-md);
            }
            .sidebar.open { visibility: visible; transform: translateX(0); }
            .sidebar-backdrop.open { display: block; position: fixed; inset: 0; z-index: 80; background: rgba(15,23,42,0.5); }
            .topbar { padding: 12px 16px; }
            .content { padding: 16px; }
            .card-body { padding: 16px; }
            .filters { flex-direction: column; align-items: stretch; }
            .filters .search-input { min-width: 0; }
            .detail-grid, .grid-2 { grid-template-columns: 1fr; }
            .inline-form-grid { grid-template-columns: 1fr 1fr; }
            .toast-stack { top: 64px; right: 16px; }
            .bar-row { grid-template-columns: 90px 1fr 30px; }
            .auth-main { padding: 28px 18px 40px; align-items: flex-start; }
            .auth-features li > div > span { display: none; }
            .auth-aside { padding: 26px 18px 28px; gap: 18px; }
            .auth-stage h2 { font-size: 1.45rem; }
            .auth-lead { display: none; }

            /* Les tableaux deviennent des cartes empilées */
            .table-wrapper { overflow-x: visible; }
            table.stack thead { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); }
            table.stack, table.stack tbody, table.stack tr, table.stack td { display: block; width: 100%; }
            table.stack tr { border: 1px solid var(--color-border); border-radius: 12px; margin-bottom: 12px; padding: 4px 14px; background: var(--color-surface); }
            table.stack tbody tr:hover { background: var(--color-surface); }
            table.stack td { display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 10px 0; border-bottom: 1px solid var(--color-border); text-align: right; }
            table.stack td:last-child { border-bottom: none; }
            table.stack td::before { content: attr(data-label); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-text-muted); font-weight: 600; text-align: left; flex-shrink: 0; }
            table.stack td[data-label=""]::before { display: none; }
            table.stack td .actions { justify-content: flex-end; }
        }
        @media (max-width: 480px) {
            .kpi-grid { grid-template-columns: 1fr 1fr; gap: 12px; }
            .kpi { padding: 14px; gap: 10px; }
            .kpi-icon { width: 38px; height: 38px; }
            .kpi-value { font-size: 1.45rem; }
            .inline-form-grid { grid-template-columns: 1fr; }
            .hero { padding: 18px; }
            .month-chart { gap: 6px; }
        }
    </style>
</head>
<body>
    <a href="#main-content" class="skip-link">Aller au contenu principal</a>

    @auth
    <div class="layout-wrapper">

        <aside class="sidebar" id="sidebar" role="navigation" aria-label="Navigation principale">
            <div class="sidebar-header">
                <div class="brand-mark"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></div>
                <div>
                    <h1>Eventia</h1>
                    <p>Gestion d'événements</p>
                </div>
            </div>
            <nav class="sidebar-nav">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}">
                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg> Tableau de bord
                </a>
                <div class="nav-label">Gestion</div>
                <a href="{{ route('events.index') }}" class="{{ request()->routeIs('events.*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('events.*') ? 'page' : 'false' }}">
                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg> Événements
                </a>
                <a href="{{ route('clients.index') }}" class="{{ request()->routeIs('clients.*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('clients.*') ? 'page' : 'false' }}">
                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> Clients
                </a>
                <a href="{{ route('providers.index') }}" class="{{ request()->routeIs('providers.*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('providers.*') ? 'page' : 'false' }}">
                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg> Prestataires
                </a>
                <a href="{{ route('venues.index') }}" class="{{ request()->routeIs('venues.*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('venues.*') ? 'page' : 'false' }}">
                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg> Lieux
                </a>
                @if(auth()->user()->isAdmin())
                <div class="nav-label">Administration</div>
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('users.*') ? 'page' : 'false' }}">
                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg> Utilisateurs
                </a>
                @endif
            </nav>
            <div class="sidebar-footer">
                <a href="{{ route('account.edit') }}" class="user-chip {{ request()->routeIs('account.*') ? 'active' : '' }}" title="Mon compte" aria-label="Mon compte : {{ auth()->user()->name }}" aria-current="{{ request()->routeIs('account.*') ? 'page' : 'false' }}">
                    <div class="avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="user-text">
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-role">{{ App\Models\User::ROLE_LABELS[auth()->user()->role] ?? auth()->user()->role }}</div>
                    </div>
                    <svg class="icon user-chip-arrow" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 18l6-6-6-6"/></svg>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/></svg> Se déconnecter</button>
                </form>
            </div>
        </aside>
        <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

        <!-- Contenu principal -->
        <div class="main-content">
            <header class="topbar">
                <div class="topbar-title">
                    <button type="button" class="menu-toggle" id="menu-toggle" aria-label="Ouvrir le menu" aria-controls="sidebar" aria-expanded="false">&#9776; Menu</button>
                    <h2>@yield('title', 'Eventia')</h2>
                </div>
                <div class="topbar-right">
                    <span class="topbar-date">{{ now()->locale('fr')->translatedFormat('l j F Y') }}</span>
                    <button type="button" class="icon-btn" id="theme-toggle" aria-label="Basculer entre le mode clair et le mode sombre" title="Mode clair / sombre"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></button>
                </div>
            </header>
            @if(session('success') || session('error'))
            <div class="toast-stack" aria-live="polite">
                @if(session('success'))
                <div class="toast toast-success" role="status" data-autoclose="6000">
                    <p>{{ session('success') }}</p>
                    <button type="button" class="toast-close" aria-label="Fermer le message"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
                </div>
                @endif
                @if(session('error'))
                <div class="toast toast-error" role="alert">
                    <p>{{ session('error') }}</p>
                    <button type="button" class="toast-close" aria-label="Fermer le message"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
                </div>
                @endif
            </div>
            @endif
            <main id="main-content" class="content" role="main">
                @yield('content')
            </main>
        </div>
    </div>
    @else
    <main id="main-content" role="main">
        @yield('content')
    </main>
    @endauth

    @auth
    <script>
        (function () {
            var toggle = document.getElementById('menu-toggle');
            var sidebar = document.getElementById('sidebar');
            var backdrop = document.getElementById('sidebar-backdrop');

            function setOpen(open) {
                sidebar.classList.toggle('open', open);
                backdrop.classList.toggle('open', open);
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                toggle.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
            }
            if (toggle && sidebar && backdrop) {
                toggle.addEventListener('click', function () { setOpen(!sidebar.classList.contains('open')); });
                backdrop.addEventListener('click', function () { setOpen(false); });
                document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { setOpen(false); } });
            }

            // Mode clair / sombre
            var themeBtn = document.getElementById('theme-toggle');
            if (themeBtn) {
                themeBtn.addEventListener('click', function () {
                    var root = document.documentElement;
                    var current = root.getAttribute('data-theme');
                    if (!current) {
                        current = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                    }
                    var next = current === 'dark' ? 'light' : 'dark';
                    root.setAttribute('data-theme', next);
                    try { localStorage.setItem('eventia-theme', next); } catch (e) { /* ignoré */ }
                });
            }

            // Notifications : fermeture manuelle ou automatique
            function closeToast(toast) {
                toast.classList.add('leaving');
                setTimeout(function () { if (toast.parentNode) { toast.parentNode.removeChild(toast); } }, 260);
            }
            document.querySelectorAll('.toast').forEach(function (toast) {
                var btn = toast.querySelector('.toast-close');
                if (btn) { btn.addEventListener('click', function () { closeToast(toast); }); }
                var delay = parseInt(toast.getAttribute('data-autoclose') || '0', 10);
                if (delay > 0) { setTimeout(function () { closeToast(toast); }, delay); }
            });

            // Tableaux : libellés pour l'affichage en cartes sur téléphone
            document.querySelectorAll('table').forEach(function (table) {
                var heads = table.querySelectorAll('thead th');
                if (!heads.length) { return; }
                table.classList.add('stack');
                var labels = Array.prototype.map.call(heads, function (th) { return th.textContent.trim(); });
                table.querySelectorAll('tbody tr').forEach(function (row) {
                    Array.prototype.forEach.call(row.children, function (cell, i) {
                        if (cell.tagName === 'TD' && !cell.hasAttribute('colspan')) {
                            cell.setAttribute('data-label', labels[i] || '');
                        }
                    });
                });
            });
        })();
    </script>
    @endauth
</body>
</html>
