@extends('layouts.inspector-shell')

@section('title', 'Horarios docentes')
@section('inspector-nav', 'horario-docente')
@section('sidebar-title', 'Horarios')
@section('sidebar-body', 'Consulte el horario semanal asignado a cada docente de la institucion.')

@section('head')
    <style>
        .docente-list {
            display: grid;
            gap: var(--sp-md, 12px);
        }

        .docente-row {
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            gap: var(--sp-base, 16px);
            padding: var(--sp-base, 16px);
            border: 1px solid var(--border, #dde3f0);
            border-radius: var(--r-xl, 14px);
            background: var(--base-100, #fdfdff);
        }

        .docente-row-info strong {
            display: block;
            color: var(--navy-800, #0e1f55);
            margin-bottom: 4px;
        }

        .docente-row-info p {
            margin: 0;
            color: var(--muted, #64748b);
            font-size: 13px;
        }

        .mini-schedule {
            display: flex;
            gap: 3px;
            margin-top: 8px;
        }

        .mini-day {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
        }

        .mini-day-label {
            font-size: 10px;
            font-weight: 600;
            color: var(--muted, #64748b);
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .mini-block {
            width: 28px;
            height: 6px;
            border-radius: 3px;
            background: var(--base-300, #eef1f8);
        }

        .mini-block.filled {
            background: var(--color-primary, #152b74);
        }

        .docente-row-actions {
            display: flex;
            gap: var(--sp-sm, 8px);
            align-items: center;
        }

        .chip-count {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: var(--r-full, 9999px);
            font-family: 'DM Mono', monospace;
            font-size: 12px;
            font-weight: 600;
            background: var(--color-primary-light, #eef1fb);
            color: var(--color-primary, #152b74);
            white-space: nowrap;
        }

        .chip-empty {
            background: var(--base-200, #f7f9fe);
            color: var(--muted, #64748b);
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
            .docente-row {
                grid-template-columns: 1fr;
            }

            .docente-row-actions {
                justify-content: flex-start;
            }
        }
    </style>
@endsection

@section('sidebar-panel-items')
    <div>
        <strong>Vista individual</strong>
        <p>Seleccione un docente para ver su grilla horaria semanal completa.</p>
    </div>
    <div>
        <strong>Supervision operativa</strong>
        <p>Identifique docentes sin horario asignado para coordinar con el area academica.</p>
    </div>
@endsection

@section('crumbs')
    <a href="{{ route('auth.form') }}">Menu principal</a>
    <span>/</span>
    <a href="{{ route('roles.dashboard', 'inspector') }}">Dashboard inspector</a>
    <span>/</span>
    <span>Horarios docentes</span>
@endsection

@section('topbar-action')
    <a class="btn" href="{{ route('roles.dashboard', 'inspector') }}">Volver al panel</a>
@endsection

@section('content')
    <section class="hero" style="display: block;">
        <h2 style="margin: 0 0 6px; font-size: 22px; font-weight: 700; line-height: 1.2;">Horarios docentes</h2>
        @if ($institucionActiva)
            <p style="margin: 0; color: rgba(255,255,255,0.65); font-size: 13px;">
                Horario semanal del personal docente de <strong style="color: rgba(255,255,255,0.9);">{{ $institucionActiva->nombre }}</strong>
            </p>
        @else
            <p style="margin: 0; color: rgba(255,255,255,0.65); font-size: 13px;">Horario semanal del personal docente.</p>
        @endif
    </section>

    <section class="stats" style="--stats-columns: 3;">
        <article class="card"><strong class="metric">{{ $metricas['con_horario'] }}</strong><span>Docentes con horario</span></article>
        <article class="card"><strong class="metric">{{ $metricas['total_docentes'] }}</strong><span>Total docentes activos</span></article>
        <article class="card"><strong class="metric">{{ $metricas['total_bloques'] }}</strong><span>Bloques asignados</span></article>
    </section>

    <section class="panel">
        <div class="section-title">
            <h2>Docentes</h2>
            <span>{{ $docentes->total() }} registros</span>
        </div>

        <div class="toolbar">
            <form class="filters" method="GET" action="{{ route('inspector.horario-docente.index') }}">
                <input class="input" type="text" name="search" placeholder="Buscar por nombre, apellido o cedula" value="{{ request('search') }}">
                <button class="btn" type="submit">Filtrar</button>
                <a class="btn" href="{{ route('inspector.horario-docente.index') }}">Limpiar</a>
            </form>
        </div>

        @if ($docentes->count())
            <div class="docente-list">
                @foreach ($docentes as $docente)
                    @php
                        $persona = $docente->persona;
                        $nombre = trim(implode(' ', array_filter([
                            $persona?->primer_nombre,
                            $persona?->segundo_nombre,
                            $persona?->primer_apellido,
                            $persona?->segundo_apellido,
                        ])));
                        $docenteBloques = $bloques->get($docente->id, collect());
                        $diasConBloque = $docenteBloques->pluck('dia_semana')->unique();
                        $totalBloquesDocente = $docenteBloques->count();
                    @endphp
                    <article class="docente-row">
                        <div class="docente-row-info">
                            <strong>{{ $nombre !== '' ? $nombre : 'Sin nombre cargado' }}</strong>
                            <p>{{ $persona?->numero_identificacion ?: 'Sin cedula' }} &middot; {{ $docente->username }}</p>

                            <div class="mini-schedule">
                                @foreach ($dias as $diaNum => $diaNombre)
                                    @php $count = $docenteBloques->where('dia_semana', $diaNum)->count(); @endphp
                                    <div class="mini-day">
                                        <span class="mini-day-label">{{ mb_substr($diaNombre, 0, 2) }}</span>
                                        @for ($i = 0; $i < max($count, 1); $i++)
                                            <span class="mini-block {{ $i < $count ? 'filled' : '' }}"></span>
                                        @endfor
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="docente-row-actions">
                            @if ($totalBloquesDocente > 0)
                                <span class="chip-count">{{ $totalBloquesDocente }} bloques</span>
                            @else
                                <span class="chip-count chip-empty">Sin horario</span>
                            @endif
                            <a class="btn" href="{{ route('inspector.horario-docente.show', $docente) }}">Ver horario</a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="pager" style="margin-top: var(--sp-lg, 24px);">
                @if ($docentes->onFirstPage())
                    <span class="btn">Anterior</span>
                @else
                    <a class="btn" href="{{ $docentes->previousPageUrl() }}">Anterior</a>
                @endif

                <span class="btn">Pagina {{ $docentes->currentPage() }} de {{ $docentes->lastPage() }}</span>

                @if ($docentes->hasMorePages())
                    <a class="btn" href="{{ $docentes->nextPageUrl() }}">Siguiente</a>
                @else
                    <span class="btn">Siguiente</span>
                @endif
            </div>
        @else
            <div class="empty-state">
                <strong>Sin docentes activos</strong>
                <p>No se encontraron docentes{{ request('search') ? ' con los filtros actuales' : '' }}.</p>
            </div>
        @endif
    </section>
@endsection
