<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <title>Dashboard {{ $role['name'] }} — IntegraEdu360</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    @if(file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @if (in_array($dashboardSection ?? null, ['zonas', 'distritos', 'roles', 'usuarios', 'resumen'], true))
        <link href="{{ asset('css/zonas.css') }}" rel="stylesheet">
    @endif
    @if (in_array($dashboardSection ?? null, ['instituciones', 'zonas', 'distritos', 'roles', 'usuarios', 'resumen'], true))
        <link href="{{ asset('css/instituciones.css') }}" rel="stylesheet">
    @endif
    <style>
        /* ============================================================
           Dashboard — estilos específicos
           Tokens de color/espaciado vienen de app.css (cargado via Vite)
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

        /* ---- Estructura principal ---- */
        .app-shell {
            display: grid;
            grid-template-columns: var(--w-sidebar, 220px) minmax(0, 1fr);
            min-height: 100vh;
        }

        /* ================================================================
           SIDEBAR
        ================================================================ */
        .sidebar {
            background: var(--sidebar-bg, #070f2e);
            color: #fff;
            padding: var(--sp-lg, 24px) 0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.08) transparent;
            display: flex;
            flex-direction: column;
        }

        /* Marca / logo */
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

        .brand-text strong {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: rgba(255,255,255,0.95);
        }

        .brand-text span {
            font-size: 11px;
            color: rgba(255,255,255,0.4);
        }

        /* Perfil del rol en sidebar */
        .sidebar-profile {
            padding: var(--sp-md, 12px) var(--sp-lg, 24px);
            margin-bottom: var(--sp-xs, 4px);
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: var(--r-md, 6px);
            background: linear-gradient(135deg, var(--navy-600, #1e3890), var(--rosa-600, #a03560));
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: var(--sp-sm, 8px);
        }

        .sidebar-profile h2 {
            margin: 0 0 3px;
            font-size: 13px;
            font-weight: 700;
            color: rgba(255,255,255,0.9);
        }

        .sidebar-profile p {
            margin: 0;
            font-size: 11px;
            color: rgba(255,255,255,0.4);
        }

        /* Grupo de nav con etiqueta */
        .nav-group {
            padding: var(--sp-lg, 24px) 0 var(--sp-sm, 8px);
        }

        .nav-group-label {
            padding: 0 var(--sp-lg, 24px) var(--sp-xs, 4px);
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255,255,255,0.28);
        }

        nav.sidebar-nav {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1px;
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
            cursor: pointer;
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

        .nav-item small {
            font-size: 10px;
            font-weight: 400;
            color: rgba(255,255,255,0.25);
        }

        .nav-item-form { margin: 0; }

        .nav-item-button {
            width: 100%;
            font: inherit;
            text-align: left;
            min-height: unset;
            height: auto;
        }

        /* Panel de alertas en sidebar (solo non-admin) */
        .sidebar-alerts {
            margin: var(--sp-sm, 8px) var(--sp-sm, 8px) 0;
            border-radius: var(--r-xl, 14px);
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-alerts-header {
            padding: var(--sp-md, 12px) var(--sp-base, 16px);
            background: rgba(255,255,255,0.06);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: rgba(255,255,255,0.5);
        }

        .sidebar-alert-item {
            padding: var(--sp-md, 12px) var(--sp-base, 16px);
            border-top: 1px solid rgba(255,255,255,0.07);
        }

        .sidebar-alert-item strong {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: rgba(255,255,255,0.85);
            margin-bottom: 2px;
        }

        .sidebar-alert-item p {
            margin: 0;
            font-size: 11px;
            color: rgba(255,255,255,0.4);
            line-height: 1.5;
        }

        /* ================================================================
           ÁREA PRINCIPAL
        ================================================================ */
        .main {
            padding: var(--sp-xl, 32px);
            min-height: 100vh;
        }

        /* ---- Cabecera de página ---- */
        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: var(--sp-xl, 32px);
            margin-bottom: var(--sp-xl, 32px);
        }

        .page-header-copy h1 {
            margin: 0 0 6px;
            font-size: 22px;
            font-weight: 700;
            line-height: 1.2;
            color: var(--navy-800, #0e1f55);
            letter-spacing: -0.3px;
        }

        .page-header-copy h1.institution-title {
            font-size: 20px;
            line-height: 1.25;
            max-width: 620px;
        }

        .page-header-copy p {
            margin: 0;
            font-size: 13px;
            color: var(--muted, #64748b);
            line-height: 1.6;
            max-width: 580px;
        }

        .page-header-actions {
            display: flex;
            flex-wrap: wrap;
            gap: var(--sp-sm, 8px);
            flex-shrink: 0;
        }

        /* Botones del dashboard */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: var(--h-md, 36px);
            padding: 0 var(--sp-base, 16px);
            border-radius: var(--r-lg, 10px);
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background 150ms ease, transform 150ms ease, box-shadow 150ms ease;
            white-space: nowrap;
            border: 1px solid var(--border, #dde3f0);
            gap: var(--sp-xs, 4px);
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

        .btn-secondary {
            background: var(--base-100, #fdfdff);
            color: var(--base-content, #303237);
        }

        .btn-secondary:hover {
            background: var(--base-300, #eef1f8);
        }

        /* ---- Hero del rol (solo non-admin) ---- */
        .role-hero {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(260px, 0.8fr);
            gap: var(--sp-base, 16px);
            margin-bottom: var(--sp-xl, 32px);
            background: linear-gradient(135deg, var(--navy-900, #070f2e), var(--navy-800, #0e1f55));
            border-radius: var(--r-2xl, 20px);
            padding: var(--sp-xl, 32px);
            color: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-lg, 0 10px 15px rgba(21,43,116,0.08));
        }

        .role-hero::after {
            content: "";
            position: absolute;
            right: -80px;
            top: -80px;
            width: 240px;
            height: 240px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(196,82,122,0.22), transparent 70%);
            pointer-events: none;
        }

        .role-hero > * { position: relative; z-index: 1; }

        .role-hero-info .eyebrow {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: var(--r-full, 9999px);
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: var(--sp-sm, 8px);
        }

        .role-hero-info h2 {
            margin: 0 0 var(--sp-sm, 8px);
            font-size: 20px;
            font-weight: 700;
            line-height: 1.25;
        }

        .role-hero-info p {
            margin: 0;
            font-size: 13px;
            color: rgba(255,255,255,0.65);
            line-height: 1.6;
        }

        .focus-card {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: var(--r-xl, 14px);
            padding: var(--sp-lg, 24px);
            align-self: start;
        }

        .focus-card .focus-label {
            display: inline-flex;
            padding: 2px 8px;
            border-radius: var(--r-full, 9999px);
            background: rgba(255,255,255,0.12);
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: var(--sp-sm, 8px);
        }

        .focus-card p {
            margin: 0;
            font-size: 13px;
            color: rgba(255,255,255,0.7);
            line-height: 1.6;
        }

        /* ---- KPI row ---- */
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: var(--sp-base, 16px);
            margin-bottom: var(--sp-xl, 32px);
        }

        .kpi-card {
            background: var(--base-100, #fdfdff);
            border: 1px solid var(--border, #dde3f0);
            border-radius: var(--r-xl, 14px);
            box-shadow: var(--shadow-sm, 0 1px 3px rgba(21,43,116,0.08));
            padding: var(--sp-lg, 24px) var(--sp-lg, 24px) var(--sp-lg, 24px) calc(var(--sp-lg, 24px) + 4px);
            position: relative;
            overflow: hidden;
        }

        .kpi-card::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: var(--color-accent, #c4527a);
            border-radius: var(--r-xs, 2px) 0 0 var(--r-xs, 2px);
        }

        .kpi-card:nth-child(3n+1)::before { background: var(--color-accent, #c4527a); }
        .kpi-card:nth-child(3n+2)::before { background: var(--color-primary, #152b74); }
        .kpi-card:nth-child(3n)::before   { background: var(--steel-400, #6a97c5); }

        .kpi-value {
            display: block;
            font-family: 'DM Mono', monospace;
            font-size: 28px;
            font-weight: 700;
            color: var(--navy-800, #0e1f55);
            line-height: 1.2;
            margin-bottom: var(--sp-xs, 4px);
        }

        .kpi-label {
            display: block;
            font-size: 13px;
            color: var(--muted, #64748b);
            line-height: 1.4;
        }

        .kpi-trend {
            display: inline-flex;
            align-items: center;
            margin-top: var(--sp-md, 12px);
            padding: 3px 8px;
            border-radius: var(--r-full, 9999px);
            background: var(--color-info-bg, #eef1fb);
            color: var(--color-info-text, #152b74);
            font-size: 11px;
            font-weight: 600;
        }

        /* ---- Grid de contenido ---- */
        .dashboard-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(300px, 0.75fr);
            gap: var(--sp-base, 16px);
        }

        .dashboard-col {
            display: flex;
            flex-direction: column;
            gap: var(--sp-base, 16px);
        }

        /* ---- Tarjeta genérica ---- */
        .card {
            background: var(--base-100, #fdfdff);
            border: 1px solid var(--border, #dde3f0);
            border-radius: var(--r-xl, 14px);
            box-shadow: var(--shadow-sm, 0 1px 3px rgba(21,43,116,0.08));
            overflow: hidden;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--sp-md, 12px);
            padding: var(--sp-base, 16px) var(--sp-lg, 24px);
            border-bottom: 1px solid var(--base-300, #eef1f8);
        }

        .card-header h3 {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            color: var(--navy-800, #0e1f55);
        }

        .card-header-meta {
            font-size: 11px;
            font-weight: 500;
            color: var(--muted, #64748b);
        }

        .card-body {
            padding: var(--sp-base, 16px) var(--sp-lg, 24px);
        }

        /* ---- Áreas de enfoque (highlights) ---- */
        .highlight-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: var(--sp-sm, 8px);
        }

        .highlight {
            padding: var(--sp-md, 12px);
            border-radius: var(--r-lg, 10px);
            background: var(--base-200, #f7f9fe);
            border: 1px solid var(--border, #dde3f0);
        }

        .highlight strong {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--navy-700, #152b74);
            margin-bottom: 4px;
        }

        .highlight span {
            font-size: 12px;
            color: var(--muted, #64748b);
            line-height: 1.5;
        }

        /* ---- Agenda del día (timeline) ---- */
        .timeline-list {
            display: flex;
            flex-direction: column;
        }

        .timeline-item {
            padding: var(--sp-md, 12px) 0;
            border-bottom: 1px solid var(--base-300, #eef1f8);
        }

        .timeline-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .timeline-time {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: var(--r-full, 9999px);
            background: var(--color-info-bg, #eef1fb);
            color: var(--color-info-text, #152b74);
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.02em;
            margin-bottom: 6px;
        }

        .timeline-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--base-content, #303237);
            margin-bottom: 2px;
        }

        .timeline-detail {
            font-size: 12px;
            color: var(--muted, #64748b);
            line-height: 1.5;
        }

        /* ---- Acciones rápidas ---- */
        .action-list {
            display: flex;
            flex-direction: column;
        }

        .action-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--sp-md, 12px);
            padding: var(--sp-md, 12px) 0;
            border-bottom: 1px solid var(--base-300, #eef1f8);
        }

        .action-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .action-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--base-content, #303237);
            margin-bottom: 2px;
        }

        .action-description {
            display: block;
            font-size: 11px;
            color: var(--muted, #64748b);
        }

        .action-link,
        .action-link-button {
            display: inline-flex;
            align-items: center;
            height: var(--h-sm, 32px);
            padding: 0 var(--sp-md, 12px);
            border-radius: var(--r-lg, 10px);
            border: 1px solid var(--border, #dde3f0);
            background: var(--base-100, #fdfdff);
            color: var(--color-primary, #152b74);
            font-family: inherit;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            white-space: nowrap;
            transition: background 150ms ease, color 150ms ease;
            flex-shrink: 0;
        }

        .action-link:hover,
        .action-link-button:hover {
            background: var(--color-primary-light, #eef1fb);
            border-color: var(--navy-200, #9aaae3);
        }

        .action-link.disabled {
            color: var(--muted, #64748b);
            cursor: default;
            pointer-events: none;
        }

        .action-link-form { margin: 0; }

        /* ---- Notificaciones / alertas ---- */
        .alert-list {
            display: flex;
            flex-direction: column;
            gap: var(--sp-sm, 8px);
        }

        .alert-item {
            padding: var(--sp-md, 12px) var(--sp-base, 16px);
            border-radius: var(--r-lg, 10px);
            background: var(--color-info-bg, #eef1fb);
            border-left: 3px solid var(--color-primary, #152b74);
        }

        .alert-item strong {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--color-info-text, #152b74);
            margin-bottom: 3px;
        }

        .alert-item span {
            display: block;
            font-size: 12px;
            color: var(--muted, #64748b);
            line-height: 1.5;
        }

        /* ================================================================
           RESPONSIVE
        ================================================================ */
        @media (max-width: 1180px) {
            .dashboard-grid { grid-template-columns: 1fr; }
            .role-hero       { grid-template-columns: 1fr; }
            .kpi-row         { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 1024px) {
            .app-shell { grid-template-columns: var(--w-sidebar-collapsed, 56px) minmax(0, 1fr); }
            .brand-text, .sidebar-profile p,
            .nav-group-label, .nav-item small,
            .sidebar-alerts, .sidebar-profile h2 { display: none; }
            .brand { justify-content: center; padding-left: 0; padding-right: 0; }
            .brand-mark { margin: 0 auto; }
            .nav-item { justify-content: center; padding-left: 0; padding-right: 0; }
            .nav-item span { display: none; }
            .sidebar-profile { display: none; }
        }

        @media (max-width: 640px) {
            .app-shell { grid-template-columns: 1fr; }
            .sidebar    { display: none; }
            .main       { padding: var(--sp-base, 16px); }
            .kpi-row    { grid-template-columns: 1fr; }
            .page-header {
                flex-direction: column;
                gap: var(--sp-base, 16px);
            }
            .page-header-actions { width: 100%; }
        }
    </style>
</head>
<body>
    @php
        $roleModules = collect(integraEduModules()[$roleKey] ?? [])->keys()->all();
    @endphp

    <div class="app-shell">

        {{-- ===================== SIDEBAR ===================== --}}
        <aside class="sidebar">

            {{-- Marca --}}
            <div class="brand">
                <div class="brand-mark">IE</div>
                <div class="brand-text">
                    <strong>IntegraEdu360</strong>
                    <span>Plataforma educativa</span>
                </div>
            </div>

            {{-- Perfil del rol --}}
            <div class="sidebar-profile">
                @if ($roleKey !== 'admin')
                    <div class="role-badge">{{ $role['short'] }}</div>
                @endif
                <h2>{{ $role['name'] }}</h2>
                @if ($roleKey !== 'admin')
                    <p>{{ $role['category'] ?? 'Rol institucional' }}</p>
                @else
                    <p>Panel de administración</p>
                @endif
            </div>

            {{-- Navegación --}}
            <nav class="sidebar-nav">
                @if ($roleKey === 'admin')
                    <div class="nav-group">
                        <div class="nav-group-label">Gestión</div>
                        <a class="nav-item {{ request()->routeIs('admin.institucion.*') ? 'active' : '' }}"
                           href="{{ route('admin.institucion.index') }}">
                            <span>Datos institucionales</span>
                            <small>Editar</small>
                        </a>
                        <a class="nav-item {{ request()->routeIs('admin.periodos.*') ? 'active' : '' }}"
                           href="{{ route('admin.periodos.index') }}">
                            <span>Periodos académicos</span>
                            <small>Gestión</small>
                        </a>
                        <a class="nav-item {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}"
                           href="{{ route('admin.usuarios.index') }}">
                            <span>Usuarios</span>
                            <small>Gestión</small>
                        </a>
                        <a class="nav-item {{ request()->routeIs('admin.docentes.*') ? 'active' : '' }}"
                           href="{{ route('admin.docentes.index') }}">
                            <span>Docentes</span>
                            <small>Gestión</small>
                        </a>
                    </div>

                    <div class="nav-group">
                        <div class="nav-group-label">Módulos</div>
                        @foreach ([
                            'estudiantes' => 'Alumnos',
                            'cursos'      => 'Cursos',
                            'paralelos'   => 'Paralelos',
                            'especialidades' => 'Especialidades',
                            'asignaturas' => 'Asignaturas',
                        ] as $slug => $label)
                            <a class="nav-item {{ request()->routeIs('roles.module') && request()->route('module') === $slug ? 'active' : '' }}"
                               href="{{ route('roles.module', ['role' => $roleKey, 'module' => $slug]) }}">
                                <span>{{ $label }}</span>
                                <small>Módulo</small>
                            </a>
                        @endforeach
                    </div>

                    <div class="nav-group">
                        <form class="nav-item-form" method="POST" action="{{ route('auth.logout') }}">
                            @csrf
                            <button class="nav-item nav-item-button" type="submit">
                                <span>Cerrar sesión</span>
                                <small>Salir</small>
                            </button>
                        </form>
                    </div>

                @else
                    <div class="nav-group">
                        <div class="nav-group-label">Navegación</div>
                        <a class="nav-item {{ request()->routeIs('roles.dashboard') ? 'active' : '' }}"
                           href="{{ route('roles.dashboard', $roleKey) }}">
                            <span>Resumen ejecutivo</span>
                            <small>Hoy</small>
                        </a>
                        @if ($roleKey === 'admin')
                            <a class="nav-item {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}"
                               href="{{ route('admin.usuarios.index') }}">
                                <span>Gestión de usuarios</span>
                                <small>Sistema</small>
                            </a>
                        @endif
                        @if ($roleKey === 'superusuario')
                            <a class="nav-item {{ request()->routeIs('superusuario.zonas.*') ? 'active' : '' }}" href="{{ route('superusuario.zonas.index') }}" @if(request()->routeIs('superusuario.zonas.*')) aria-current="page" @endif>
                                <span>Zonas educativas</span>
                                <small>Catálogo</small>
                            </a>
                            <a class="nav-item {{ request()->routeIs('superusuario.distritos.*') ? 'active' : '' }}"
                               href="{{ route('superusuario.distritos.index') }}" @if(request()->routeIs('superusuario.distritos.*')) aria-current="page" @endif>
                                <span>Distritos Educativos</span>
                                <small>Catálogo</small>
                            </a>
                            <a class="nav-item {{ request()->routeIs('superusuario.instituciones.*') ? 'active' : '' }}"
                               href="{{ route('superusuario.instituciones.index') }}" @if(request()->routeIs('superusuario.instituciones.*')) aria-current="page" @endif>
                                <span>Instituciones</span>
                                <small>CRUD</small>
                            </a>
                            <a class="nav-item {{ request()->routeIs('superusuario.roles.*') ? 'active' : '' }}"
                               href="{{ route('superusuario.roles.index') }}" @if(request()->routeIs('superusuario.roles.*')) aria-current="page" @endif>
                                <span>Roles de Usuario</span>
                                <small>Catálogo</small>
                            </a>
                            <a class="nav-item {{ request()->routeIs('superusuario.usuarios.*') ? 'active' : '' }}"
                               href="{{ route('superusuario.usuarios.index') }}" @if(request()->routeIs('superusuario.usuarios.*')) aria-current="page" @endif>
                                <span>Gestión de Usuarios</span>
                                <small>Administradores</small>
                            </a>
                        @endif
                        <a class="nav-item" href="{{ route('auth.form') }}">
                            <span>Cambiar acceso</span>
                            <small>Menú</small>
                        </a>
                    </div>
                @endif
            </nav>

            {{-- Panel de alertas en sidebar (solo non-admin) --}}
            @if (! in_array($roleKey, ['admin', 'superusuario'], true))
                <div class="sidebar-alerts">
                    <div class="sidebar-alerts-header">Alertas clave</div>
                    @foreach ($dashboard['alerts'] as $alert)
                        <div class="sidebar-alert-item">
                            <strong>{{ $alert['title'] }}</strong>
                            <p>{{ $alert['detail'] }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

        </aside>

        {{-- ===================== MAIN ===================== --}}
        <main class="main">

            @if (($dashboardSection ?? null) === 'resumen')
                @include('superusuario.resumen.index')
            @elseif (($dashboardSection ?? null) === 'usuarios')
                @include('superusuario.usuarios.index')
            @elseif (($dashboardSection ?? null) === 'roles')
                @include('superusuario.roles.index')
            @elseif (($dashboardSection ?? null) === 'zonas')
                @include('superusuario.zonas.index')
            @elseif (($dashboardSection ?? null) === 'distritos')
                @include('superusuario.distritos.index')
            @elseif (($dashboardSection ?? null) === 'instituciones')
                @include('superusuario.instituciones.index')
            @else
            {{-- Cabecera de página --}}
            <header class="page-header">
                <div class="page-header-copy">
                    <h1 class="{{ $roleKey === 'admin' ? 'institution-title' : '' }}">
                        {{ $dashboard['greeting'] }}
                    </h1>
                    @if (! empty($dashboard['headline']))
                        <p>{{ $dashboard['headline'] }}</p>
                    @endif
                </div>

                <div class="page-header-actions">
                    <a class="btn btn-primary" href="{{ route('roles.access', $roleKey) }}">Ver perfil</a>
                    <a class="btn btn-secondary" href="{{ route('auth.form') }}">Menú principal</a>
                </div>
            </header>

            {{-- Hero del rol (solo non-admin) --}}
            @if ($roleKey !== 'admin')
                <section class="role-hero">
                    <div class="role-hero-info">
                        <div class="eyebrow">{{ $role['short'] }} — {{ $role['category'] ?? 'Rol institucional' }}</div>
                        <h2>{{ $role['name'] }}</h2>
                        <p>{{ $role['description'] }}</p>
                    </div>
                    <div class="focus-card">
                        <div class="focus-label">{{ $dashboard['focus']['title'] }}</div>
                        <p>{{ $dashboard['focus']['body'] }}</p>
                    </div>
                </section>
            @endif

            {{-- KPIs --}}
            <section class="kpi-row">
                @foreach ($dashboard['kpis'] as $kpi)
                    <article class="kpi-card">
                        <span class="kpi-value">{{ $kpi['value'] }}</span>
                        <span class="kpi-label">{{ $kpi['label'] }}</span>
                        <span class="kpi-trend">{{ $kpi['trend'] }}</span>
                    </article>
                @endforeach
            </section>

            {{-- Contenido principal en 2 columnas --}}
            <section class="dashboard-grid">

                {{-- Columna izquierda --}}
                <div class="dashboard-col">

                    {{-- Áreas de enfoque (solo non-admin) --}}
                    @if ($roleKey !== 'admin')
                        <article class="card">
                            <div class="card-header">
                                <h3>Áreas de enfoque</h3>
                                <span class="card-header-meta">Visión priorizada</span>
                            </div>
                            <div class="card-body">
                                <div class="highlight-grid">
                                    @foreach ($dashboard['highlights'] as $highlight)
                                        <div class="highlight">
                                            <strong>{{ $highlight['title'] }}</strong>
                                            <span>{{ $highlight['body'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </article>
                    @endif

                    {{-- Agenda del día --}}
                    <article class="card">
                        <div class="card-header">
                            <h3>Agenda del día</h3>
                            <span class="card-header-meta">Seguimiento operativo</span>
                        </div>
                        <div class="card-body">
                            <div class="timeline-list">
                                @foreach ($dashboard['timeline'] as $item)
                                    <div class="timeline-item">
                                        <div class="timeline-time">{{ $item['time'] }}</div>
                                        <div class="timeline-title">{{ $item['title'] }}</div>
                                        <div class="timeline-detail">{{ $item['detail'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </article>

                </div>

                {{-- Columna derecha --}}
                <div class="dashboard-col">

                    {{-- Acciones rápidas (todos los roles) --}}
                    <article class="card">
                        <div class="card-header">
                            <h3>Acciones rápidas</h3>
                            <span class="card-header-meta">Inicio sugerido</span>
                        </div>
                        <div class="card-body">
                            <div class="action-list">
                                @foreach ($dashboard['actions'] as $action)
                                    <div class="action-item">
                                        <div class="action-info">
                                            <span class="action-label">{{ $action['label'] }}</span>
                                            <span class="action-description">{{ $action['description'] }}</span>
                                        </div>
                                        @if ($roleKey === 'superusuario' && ($action['slug'] ?? null) === 'instituciones')
                                            <a class="action-link" href="{{ route('superusuario.instituciones.index') }}">Abrir</a>
                                        @elseif ($roleKey === 'superusuario' && ($action['slug'] ?? null) === 'zonas')
                                            <a class="action-link" href="{{ route('superusuario.zonas.index') }}">Abrir</a>
                                        @elseif ($roleKey === 'admin' && ($action['slug'] ?? null) === 'datos-institucionales')
                                            <a class="action-link" href="{{ route('admin.institucion.index') }}">Abrir</a>
                                        @elseif ($roleKey === 'admin' && ($action['slug'] ?? null) === 'periodos')
                                            <a class="action-link" href="{{ route('admin.periodos.index') }}">Abrir</a>
                                        @elseif ($roleKey === 'admin' && ($action['slug'] ?? null) === 'docentes')
                                            <a class="action-link" href="{{ route('admin.docentes.index') }}">Abrir</a>
                                        @elseif ($roleKey === 'admin' && ($action['slug'] ?? null) === 'usuarios')
                                            <a class="action-link" href="{{ route('admin.usuarios.index') }}">Abrir</a>
                                        @elseif ($roleKey === 'admin' && ($action['slug'] ?? null) === 'cerrar-sesion')
                                            <form class="action-link-form" method="POST" action="{{ route('auth.logout') }}">
                                                @csrf
                                                <button class="action-link action-link-button" type="submit">Salir</button>
                                            </form>
                                        @elseif (isset($action['slug']) && in_array($action['slug'], $roleModules, true))
                                            <a class="action-link" href="{{ route('roles.module', ['role' => $roleKey, 'module' => $action['slug']]) }}">Abrir</a>
                                        @else
                                            <span class="action-link disabled">Próximo</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </article>

                    {{-- Notificaciones (solo non-admin) --}}
                    @if ($roleKey !== 'admin')
                        <article class="card">
                            <div class="card-header">
                                <h3>Notificaciones</h3>
                                <span class="card-header-meta">Prioridad actual</span>
                            </div>
                            <div class="card-body">
                                <div class="alert-list">
                                    @foreach ($dashboard['alerts'] as $alert)
                                        <div class="alert-item">
                                            <strong>{{ $alert['title'] }}</strong>
                                            <span>{{ $alert['detail'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </article>
                    @endif

                </div>
            </section>

            @endif
        </main>
    </div>
</body>
</html>
