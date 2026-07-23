@extends('layouts.inspector-shell')

@php
    $persona = $usuario->persona;
    $nombre = trim(implode(' ', array_filter([
        $persona?->primer_nombre,
        $persona?->segundo_nombre,
        $persona?->primer_apellido,
        $persona?->segundo_apellido,
    ])));
    $nombre = $nombre !== '' ? $nombre : 'Docente sin nombre';
@endphp

@section('title', 'Horario de ' . $nombre)
@section('inspector-nav', 'horario-docente')
@section('sidebar-title', 'Horario individual')
@section('sidebar-body', 'Grilla semanal del docente seleccionado con detalle de asignaturas, cursos y aulas.')

@section('head')
    <style>
        .horario-grid-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .horario-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid var(--border, #dde3f0);
            border-radius: var(--r-xl, 14px);
            overflow: hidden;
            min-width: 640px;
        }

        .horario-grid thead th {
            background: var(--navy-800, #0e1f55);
            color: #fff;
            padding: 12px 16px;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border: none;
        }

        .horario-grid thead th:first-child {
            text-align: left;
            width: 110px;
        }

        .horario-grid tbody td {
            padding: 0;
            border: 1px solid var(--base-300, #eef1f8);
            vertical-align: top;
            height: 72px;
        }

        .horario-grid tbody td:first-child {
            padding: 10px 12px;
            background: var(--base-200, #f7f9fe);
            font-family: 'DM Mono', monospace;
            font-size: 12px;
            font-weight: 600;
            color: var(--navy-800, #0e1f55);
            vertical-align: middle;
            white-space: nowrap;
        }

        .cell-block {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 8px 10px;
            height: 100%;
            background: var(--color-primary-light, #eef1fb);
            border-left: 3px solid var(--color-primary, #152b74);
        }

        .cell-block strong {
            display: block;
            font-size: 13px;
            color: var(--navy-800, #0e1f55);
            margin-bottom: 2px;
            line-height: 1.3;
        }

        .cell-block span {
            font-size: 11px;
            color: var(--muted, #64748b);
            line-height: 1.3;
        }

        .cell-empty {
            background: var(--base-100, #fdfdff);
        }

        .summary-chips {
            display: flex;
            gap: var(--sp-md, 12px);
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .summary-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 12px;
            border-radius: var(--r-full, 9999px);
            font-family: 'DM Mono', monospace;
            font-size: 12px;
            font-weight: 600;
        }

        .chip-bloques {
            background: var(--color-primary-light, #eef1fb);
            color: var(--color-primary, #152b74);
        }

        .chip-horas-total {
            background: #f0fdf4;
            color: #166534;
        }

        .empty-horario {
            text-align: center;
            padding: var(--sp-section, 64px) var(--sp-lg, 24px);
            color: var(--muted, #64748b);
        }

        .empty-horario strong {
            display: block;
            margin-bottom: 8px;
            color: var(--navy-800, #0e1f55);
            font-size: 16px;
        }

        @media print {
            .sidebar, .topbar, .crumbs, .hero, .btn { display: none !important; }
            .app-shell { display: block !important; }
            .main { margin: 0 !important; padding: 0 !important; }
            .panel { border: none !important; box-shadow: none !important; }
            .horario-grid { min-width: 0; }

            .print-header {
                display: block !important;
                margin-bottom: 16px;
            }
        }

        .print-header {
            display: none;
        }
    </style>
@endsection

@section('sidebar-panel-items')
    <div>
        <strong>{{ $nombre }}</strong>
        <p>{{ $persona?->numero_identificacion ?: 'Sin cedula' }}</p>
    </div>
    <div>
        <strong>Carga semanal</strong>
        <p>{{ $totalBloques }} bloques &middot; {{ $totalHoras }}h totales</p>
    </div>
@endsection

@section('crumbs')
    <a href="{{ route('auth.form') }}">Menu principal</a>
    <span>/</span>
    <a href="{{ route('roles.dashboard', 'inspector') }}">Dashboard inspector</a>
    <span>/</span>
    <a href="{{ route('inspector.horario-docente.index') }}">Horarios docentes</a>
    <span>/</span>
    <span>{{ $nombre }}</span>
@endsection

@section('topbar-action')
    <button class="btn btn-primary" type="button" onclick="window.print()">Imprimir horario</button>
@endsection

@section('content')
    <div class="print-header">
        <h1 style="margin: 0 0 4px; font-size: 18px;">Horario semanal &mdash; {{ $nombre }}</h1>
        <p style="margin: 0; font-size: 13px; color: #64748b;">
            @if ($institucionActiva) {{ $institucionActiva->nombre }} &middot; @endif
            {{ $totalBloques }} bloques &middot; {{ $totalHoras }}h semanales
        </p>
    </div>

    <section class="hero" style="display: block;">
        <h2 style="margin: 0 0 6px; font-size: 22px; font-weight: 700; line-height: 1.2;">{{ $nombre }}</h2>
        <p style="margin: 0; color: rgba(255,255,255,0.65); font-size: 13px;">
            {{ $persona?->numero_identificacion ?: 'Sin cedula' }} &middot; {{ $usuario->username }}
        </p>
        <div class="summary-chips" style="margin-top: 12px;">
            <span class="summary-chip chip-bloques" style="background: rgba(255,255,255,0.15); color: #fff;">{{ $totalBloques }} bloques</span>
            <span class="summary-chip chip-horas-total" style="background: rgba(255,255,255,0.15); color: #fff;">{{ $totalHoras }}h semanales</span>
        </div>
    </section>

    <section class="panel">
        <div class="section-title">
            <h2>Horario semanal</h2>
            <span>Lunes a viernes</span>
        </div>

        @if (count($grid) > 0)
            <div class="horario-grid-wrapper">
                <table class="horario-grid">
                    <thead>
                        <tr>
                            <th>Hora</th>
                            @foreach ($dias as $diaNombre)
                                <th>{{ $diaNombre }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($grid as $row)
                            <tr>
                                <td>
                                    {{ substr($row['franja']->inicio, 0, 5) }}<br>{{ substr($row['franja']->fin, 0, 5) }}
                                </td>
                                @foreach ($dias as $diaNum => $diaNombre)
                                    @php $bloque = $row[$diaNum] ?? null; @endphp
                                    @if ($bloque)
                                        <td>
                                            <div class="cell-block">
                                                <strong>{{ $bloque->asignatura }}</strong>
                                                <span>{{ $bloque->curso }}</span>
                                                @if ($bloque->aula)
                                                    <span>Aula: {{ $bloque->aula }}</span>
                                                @endif
                                            </div>
                                        </td>
                                    @else
                                        <td class="cell-empty"></td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-horario">
                <strong>Sin horario asignado</strong>
                <p>Este docente aun no tiene bloques horarios registrados en el sistema.</p>
            </div>
        @endif
    </section>
@endsection
