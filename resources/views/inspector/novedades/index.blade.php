@extends('layouts.inspector-shell')

@section('title', 'Novedades de convivencia y disciplina')
@section('inspector-nav', 'novedades')
@section('sidebar-title', 'Novedades')
@section('sidebar-body', 'Registre y de seguimiento a la disciplina, convivencia y presentacion personal de los estudiantes.')

@section('head')
    <style>
        .novedad-tabla-wrap {
            overflow-x: auto;
        }

        .novedad-estudiante strong {
            display: block;
            color: var(--navy-800, var(--p));
        }

        .novedad-estudiante span {
            color: var(--bc2);
            font-size: 12px;
        }

        .novedad-accion {
            max-width: 260px;
            color: var(--bc2);
            font-size: 13px;
            line-height: 1.45;
        }

        .empty-state {
            text-align: center;
            padding: var(--sp-section, 64px) var(--sp-lg, 24px);
            color: var(--bc2);
        }

        .empty-state strong {
            display: block;
            margin-bottom: 8px;
            color: var(--p);
            font-size: 16px;
        }

        .modal-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .modal-form-grid .full {
            grid-column: 1 / -1;
        }

        .modal-field label {
            display: block;
            margin-bottom: 4px;
            color: var(--p);
            font-size: 12px;
            font-weight: 600;
        }

        .modal-note {
            margin-top: 14px;
            padding: 10px 12px;
            border-radius: var(--r);
            background: color-mix(in srgb, var(--in) 10%, transparent);
            color: color-mix(in srgb, var(--in) 68%, #000);
            font-size: 12px;
            line-height: 1.5;
        }

        @media (max-width: 640px) {
            .modal-form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('sidebar-panel-items')
    <div>
        <strong>Marco normativo</strong>
        <p>Aplique el Codigo de Convivencia Institucional al calificar la gravedad de cada novedad.</p>
    </div>
    <div>
        <strong>Coordinacion con DECE</strong>
        <p>Derive a Consejeria Estudiantil los casos que superen el ambito disciplinario ordinario.</p>
    </div>
@endsection

@section('crumbs')
    <a href="{{ route('auth.form') }}">Menu principal</a>
    <span>/</span>
    <a href="{{ route('roles.dashboard', 'inspector') }}">Dashboard inspector</a>
    <span>/</span>
    <span>Novedades</span>
@endsection

@section('topbar-action')
    <button type="button" class="btn btn-primary" onclick="document.getElementById('modalNovedad').showModal()">
        <i class="fa-solid fa-plus"></i> Registrar novedad
    </button>
@endsection

@section('topbar-secondary')
    <a class="btn" href="{{ route('roles.dashboard', 'inspector') }}">Volver al panel</a>
@endsection

@section('content')
    <section class="hero" style="display: block;">
        <h2 style="margin: 0 0 6px; font-size: 22px; font-weight: 700; line-height: 1.2;">Novedades de convivencia y disciplina</h2>
        <p style="margin: 0; color: rgba(255,255,255,0.7); font-size: 13px; max-width: 720px;">
            Registre observaciones disciplinarias, de convivencia, presentacion personal y asistencia conforme a las
            funciones de control e inspeccion establecidas por el Ministerio de Educacion, y derive a Consejeria
            Estudiantil (DECE) los casos que lo requieran.
        </p>
    </section>

    <section class="stats" style="--stats-columns: 4;">
        <article class="card"><strong class="metric">{{ $stats['total'] }}</strong><span>Novedades registradas</span></article>
        <article class="card"><strong class="metric">{{ $stats['abiertas'] }}</strong><span>Abiertas o en seguimiento</span></article>
        <article class="card"><strong class="metric">{{ $stats['derivadas_dece'] }}</strong><span>Derivadas a DECE</span></article>
        <article class="card"><strong class="metric">{{ $stats['cerradas'] }}</strong><span>Cerradas</span></article>
    </section>

    <section class="panel">
        <div class="section-title">
            <h2>Casos registrados</h2>
            <span>{{ $novedades->count() }} resultados &middot; Datos de ejemplo</span>
        </div>

        <div class="toolbar">
            <form class="filters" method="GET" action="{{ route('inspector.novedades.index') }}">
                <input class="input" type="text" name="search" placeholder="Buscar por estudiante o curso" value="{{ $filtros['search'] }}">

                <select class="input" name="tipo">
                    <option value="">Todos los tipos</option>
                    @foreach ($tipos as $opcion)
                        <option value="{{ $opcion }}" {{ $filtros['tipo'] === $opcion ? 'selected' : '' }}>{{ $opcion }}</option>
                    @endforeach
                </select>

                <select class="input" name="gravedad">
                    <option value="">Toda gravedad</option>
                    @foreach ($gravedades as $opcion)
                        <option value="{{ $opcion }}" {{ $filtros['gravedad'] === $opcion ? 'selected' : '' }}>{{ $opcion }}</option>
                    @endforeach
                </select>

                <select class="input" name="estado">
                    <option value="">Todo estado</option>
                    @foreach ($estados as $opcion)
                        <option value="{{ $opcion }}" {{ $filtros['estado'] === $opcion ? 'selected' : '' }}>{{ $opcion }}</option>
                    @endforeach
                </select>

                <button class="btn" type="submit">Filtrar</button>
                <a class="btn" href="{{ route('inspector.novedades.index') }}">Limpiar</a>
            </form>
        </div>

        @if ($novedades->count())
            <div class="novedad-tabla-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Tipo</th>
                            <th>Gravedad</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Responsable</th>
                            <th>Accion tomada</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($novedades as $novedad)
                            <tr>
                                <td class="novedad-estudiante">
                                    <strong>{{ $novedad['estudiante'] }}</strong>
                                    <span>{{ $novedad['curso'] }}</span>
                                </td>
                                <td>{{ $novedad['tipo'] }}</td>
                                <td>
                                    @php
                                        $gravedadBadge = match ($novedad['gravedad']) {
                                            'Grave' => 'sw-badge-warning',
                                            'Moderada' => 'sw-badge-secondary',
                                            default => 'sw-badge-primary',
                                        };
                                    @endphp
                                    <span class="sw-badge {{ $gravedadBadge }}">{{ $novedad['gravedad'] }}</span>
                                </td>
                                <td>{{ $novedad['fecha'] }}</td>
                                <td>
                                    @php
                                        $estadoBadge = match ($novedad['estado']) {
                                            'Cerrado' => 'sw-badge-success',
                                            'Derivado a DECE' => 'sw-badge-warning',
                                            'En seguimiento' => 'sw-badge-secondary',
                                            default => 'sw-badge-primary',
                                        };
                                    @endphp
                                    <span class="sw-badge {{ $estadoBadge }}">{{ $novedad['estado'] }}</span>
                                </td>
                                <td>{{ $novedad['responsable'] }}</td>
                                <td class="novedad-accion">{{ $novedad['accion'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <strong>Sin novedades registradas</strong>
                <p>No se encontraron casos con los filtros actuales.</p>
            </div>
        @endif
    </section>
@endsection

@section('dialogs')
    <dialog class="modal" id="modalNovedad">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Registrar novedad</h3>
                <button type="button" class="btn" onclick="document.getElementById('modalNovedad').close()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form class="modal-form-grid" onsubmit="document.getElementById('modalNovedad').close(); return false;">
                <div class="modal-field full">
                    <label for="novedad-estudiante">Estudiante</label>
                    <input class="input" type="text" id="novedad-estudiante" name="estudiante" placeholder="Nombre del estudiante">
                </div>

                <div class="modal-field">
                    <label for="novedad-curso">Curso y paralelo</label>
                    <input class="input" type="text" id="novedad-curso" name="curso" placeholder="Ej. 9no B">
                </div>

                <div class="modal-field">
                    <label for="novedad-fecha">Fecha</label>
                    <input class="input" type="date" id="novedad-fecha" name="fecha">
                </div>

                <div class="modal-field">
                    <label for="novedad-tipo">Tipo de novedad</label>
                    <select class="input" id="novedad-tipo" name="tipo">
                        @foreach ($tipos as $opcion)
                            <option value="{{ $opcion }}">{{ $opcion }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-field">
                    <label for="novedad-gravedad">Gravedad</label>
                    <select class="input" id="novedad-gravedad" name="gravedad">
                        @foreach ($gravedades as $opcion)
                            <option value="{{ $opcion }}">{{ $opcion }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-field full">
                    <label for="novedad-descripcion">Descripcion de la novedad</label>
                    <textarea class="input" id="novedad-descripcion" name="descripcion" rows="3" style="height: auto; padding-top: 10px;" placeholder="Detalle lo observado"></textarea>
                </div>

                <div class="modal-field full">
                    <label for="novedad-accion">Accion tomada o acuerdo</label>
                    <textarea class="input" id="novedad-accion" name="accion" rows="2" style="height: auto; padding-top: 10px;" placeholder="Ej. Cita a representante, acta de compromiso, derivacion a DECE"></textarea>
                </div>

                <div class="modal-note full">
                    <i class="fa-solid fa-circle-info"></i>
                    Formulario de captura listo para conectarse al registro real de novedades. Por ahora no persiste datos.
                </div>

                <div class="full" style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 4px;">
                    <button type="button" class="btn" onclick="document.getElementById('modalNovedad').close()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar novedad</button>
                </div>
            </form>
        </div>
    </dialog>
@endsection
