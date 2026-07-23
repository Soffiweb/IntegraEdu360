@extends('layouts.admin-shell')

@section('title', 'Dashboard administrativo')
@section('sidebar-title', 'Administrador')
@section('sidebar-body', 'Panel principal con acceso directo a los modulos operativos de la institucion activa.')

@section('head')
    <style>
        .admin-dashboard-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.3fr) minmax(320px, 0.7fr);
            gap: var(--sp-lg, 24px);
        }

        .admin-dashboard-stack {
            display: grid;
            gap: var(--sp-lg, 24px);
        }

        .quick-links {
            display: grid;
            gap: var(--sp-md, 12px);
        }

        .quick-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--sp-base, 16px);
            padding: var(--sp-base, 16px);
            border: 1px solid var(--border, #dde3f0);
            border-radius: var(--r-xl, 14px);
            background: var(--base-100, #fdfdff);
        }

        .quick-link strong {
            display: block;
            margin-bottom: 4px;
            color: var(--navy-800, #0e1f55);
        }

        .quick-link p {
            margin: 0;
            color: var(--muted, #64748b);
            font-size: 13px;
        }

        .agenda-list,
        .alert-list {
            display: grid;
            gap: var(--sp-md, 12px);
        }

        .agenda-item,
        .alert-item {
            padding: var(--sp-base, 16px);
            border-radius: var(--r-lg, 10px);
            background: var(--base-200, #f7f9fe);
            border: 1px solid var(--base-300, #eef1f8);
        }

        .agenda-item strong,
        .alert-item strong {
            display: block;
            color: var(--navy-800, #0e1f55);
            margin-bottom: 4px;
        }

        .agenda-item p,
        .alert-item p {
            margin: 0;
            color: var(--muted, #64748b);
            font-size: 13px;
        }

        .agenda-time {
            display: inline-flex;
            margin-bottom: 8px;
            padding: 4px 10px;
            border-radius: var(--r-full, 9999px);
            background: var(--color-primary-light, #eef1fb);
            color: var(--color-primary, #152b74);
            font-family: 'DM Mono', monospace;
            font-size: 12px;
            font-weight: 600;
        }

        @media (max-width: 1100px) {
            .admin-dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('crumbs')
    <a href="{{ route('auth.form') }}">Menu principal</a>
    <span>/</span>
    <span>Dashboard admin</span>
@endsection

@section('topbar-action')
    <a class="btn btn-primary" href="{{ route('auth.form') }}">Cambiar acceso</a>
@endsection

@php
    $adminLinks = [
        'datos-institucionales' => route('admin.institucion.index'),
        'periodos' => route('admin.periodos.index'),
        'usuarios' => route('admin.usuarios.index'),
        'docentes' => route('admin.docentes.index'),
        'estudiantes' => route('admin.estudiantes.index'),
        'cursos' => route('admin.cursos.index'),
        'paralelos' => route('admin.paralelos.index'),
        'especialidades' => route('admin.especialidades.index'),
        'asignaturas' => route('admin.asignaturas.index'),
    ];
@endphp

@section('content')
    <section class="hero">
        <div>
            <div class="eyebrow">Panel institucional</div>
            <h1>{{ $dashboard['greeting'] }}</h1>
            <p>Este inicio ahora comparte la misma estructura visual que el resto del modulo de Administrador para mantener continuidad entre el dashboard y cada opcion del menu.</p>
        </div>
        <div class="hero-side">
            <strong>{{ $dashboard['focus']['title'] }}</strong>
            <p>{{ $dashboard['focus']['body'] }}</p>
        </div>
    </section>

    <section class="stats" style="--stats-columns: 3;">
        @foreach ($dashboard['kpis'] as $kpi)
            <article class="card">
                <strong class="metric">{{ $kpi['value'] }}</strong>
                <span>{{ $kpi['label'] }}</span>
                <span style="margin-top: 10px; font-weight: 600; color: var(--navy-700, #152b74);">{{ $kpi['trend'] }}</span>
            </article>
        @endforeach
    </section>

    <section class="admin-dashboard-grid">
        <div class="admin-dashboard-stack">
            <section class="panel">
                <div class="section-title">
                    <h2>Accesos directos</h2>
                    <span>Ingreso directo a cada modulo</span>
                </div>

                <div class="quick-links">
                    @foreach ($dashboard['actions'] as $action)
                        @if (($action['slug'] ?? null) === 'cerrar-sesion')
                            <div class="quick-link">
                                <div>
                                    <strong>{{ $action['label'] }}</strong>
                                    <p>{{ $action['description'] }}</p>
                                </div>
                                <form method="POST" action="{{ route('auth.logout') }}">
                                    @csrf
                                    <button class="btn" type="submit">Salir</button>
                                </form>
                            </div>
                        @elseif (isset($adminLinks[$action['slug'] ?? '']))
                            <div class="quick-link">
                                <div>
                                    <strong>{{ $action['label'] }}</strong>
                                    <p>{{ $action['description'] }}</p>
                                </div>
                                <a class="btn" href="{{ $adminLinks[$action['slug']] }}">Abrir</a>
                            </div>
                        @endif
                    @endforeach
                </div>
            </section>

            <section class="panel">
                <div class="section-title">
                    <h2>Agenda del dia</h2>
                    <span>Seguimiento operativo</span>
                </div>

                <div class="agenda-list">
                    @foreach ($dashboard['timeline'] as $item)
                        <article class="agenda-item">
                            <span class="agenda-time">{{ $item['time'] }}</span>
                            <strong>{{ $item['title'] }}</strong>
                            <p>{{ $item['detail'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="admin-dashboard-stack">
            <section class="panel">
                <div class="section-title">
                    <h2>Alertas clave</h2>
                    <span>Prioridad actual</span>
                </div>

                <div class="alert-list">
                    @foreach ($dashboard['alerts'] as $alert)
                        <article class="alert-item">
                            <strong>{{ $alert['title'] }}</strong>
                            <p>{{ $alert['detail'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="panel">
                <div class="section-title">
                    <h2>Enfoque institucional</h2>
                    <span>Resumen ejecutivo</span>
                </div>

                <div class="quick-links">
                    @foreach ($dashboard['highlights'] as $highlight)
                        <article class="quick-link">
                            <div>
                                <strong>{{ $highlight['title'] }}</strong>
                                <p>{{ $highlight['body'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>
    </section>
@endsection
