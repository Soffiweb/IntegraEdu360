<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel administrativo') - IntegraEdu360</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Tokens Soffiweb — disponibles sin necesidad de Vite */
        :root {
            --navy-50:#eef1fb;--navy-100:#ccd4f1;--navy-200:#9aaae3;--navy-300:#6880d4;
            --navy-400:#3d5fc4;--navy-500:#2a4aaa;--navy-600:#1e3890;--navy-700:#152b74;
            --navy-800:#0e1f55;--navy-900:#070f2e;
            --rosa-50:#fdf0f4;--rosa-100:#f7d0de;--rosa-200:#edaac2;--rosa-300:#e07fa4;
            --rosa-400:#d05c88;--rosa-500:#c4527a;--rosa-600:#a03560;
            --steel-300:#8fb3d9;--steel-400:#6a97c5;
            --base-100:#fdfdff;--base-200:#f7f9fe;--base-300:#eef1f8;
            --base-content:#303237;--muted:#64748b;--border:#dde3f0;--sidebar-bg:#070f2e;
            --color-primary:#152b74;--color-primary-hover:#0e1f55;--color-primary-light:#eef1fb;
            --color-accent:#c4527a;--color-accent-hover:#a03560;
            --color-success-bg:#eaf3de;--color-success-text:#3b6d11;
            --color-warning-bg:#faeeda;--color-warning-text:#854f0b;
            --color-error-bg:#fcebeb;--color-error-text:#a32d2d;
            --color-info-bg:#eef1fb;--color-info-text:#152b74;
            --sp-xxs:2px;--sp-xs:4px;--sp-sm:8px;--sp-md:12px;--sp-base:16px;
            --sp-lg:24px;--sp-xl:32px;--sp-xxl:48px;--sp-section:64px;
            --r-xs:2px;--r-sm:4px;--r-md:6px;--r-lg:10px;--r-xl:14px;--r-2xl:20px;--r-full:9999px;
            --shadow-xs:0 1px 2px rgba(21,43,116,.06);
            --shadow-sm:0 1px 3px rgba(21,43,116,.08),0 1px 2px rgba(21,43,116,.06);
            --shadow-md:0 4px 6px rgba(21,43,116,.07),0 2px 4px rgba(21,43,116,.06);
            --shadow-lg:0 10px 15px rgba(21,43,116,.08),0 4px 6px rgba(21,43,116,.05);
            --shadow-focus:0 0 0 3px rgba(61,95,196,.18);
            --h-xs:24px;--h-sm:32px;--h-md:36px;--h-lg:48px;--h-topbar:60px;
            --w-sidebar:220px;--w-sidebar-collapsed:56px;
        }
    </style>
    <style>
        /* ============================================================
           Soffiweb Design System — Componentes de layout
           Los tokens CSS (:root) vienen de app.css (cargado via Vite)
        ============================================================ */

        *, *::before, *::after { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-size: 13px;
            line-height: 1.5;
            color: var(--base-content, #303237);
            background: var(--base-200, #f7f9fe);
        }

        /* ---- Layout principal ---- */
        .app-shell {
            display: grid;
            grid-template-columns: var(--w-sidebar, 220px) minmax(0, 1fr);
            min-height: 100vh;
        }

        /* ---- Sidebar ---- */
        .sidebar {
            background: var(--sidebar-bg, #070f2e);
            color: #fff;
            padding: var(--sp-lg, 24px) 0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.1) transparent;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: var(--sp-sm, 8px);
            padding: 0 var(--sp-lg, 24px) var(--sp-xl, 32px);
            border-bottom: 1px solid rgba(255,255,255,0.07);
            margin-bottom: var(--sp-xs, 4px);
        }

        .brand-mark {
            width: 32px;
            height: 32px;
            flex-shrink: 0;
            border-radius: var(--r-md, 6px);
            background: linear-gradient(135deg, var(--navy-400, #3d5fc4), var(--rosa-500, #c4527a));
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .brand strong {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: rgba(255,255,255,0.95);
        }

        .brand span {
            color: rgba(255,255,255,0.45);
            font-size: 11px;
        }

        /* ---- Perfil en sidebar ---- */
        .profile-card {
            padding: var(--sp-md, 12px) var(--sp-lg, 24px);
            margin-bottom: var(--sp-xs, 4px);
        }

        .profile-card h2 {
            margin: 0;
            font-size: 13px;
            font-weight: 600;
            color: rgba(255,255,255,0.9);
        }

        .profile-card p {
            margin: 2px 0 0;
            color: rgba(255,255,255,0.45);
            font-size: 11px;
        }

        /* ---- Navegación ---- */
        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 2px;
            padding: var(--sp-sm, 8px) 0 var(--sp-lg, 24px);
        }

        .nav-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 9px var(--sp-lg, 24px);
            border-left: 3px solid transparent;
            color: rgba(255,255,255,0.55);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            background: transparent;
            border-top: none;
            border-right: none;
            border-bottom: none;
            border-radius: 0;
            transition: background 150ms ease, color 150ms ease;
            gap: var(--sp-sm, 8px);
        }

        .nav-item:hover {
            background: rgba(255,255,255,0.05);
            color: rgba(255,255,255,0.85);
        }

        .nav-item.active {
            background: rgba(104,128,212,0.15);
            color: #fff;
            border-left-color: var(--navy-300, #6880d4);
        }

        .nav-item i {
            width: 16px;
            flex-shrink: 0;
            text-align: center;
            font-size: 13px;
        }

        .nav-item span {
            flex: 1;
        }

        .nav-item small {
            color: rgba(255,255,255,0.3);
            font-size: 11px;
            font-weight: 400;
        }

        /* Opciones destacadas del menu (gestion de personas) */
        .nav-item-highlight i {
            color: var(--rosa-300, #e07fa4);
        }

        .nav-item-highlight.active i {
            color: #fff;
        }

        .nav-item-form { margin: 0; }

        .nav-item-button {
            width: 100%;
            cursor: pointer;
            font: inherit;
            text-align: left;
            min-height: unset;
            height: auto;
        }

        /* ---- Panel lateral opcional ---- */
        .sidebar-panel {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: var(--r-xl, 14px);
            padding: var(--sp-lg, 24px);
            margin: 0 var(--sp-sm, 8px);
        }

        .sidebar-panel h3 { margin: 0; }

        .sidebar-panel p {
            color: rgba(255,255,255,0.45);
            font-size: 12px;
        }

        .sidebar-list {
            display: grid;
            gap: var(--sp-sm, 8px);
            margin-top: var(--sp-sm, 8px);
        }

        .sidebar-list div {
            padding: var(--sp-md, 12px);
            border-radius: var(--r-lg, 10px);
            background: rgba(255,255,255,0.06);
        }

        /* ---- Área principal ---- */
        .main {
            padding: var(--sp-xl, 32px);
            min-height: 100vh;
        }

        .page {
            width: 100%;
            max-width: none;
            margin: 0;
            padding: 0;
        }

        /* ---- Topbar ---- */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--sp-base, 16px);
            min-height: var(--h-topbar, 60px);
            padding: 0 var(--sp-xl, 32px);
            margin-bottom: var(--sp-xl, 32px);
            background: var(--base-100, #fdfdff);
            border: 1px solid var(--border, #dde3f0);
            border-radius: var(--r-xl, 14px);
            box-shadow: var(--shadow-xs, 0 1px 2px rgba(21,43,116,0.06));
        }

        .crumbs {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: var(--sp-sm, 8px);
            font-size: 13px;
            color: var(--muted, #64748b);
        }

        .crumbs a {
            color: var(--muted, #64748b);
            text-decoration: none;
            transition: color 150ms ease;
        }

        .crumbs a:hover { color: var(--color-primary, #152b74); }

        /* ---- Botones ---- */
        .btn,
        button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: var(--h-md, 36px);
            padding: 0 var(--sp-base, 16px);
            border-radius: var(--r-lg, 10px);
            border: 1px solid var(--border, #dde3f0);
            background: var(--base-100, #fdfdff);
            color: var(--base-content, #303237);
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background 150ms ease, transform 150ms ease, box-shadow 150ms ease;
            gap: var(--sp-xs, 4px);
            white-space: nowrap;
        }

        .btn:hover,
        button:hover {
            background: var(--base-300, #eef1f8);
        }

        .btn-primary {
            background: var(--color-primary, #152b74);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 2px 4px rgba(21,43,116,0.25);
        }

        .btn-primary:hover {
            background: var(--color-primary-hover, #0e1f55);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(21,43,116,0.3);
        }

        .btn-accent {
            background: var(--color-accent, #c4527a);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 2px 4px rgba(196,82,122,0.25);
        }

        .btn-accent:hover {
            background: var(--color-accent-hover, #a03560);
            transform: translateY(-1px);
        }

        .btn-danger {
            color: var(--color-error-text, #a32d2d);
            background: var(--color-error-bg, #fcebeb);
            border-color: #f2c9c9;
        }

        .btn-danger:hover {
            background: #f8d7d7;
        }

        .mini-btn {
            height: var(--h-sm, 32px);
            padding: 0 var(--sp-md, 12px);
            font-size: 12px;
        }

        /* ---- Cards y paneles ---- */
        .card,
        .panel,
        .modal-card {
            background: var(--base-100, #fdfdff);
            border: 1px solid var(--border, #dde3f0);
            box-shadow: var(--shadow-sm, 0 1px 3px rgba(21,43,116,0.08));
            border-radius: var(--r-xl, 14px);
            padding: var(--sp-xl, 32px);
        }

        .panel {
            border-radius: var(--r-2xl, 20px);
            padding: var(--sp-lg, 24px);
        }

        /* ---- Hero banner ---- */
        .hero {
            border-radius: var(--r-2xl, 20px);
            padding: var(--sp-xl, 32px);
            margin-bottom: var(--sp-lg, 24px);
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(280px, 0.9fr);
            gap: var(--sp-base, 16px);
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--navy-900, #070f2e), var(--navy-800, #0e1f55));
            color: #fff;
            box-shadow: var(--shadow-lg, 0 10px 15px rgba(21,43,116,0.08));
        }

        .hero > * { position: relative; z-index: 1; }

        .hero::after {
            content: "";
            position: absolute;
            right: -90px;
            top: -90px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(196,82,122,0.25), transparent 68%);
        }

        .hero h1 {
            margin: 0 0 10px;
            font-size: clamp(1.75rem, 3vw, 2.5rem);
            font-weight: 700;
            line-height: 1.1;
        }

        .hero p { color: rgba(255,255,255,0.75); line-height: 1.6; }

        .hero-side {
            padding: var(--sp-lg, 24px);
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: var(--r-xl, 14px);
            box-shadow: none;
        }

        .hero-side p,
        .hero-side strong { color: rgba(255,255,255,0.85); }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            padding: var(--sp-xs, 4px) var(--sp-md, 12px);
            border-radius: var(--r-full, 9999px);
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-size: 11px;
            font-weight: 600;
            margin-bottom: var(--sp-sm, 8px);
        }

        /* ---- Stats grid ---- */
        .stats {
            display: grid;
            grid-template-columns: repeat(var(--stats-columns, 4), minmax(0, 1fr));
            gap: var(--sp-base, 16px);
            margin-bottom: var(--sp-lg, 24px);
        }

        .stats .card {
            /* Sobreescribe padding shorthand del .card base con valores explícitos */
            padding-top: 20px;
            padding-right: 20px;
            padding-bottom: 20px;
            padding-left: 22px;
            min-height: 160px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .stats .card::before {
            content: "";
            position: absolute;
            top: 0; left: 0; bottom: 0;
            width: 4px;
            border-radius: 2px 0 0 2px;
            background: var(--color-accent, #c4527a);
        }

        .stats .card:nth-child(3n+2)::before { background: var(--color-primary, #152b74); }
        .stats .card:nth-child(3n)::before   { background: var(--steel-400, #6a97c5); }

        .metric {
            display: block;
            font-family: 'DM Mono', monospace;
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--navy-800, #0e1f55);
            line-height: 1.1;
            margin-bottom: 6px;
        }

        .stats .card > span {
            display: block;
            font-size: 13px;
            color: var(--muted, #64748b);
            line-height: 1.4;
        }

        /* ---- Sección / toolbar ---- */
        .section-title,
        .toolbar,
        .filters,
        .actions,
        .pager,
        .form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: var(--sp-md, 12px);
        }

        .section-title,
        .toolbar {
            align-items: center;
            justify-content: space-between;
        }

        .section-title { margin-bottom: var(--sp-base, 16px); }

        .section-title h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: var(--navy-800, #0e1f55);
        }

        .section-title span,
        .name-cell span,
        .helper-item p,
        .page-note,
        .info-block p {
            color: var(--muted, #64748b);
            font-size: 13px;
        }

        .actions-bar { display: flex; flex-wrap: wrap; gap: var(--sp-md, 12px); }

        .filters { flex: 1 1 720px; }
        .filters .input { flex: 1 1 280px; }
        .filters .select { flex: 0 1 220px; }

        /* ---- Inputs ---- */
        .input,
        .select,
        .textarea {
            width: 100%;
            border-radius: var(--r-lg, 10px);
            border: 1px solid var(--border, #dde3f0);
            background: #fff;
            color: var(--base-content, #303237);
            font: inherit;
            font-size: 13px;
            transition: border-color 150ms ease, box-shadow 150ms ease;
        }

        .input,
        .select {
            height: var(--h-md, 36px);
            padding: 0 var(--sp-md, 12px);
        }

        .textarea {
            min-height: 120px;
            padding: var(--sp-md, 12px);
            resize: vertical;
        }

        .input:hover,
        .select:hover { border-color: var(--navy-200, #9aaae3); }

        .input:focus,
        .select:focus,
        .textarea:focus {
            outline: none;
            border-color: var(--navy-400, #3d5fc4);
            box-shadow: var(--shadow-focus, 0 0 0 3px rgba(61,95,196,0.18));
        }

        /* ---- Alertas ---- */
        .alert {
            margin-bottom: var(--sp-base, 16px);
            padding: var(--sp-md, 12px) var(--sp-base, 16px);
            border-radius: var(--r-lg, 10px);
            background: var(--color-success-bg, #eaf3de);
            border: 1px solid #cce7d5;
            color: var(--color-success-text, #3b6d11);
            font-weight: 600;
            font-size: 13px;
        }

        .errors {
            margin-bottom: var(--sp-base, 16px);
            padding: var(--sp-md, 12px) var(--sp-base, 16px);
            border-radius: var(--r-lg, 10px);
            background: var(--color-error-bg, #fcebeb);
            border: 1px solid #f2c9c9;
            color: var(--color-error-text, #a32d2d);
            font-size: 13px;
        }

        .errors ul { margin: 0; padding-left: 18px; }

        /* ---- Tablas ---- */
        table { width: 100%; border-collapse: collapse; }

        thead { background: var(--navy-800, #0e1f55); }

        th {
            padding: 11px 14px;
            text-align: left;
            color: rgba(255,255,255,0.75);
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        td {
            padding: 11px 14px;
            text-align: left;
            vertical-align: middle;
            border-bottom: 1px solid var(--base-300, #eef1f8);
            font-size: 13px;
            color: var(--base-content, #303237);
        }

        tbody tr:hover td { background: var(--navy-50, #eef1fb); }

        /* ---- Badges ---- */
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 3px 8px;
            border-radius: var(--r-full, 9999px);
            font-size: 11px;
            font-weight: 600;
        }

        .badge-activo,
        .badge-active {
            background: var(--color-success-bg, #eaf3de);
            color: var(--color-success-text, #3b6d11);
        }

        .badge-observacion,
        .badge-planificado,
        .badge-review {
            background: var(--color-warning-bg, #faeeda);
            color: var(--color-warning-text, #854f0b);
        }

        .badge-inactivo,
        .badge-cerrado,
        .badge-inactive {
            background: var(--base-300, #eef1f8);
            color: var(--muted, #64748b);
        }

        /* ---- Estado vacío ---- */
        .empty {
            padding: var(--sp-xxl, 48px) var(--sp-xl, 32px);
            text-align: center;
            color: var(--muted, #64748b);
        }

        .pager { justify-content: flex-end; margin-top: var(--sp-base, 16px); }

        /* ---- Formularios ---- */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: var(--sp-lg, 24px);
        }

        .field-group { display: grid; gap: var(--sp-xs, 4px); }

        .field-group.full { grid-column: 1 / -1; }

        .field-group label {
            color: var(--navy-700, #152b74);
            font-size: 12px;
            font-weight: 600;
        }

        .field-group small,
        .field-note {
            color: var(--muted, #64748b);
            font-size: 12px;
        }

        /* ---- Bloques auxiliares ---- */
        .helper-list,
        .helper,
        .stack { display: grid; gap: var(--sp-md, 12px); }

        .helper-item,
        .info-block {
            padding: var(--sp-base, 16px);
            border-radius: var(--r-xl, 14px);
            background: var(--base-100, #fdfdff);
            border: 1px solid var(--border, #dde3f0);
        }

        .sidebar-list strong,
        .name-cell strong,
        .helper-item strong,
        .hero-side strong,
        .info-block strong {
            display: block;
            margin-bottom: 4px;
        }

        /* ---- Shell de gestión ---- */
        .management-shell,
        .content {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(300px, 0.85fr);
            gap: var(--sp-xl, 32px);
        }

        /* ---- Modales ---- */
        dialog.modal {
            width: min(960px, calc(100% - 24px));
            border: 0;
            border-radius: var(--r-2xl, 20px);
            padding: 0;
            box-shadow: var(--shadow-lg, 0 10px 15px rgba(21,43,116,0.08));
        }

        dialog.modal::backdrop {
            background: rgba(7,15,46,0.5);
            backdrop-filter: blur(2px);
        }

        .modal-card {
            padding: var(--sp-lg, 24px);
            background: var(--base-100, #fdfdff);
            border-radius: var(--r-2xl, 20px);
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--sp-md, 12px);
            margin-bottom: var(--sp-base, 16px);
        }

        .profile-card h2,
        .sidebar-panel h3,
        .section-title h2,
        .modal-header h3 { margin: 0; }

        /* ---- Responsive ---- */
        @media (max-width: 1180px) {
            .management-shell,
            .content,
            .hero,
            .stats {
                grid-template-columns: 1fr;
            }

            .toolbar {
                flex-direction: column;
                align-items: stretch;
            }
        }

        @media (max-width: 640px) {
            .app-shell {
                grid-template-columns: 1fr;
            }

            .sidebar {
                display: none;
            }

            .main {
                padding: var(--sp-base, 16px);
            }

            .topbar {
                height: auto;
                padding: var(--sp-md, 12px) var(--sp-base, 16px);
                flex-direction: column;
                align-items: flex-start;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            table, thead, tbody, th, td, tr { display: block; }

            thead { display: none; }

            tr {
                padding: var(--sp-sm, 8px) 0;
                border-bottom: 1px solid var(--base-300, #eef1f8);
            }

            td { padding: 6px 0; border-bottom: 0; }
        }
    </style>
    @if(file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @yield('head')
</head>
<body>
    @php
        $activeAdminNav = trim($__env->yieldContent('admin-nav', ''));
        $sidebarTitle = trim($__env->yieldContent('sidebar-title', 'Administrador'));
        $sidebarBody = trim($__env->yieldContent('sidebar-body', ''));
    @endphp

    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-mark">IE</div>
                <div>
                    <strong>IntegraEdu360</strong>
                    <span>Dashboard institucional</span>
                </div>
            </div>

            <div class="profile-card">
                <h2>{{ $sidebarTitle }}</h2>
                @if ($sidebarBody !== '')
                    <p>{{ $sidebarBody }}</p>
                @endif
            </div>

            <nav class="sidebar-nav">
                <a class="nav-item {{ $activeAdminNav === 'institucion' ? 'active' : '' }}" href="{{ route('admin.institucion.index') }}">
                    <i class="fa-solid fa-building"></i>
                    <span>Datos institucionales</span>
                    <small>Edicion</small>
                </a>
                <a class="nav-item {{ $activeAdminNav === 'periodos' ? 'active' : '' }}" href="{{ route('admin.periodos.index') }}">
                    <i class="fa-regular fa-calendar-days"></i>
                    <span>Periodos academicos</span>
                    <small>Gestion</small>
                </a>
                <a class="nav-item {{ $activeAdminNav === 'usuarios' ? 'active' : '' }}" href="{{ route('admin.usuarios.index') }}">
                    <i class="fa-solid fa-users-gear"></i>
                    <span>Administracion de usuarios</span>
                    <small>Gestion</small>
                </a>
                <a class="nav-item nav-item-highlight {{ $activeAdminNav === 'docentes' ? 'active' : '' }}" href="{{ route('admin.docentes.index') }}">
                    <i class="fa-solid fa-chalkboard-user"></i>
                    <span>Administracion de docentes</span>
                    <small>Gestion</small>
                </a>
                <a class="nav-item nav-item-highlight {{ $activeAdminNav === 'estudiantes' ? 'active' : '' }}" href="{{ route('admin.estudiantes.index') }}">
                    <i class="fa-solid fa-user-graduate"></i>
                    <span>Administracion de alumnos</span>
                    <small>Gestion</small>
                </a>
                <a class="nav-item {{ $activeAdminNav === 'especialidades' ? 'active' : '' }}" href="{{ route('admin.especialidades.index') }}">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>Administracion de especialidades</span>
                    <small>Gestion</small>
                </a>
                <a class="nav-item {{ $activeAdminNav === 'cursos' ? 'active' : '' }}" href="{{ route('admin.cursos.index') }}">
                    <i class="fa-solid fa-book"></i>
                    <span>Administracion de cursos</span>
                    <small>Gestion</small>
                </a>
                <a class="nav-item {{ $activeAdminNav === 'paralelos' ? 'active' : '' }}" href="{{ route('admin.paralelos.index') }}">
                    <i class="fa-solid fa-table-cells"></i>
                    <span>Administracion de paralelos</span>
                    <small>Gestion</small>
                </a>
                <a class="nav-item {{ $activeAdminNav === 'asignaturas' ? 'active' : '' }}" href="{{ route('admin.asignaturas.index') }}">
                    <i class="fa-solid fa-book-open"></i>
                    <span>Administracion de asignaturas</span>
                    <small>Gestion</small>
                </a>
                <form class="nav-item-form" method="POST" action="{{ route('auth.logout') }}">
                    @csrf
                    <button class="nav-item nav-item-button" type="submit">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Cerrar sesion</span>
                        <small>Salir</small>
                    </button>
                </form>
            </nav>

        </aside>

        <main class="main">
            <div class="page">
                <div class="topbar">
                    <div class="crumbs">
                        @yield('crumbs')
                    </div>
                    @hasSection('topbar-action')
                        @yield('topbar-action')
                    @else
                        <a class="btn" href="{{ route('roles.dashboard', 'admin') }}">Volver al dashboard</a>
                    @endif
                </div>

                @yield('content')
            </div>
        </main>
    </div>

    @yield('dialogs')
    @yield('scripts')
</body>
</html>
