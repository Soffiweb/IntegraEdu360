@extends('layouts.inspector-shell')

@section('title', 'Dashboard inspector')
@section('inspector-nav', 'dashboard')
@section('sidebar-title', 'Inspector institucional')
@section('sidebar-body', 'Panel principal para control de convivencia, observaciones y seguimiento de casos prioritarios.')
@section('sidebar-panel-copy', 'El flujo del inspector mantiene la misma base visual entre el resumen inicial y cada modulo operativo del panel.')

@section('head')
    <style>
        .inspector-dashboard-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(320px, 0.85fr);
            gap: 24px;
        }

        .inspector-dashboard-stack {
            display: grid;
            gap: 24px;
        }

        .inspector-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .inspector-kpi-card {
            position: relative;
            overflow: hidden;
        }

        .inspector-kpi-card::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: var(--p);
        }

        .inspector-kpi-card:nth-child(2n)::before {
            background: var(--s);
        }

        .inspector-kpi-card:nth-child(3n)::before {
            background: var(--a);
        }

        .inspector-kpi-label {
            display: block;
            color: var(--bc2);
            font-size: 13px;
            margin-bottom: 10px;
        }

        .inspector-kpi-trend {
            display: inline-flex;
            margin-top: 10px;
        }

        .inspector-list {
            display: grid;
            gap: 12px;
        }

        .inspector-option-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }

        .inspector-option-card {
            display: grid;
            gap: 16px;
            min-height: 100%;
            padding: 20px;
            border: var(--brd);
            border-radius: var(--rl);
            background: linear-gradient(180deg, color-mix(in srgb, var(--b2) 72%, var(--b1)), var(--b1));
            box-shadow: var(--sh-sm);
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }

        .inspector-option-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--sh-md);
            border-color: color-mix(in srgb, var(--p) 28%, transparent);
        }

        .inspector-option-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .inspector-option-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: color-mix(in srgb, var(--p) 12%, transparent);
            color: var(--p);
            font-size: 18px;
            flex-shrink: 0;
        }

        .inspector-option-tag {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 10px;
            border-radius: 9999px;
            background: color-mix(in srgb, var(--s) 14%, transparent);
            color: color-mix(in srgb, var(--s) 78%, #000);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .inspector-option-body {
            display: grid;
            gap: 8px;
        }

        .inspector-option-body strong {
            color: var(--bc);
            font-size: 16px;
            line-height: 1.35;
        }

        .inspector-option-body p {
            margin: 0;
            color: var(--bc2);
            font-size: 14px;
            line-height: 1.6;
        }

        .inspector-option-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: auto;
        }

        .inspector-option-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--p);
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }

        .inspector-option-link:hover {
            color: color-mix(in srgb, var(--p) 82%, #000);
            text-decoration: none;
        }

        .inspector-list-item {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 16px;
            border: var(--brd);
            border-radius: var(--r);
            background: color-mix(in srgb, var(--b2) 55%, var(--b1));
        }

        .inspector-list-item strong {
            display: block;
            color: var(--bc);
            font-size: 15px;
            margin-bottom: 4px;
        }

        .inspector-list-item p {
            color: var(--bc2);
            font-size: 14px;
            line-height: 1.55;
        }

        .inspector-list-item .btn {
            flex-shrink: 0;
        }

        .inspector-timeline-item {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 14px;
            align-items: flex-start;
            padding: 16px;
            border: var(--brd);
            border-radius: var(--r);
            background: var(--b1);
        }

        .inspector-time {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 82px;
            padding: 6px 10px;
            border-radius: 9999px;
            background: color-mix(in srgb, var(--p) 12%, transparent);
            color: var(--p);
            font-size: 12px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .inspector-highlight {
            display: grid;
            gap: 10px;
        }

        .inspector-highlight-item {
            padding: 16px;
            border-radius: var(--r);
            background: var(--b1);
            border: var(--brd);
        }

        .inspector-highlight-item strong {
            display: block;
            margin-bottom: 4px;
            color: var(--bc);
        }

        .inspector-highlight-item p {
            color: var(--bc2);
            font-size: 14px;
        }

        @media (max-width: 1100px) {
            .inspector-dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .inspector-list-item,
            .inspector-timeline-item {
                grid-template-columns: 1fr;
                flex-direction: column;
            }
        }
    </style>
@endsection

@section('sidebar-panel-items')
    @foreach ($dashboard['alerts'] as $alert)
        <div>
            <strong>{{ $alert['title'] }}</strong>
            <p>{{ $alert['detail'] }}</p>
        </div>
    @endforeach
@endsection

@section('crumbs')
    <a href="{{ route('auth.form') }}">Menu principal</a>
    <span>/</span>
    <span>Dashboard inspector</span>
@endsection

@section('topbar-action')
    <a class="sw-btn sw-btn-primary" href="{{ route('auth.form') }}">
        <i class="fa-solid fa-right-left"></i> Cambiar acceso
    </a>
@endsection

@section('topbar-secondary')
    <span class="sw-badge sw-badge-primary">
        <i class="fa-solid fa-shield-halved"></i> Panel operativo
    </span>
@endsection

@php
    $inspectorLinks = [
        'novedades' => route('inspector.novedades.index'),
        'seguimiento' => route('roles.module', ['role' => 'inspector', 'module' => 'seguimiento']),
        'asistencia-docentes' => route('roles.module', ['role' => 'inspector', 'module' => 'asistencia-docentes']),
        'asistencia-estudiantes' => route('roles.module', ['role' => 'inspector', 'module' => 'asistencia-estudiantes']),
    ];

    $dashboardActions = collect($dashboard['actions'])
        ->mapWithKeys(fn (array $action) => [$action['slug'] => $action]);

    $inspectorOptions = [
        [
            'title' => 'Registrar novedades',
            'description' => $dashboardActions->get('novedades')['description'] ?? 'Documente observaciones disciplinarias o de convivencia.',
            'route' => $inspectorLinks['novedades'],
            'icon' => 'fa-clipboard-list',
            'tag' => 'Control',
        ],
        [
            'title' => 'Seguimiento de casos',
            'description' => $dashboardActions->get('seguimiento')['description'] ?? 'Revise casos activos y compromisos pendientes.',
            'route' => $inspectorLinks['seguimiento'],
            'icon' => 'fa-binoculars',
            'tag' => 'Gestion',
        ],
        [
            'title' => 'Distribucion de horas',
            'description' => 'Visualice la carga horaria semanal asignada a cada docente de la institucion.',
            'route' => route('inspector.distribucion-horas.index'),
            'icon' => 'fa-chart-column',
            'tag' => 'Docentes',
        ],
        [
            'title' => 'Horarios docentes',
            'description' => 'Consulte y genere la grilla horaria semanal de cada docente por dia y franja.',
            'route' => route('inspector.horario-docente.index'),
            'icon' => 'fa-calendar-days',
            'tag' => 'Semanal',
        ],
        [
            'title' => 'Asistencia docentes',
            'description' => $dashboardActions->get('asistencia-docentes')['description'] ?? 'Supervise la presencia diaria del personal docente asignado.',
            'route' => $inspectorLinks['asistencia-docentes'],
            'icon' => 'fa-chalkboard-user',
            'tag' => 'Control',
        ],
        [
            'title' => 'Asistencia estudiantes',
            'description' => $dashboardActions->get('asistencia-estudiantes')['description'] ?? 'Revise la asistencia estudiantil por paralelo y jornada.',
            'route' => $inspectorLinks['asistencia-estudiantes'],
            'icon' => 'fa-user-graduate',
            'tag' => 'Control',
        ],
    ];
@endphp

@section('content')
    <section class="hero">
        <div>
            <div class="eyebrow">Panel institucional</div>
            <h1>{{ $dashboard['greeting'] }}</h1>
            <p>Revise prioridades del turno, abra los modulos criticos y mantenga seguimiento continuo sobre novedades, horarios y distribucion docente dentro del mismo marco operativo.</p>
        </div>
        <div class="hero-side">
            <strong>{{ $dashboard['focus']['title'] }}</strong>
            <p>{{ $dashboard['focus']['body'] }}</p>
        </div>
    </section>

    @if (! empty($dashboard['kpis']))
        <section class="inspector-kpi-grid">
            @foreach ($dashboard['kpis'] as $kpi)
                <article class="sw-card inspector-kpi-card">
                    <span class="inspector-kpi-label">{{ $kpi['label'] }}</span>
                    <strong class="metric">{{ $kpi['value'] }}</strong>
                    @if (! empty($kpi['trend']))
                        <span class="sw-badge sw-badge-secondary inspector-kpi-trend">{{ $kpi['trend'] }}</span>
                    @endif
                </article>
            @endforeach
        </section>
    @endif

    <section class="inspector-dashboard-grid">
        <div class="inspector-dashboard-stack">
            <section class="sw-card">
                <div class="section-title">
                    <h2>Opciones del dashboard</h2>
                    <span>Accesos conectados al flujo operativo del inspector</span>
                </div>

                <div class="inspector-option-grid">
                    @foreach ($inspectorOptions as $option)
                        <article class="inspector-option-card">
                            <div class="inspector-option-header">
                                <span class="inspector-option-icon" aria-hidden="true">
                                    <i class="fa-solid {{ $option['icon'] }}"></i>
                                </span>
                                <span class="inspector-option-tag">{{ $option['tag'] }}</span>
                            </div>

                            <div class="inspector-option-body">
                                <strong>{{ $option['title'] }}</strong>
                                <p>{{ $option['description'] }}</p>
                            </div>

                            <div class="inspector-option-footer">
                                <a class="inspector-option-link" href="{{ $option['route'] }}">
                                    <span>Abrir modulo</span>
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                                <span class="sw-badge sw-badge-primary">{{ $option['tag'] }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="sw-card">
                <div class="section-title">
                    <h2>Agenda del dia</h2>
                    <span>Seguimiento operativo</span>
                </div>

                <div class="inspector-list">
                    @foreach ($dashboard['timeline'] as $item)
                        <article class="inspector-timeline-item">
                            <span class="inspector-time">{{ $item['time'] }}</span>
                            <div>
                                <strong>{{ $item['title'] }}</strong>
                                <p>{{ $item['detail'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="inspector-dashboard-stack">
            <section class="sw-card">
                <div class="section-title">
                    <h2>Alertas clave</h2>
                    <span>Prioridad actual</span>
                </div>

                <div class="inspector-list">
                    @foreach ($dashboard['alerts'] as $alert)
                        <article class="inspector-list-item">
                            <div>
                                <strong>{{ $alert['title'] }}</strong>
                                <p>{{ $alert['detail'] }}</p>
                            </div>
                            <span class="sw-badge sw-badge-warning">Atender</span>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="sw-card">
                <div class="section-title">
                    <h2>Enfoque institucional</h2>
                    <span>Resumen ejecutivo</span>
                </div>

                <div class="inspector-highlight">
                    @foreach ($dashboard['highlights'] as $highlight)
                        <article class="inspector-highlight-item">
                            <strong>{{ $highlight['title'] }}</strong>
                            <p>{{ $highlight['body'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>
    </section>
@endsection
