@extends('layouts.inspector-shell')

@section('title', 'Distribucion de horas docentes')
@section('inspector-nav', 'distribucion-horas')
@section('sidebar-title', 'Carga horaria')
@section('sidebar-body', 'Visualice como se distribuyen las horas semanales entre el personal docente de la institucion.')

@section('head')
    <style>
        .distribucion-grid {
            display: grid;
            gap: var(--sp-lg, 24px);
        }

        .docente-card {
            border: 1px solid var(--border, #dde3f0);
            border-radius: var(--r-xl, 14px);
            background: var(--base-100, #fdfdff);
            overflow: hidden;
        }

        .docente-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--sp-base, 16px);
            padding: var(--sp-base, 16px) var(--sp-lg, 24px);
            background: var(--base-200, #f7f9fe);
            border-bottom: 1px solid var(--base-300, #eef1f8);
        }

        .docente-header strong {
            color: var(--navy-800, #0e1f55);
            font-size: 15px;
        }

        .docente-meta {
            display: flex;
            gap: var(--sp-md, 12px);
            flex-wrap: wrap;
        }

        .docente-meta .chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: var(--r-full, 9999px);
            font-family: 'DM Mono', monospace;
            font-size: 12px;
            font-weight: 600;
        }

        .chip-horas {
            background: var(--color-primary-light, #eef1fb);
            color: var(--color-primary, #152b74);
        }

        .chip-cursos {
            background: #f0fdf4;
            color: #166534;
        }

        .chip-asignaturas {
            background: #fef3c7;
            color: #92400e;
        }

        .docente-card table {
            margin: 0;
            border: 0;
        }

        .docente-card thead th {
            background: transparent;
            border-bottom: 1px solid var(--base-300, #eef1f8);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--muted, #64748b);
            padding: 10px 16px;
        }

        .docente-card tbody td {
            padding: 10px 16px;
            border-bottom: 1px solid var(--base-300, #eef1f8);
            font-size: 13px;
        }

        .docente-card tbody tr:last-child td {
            border-bottom: 0;
        }

        .horas-cell {
            font-family: 'DM Mono', monospace;
            font-weight: 700;
            color: var(--navy-800, #0e1f55);
        }

        .bar-container {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .bar-track {
            flex: 1;
            height: 6px;
            border-radius: 3px;
            background: var(--base-300, #eef1f8);
            overflow: hidden;
            min-width: 60px;
        }

        .bar-fill {
            height: 100%;
            border-radius: 3px;
            background: var(--color-primary, #152b74);
            transition: width 0.3s ease;
        }

        .area-tag {
            display: inline-block;
            padding: 2px 8px;
            border-radius: var(--r-md, 6px);
            background: var(--base-200, #f7f9fe);
            color: var(--muted, #64748b);
            font-size: 11px;
        }

        .empty-state {
            text-align: center;
            padding: var(--sp-section, 64px) var(--sp-lg, 24px);
            color: var(--muted, #64748b);
        }

        .empty-state strong {
            display: block;
            margin-bottom: 8px;
            color: var(--navy-800, #0e1f55);
            font-size: 16px;
        }

        @media (max-width: 640px) {
            .docente-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
@endsection

@section('sidebar-panel-items')
    <div>
        <strong>Lectura operativa</strong>
        <p>Identifique docentes con sobrecarga o con horas disponibles para redistribucion.</p>
    </div>
    <div>
        <strong>Control institucional</strong>
        <p>Compare la carga horaria entre docentes para equilibrar la operacion academica.</p>
    </div>
@endsection

@section('crumbs')
    <a href="{{ route('auth.form') }}">Menu principal</a>
    <span>/</span>
    <a href="{{ route('roles.dashboard', 'inspector') }}">Dashboard inspector</a>
    <span>/</span>
    <span>Distribucion de horas</span>
@endsection

@section('topbar-action')
    <a class="btn" href="{{ route('roles.dashboard', 'inspector') }}">Volver al panel</a>
@endsection

@section('content')
    <section class="hero" style="display: block;">
        <h2 style="margin: 0 0 6px; font-size: 22px; font-weight: 700; line-height: 1.2;">Distribucion de horas docentes</h2>
        @if ($institucionActiva)
            <p style="margin: 0; color: rgba(255,255,255,0.65); font-size: 13px;">
                Carga horaria semanal del personal docente de <strong style="color: rgba(255,255,255,0.9);">{{ $institucionActiva->nombre }}</strong>
            </p>
        @else
            <p style="margin: 0; color: rgba(255,255,255,0.65); font-size: 13px;">Carga horaria semanal del personal docente.</p>
        @endif
    </section>

    <section class="stats">
        <article class="card"><strong class="metric">{{ $metricas['docentes_asignados'] }}</strong><span>Docentes con carga</span></article>
        <article class="card"><strong class="metric">{{ $metricas['total_docentes'] }}</strong><span>Total docentes</span></article>
        <article class="card"><strong class="metric">{{ $metricas['total_horas'] }}h</strong><span>Horas asignadas</span></article>
        <article class="card"><strong class="metric">{{ $metricas['promedio_horas'] }}h</strong><span>Promedio por docente</span></article>
    </section>

    <section class="panel">
        <div class="section-title">
            <h2>Carga horaria por docente</h2>
            <span>{{ $docentes->count() }} docentes</span>
        </div>

        <div class="toolbar">
            <form class="filters" method="GET" action="{{ route('inspector.distribucion-horas.index') }}">
                <input class="input" type="text" name="search" placeholder="Buscar por docente, asignatura o curso" value="{{ request('search') }}">
                <button class="btn" type="submit">Filtrar</button>
                <a class="btn" href="{{ route('inspector.distribucion-horas.index') }}">Limpiar</a>
            </form>
        </div>

        @if ($docentes->count())
            @php
                $maxHoras = $docentes->max('total_horas') ?: 1;
            @endphp

            <div class="distribucion-grid">
                @foreach ($docentes as $docente)
                    <article class="docente-card">
                        <div class="docente-header">
                            <div>
                                <strong>{{ $docente->nombre }}</strong>
                                <div class="bar-container" style="margin-top: 6px;">
                                    <div class="bar-track" style="width: 120px;">
                                        <div class="bar-fill" style="width: {{ round(($docente->total_horas / $maxHoras) * 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="docente-meta">
                                <span class="chip chip-horas">{{ $docente->total_horas }}h / semana</span>
                                <span class="chip chip-cursos">{{ $docente->total_cursos }} cursos</span>
                                <span class="chip chip-asignaturas">{{ $docente->total_asignaturas }} asignaturas</span>
                            </div>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Asignatura</th>
                                    <th>Area</th>
                                    <th>Curso</th>
                                    <th>Nivel</th>
                                    <th>Horas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($docente->asignaciones as $asignacion)
                                    <tr>
                                        <td><strong>{{ $asignacion->asignatura }}</strong></td>
                                        <td>
                                            @if ($asignacion->area)
                                                <span class="area-tag">{{ $asignacion->area }}</span>
                                            @else
                                                <span style="color: var(--muted, #64748b);">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $asignacion->curso }}</td>
                                        <td>{{ $asignacion->nivel ?: '—' }}</td>
                                        <td class="horas-cell">{{ $asignacion->horas }}h</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </article>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <strong>Sin asignaciones registradas</strong>
                <p>No se encontraron distribuciones de horas docentes{{ request('search') ? ' con los filtros actuales' : '' }}.</p>
            </div>
        @endif
    </section>
@endsection
