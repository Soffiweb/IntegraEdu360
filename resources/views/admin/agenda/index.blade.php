@extends('layouts.admin-shell')

@section('title', 'Agenda institucional')
@section('admin-nav', 'agenda')
@section('sidebar-title', $agendaRole === 'directivo' ? 'Directivo' : 'Administrador')
@section('sidebar-panel-copy', 'Centralice eventos, reuniones y fechas clave del calendario de la institucion.')

@section('head')
    @if ($agendaRole === 'directivo')
        <link rel="stylesheet" href="{{ asset('css/directivo-compact.css') }}?v={{ filemtime(public_path('css/directivo-compact.css')) }}">
    @endif
    <style>
        .progress {
            display: flex;
            align-items: center;
            gap: var(--sp-sm, 8px);
            min-width: 140px;
        }

        .progress-track {
            flex: 1;
            height: 8px;
            border-radius: var(--r-full, 9999px);
            background: var(--base-300, #eef1f8);
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: inherit;
            background: var(--color-primary, #152b74);
        }

        .progress-fill.is-complete {
            background: var(--color-success-text, #3b6d11);
        }

        .progress-value,
        .agenda-datetime {
            font-family: 'DM Mono', ui-monospace, monospace;
        }

        .agenda-datetime {
            white-space: nowrap;
        }

        .progress-value {
            min-width: 40px;
            text-align: right;
            font-size: 12px;
        }

        .agenda-description {
            max-width: 420px;
            white-space: pre-line;
        }

        .agenda-actions,
        .agenda-filter-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: var(--sp-sm, 8px);
        }

        .agenda-filters {
            display: grid;
            grid-template-columns: minmax(200px, 2fr) repeat(var(--agenda-filter-cols, 3), minmax(140px, 1fr)) auto;
            gap: var(--sp-md, 12px);
            align-items: end;
            margin-bottom: var(--sp-lg, 24px);
            padding: var(--sp-base, 16px);
            border: 1px solid var(--border, #dde3f0);
            border-radius: var(--r-lg, 10px);
        }

        .agenda-filters .field-group {
            min-width: 0;
        }

        .agenda-actions .btn,
        .agenda-filter-buttons .btn {
            gap: 6px;
        }

        .agenda-filter-buttons {
            flex-wrap: nowrap;
        }

        @media (max-width: 1280px) {
            .agenda-filters {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .agenda-filter-search,
            .agenda-filter-buttons {
                grid-column: 1 / -1;
            }

            .agenda-filter-buttons {
                justify-content: flex-end;
            }
        }

        @media (max-width: 640px) {
            .agenda-filters {
                grid-template-columns: 1fr;
            }

            .agenda-actions,
            .agenda-actions .btn,
            .agenda-filter-buttons .btn {
                flex: 1 1 auto;
                justify-content: center;
            }
        }
    </style>
@endsection

@section('crumbs')
    <a href="{{ route('auth.form') }}">Menu principal</a>
    <span>/</span>
    <a href="{{ route('roles.dashboard', $agendaRole) }}">{{ $agendaRole === 'directivo' ? 'Dashboard directivo' : 'Dashboard admin' }}</a>
    <span>/</span>
    <span>Agenda institucional</span>
@endsection

@section('content')
    <section class="hero">
        <div>
            <h2>Agenda Institucional</h2>
        </div>
    </section>

    <section class="stats">
        <article class="card"><strong class="metric">{{ $metricas['total'] }}</strong><span>Total actividades</span></article>
        <article class="card"><strong class="metric">{{ $metricas['pendientes'] }}</strong><span>Pendientes</span></article>
        <article class="card"><strong class="metric">{{ $metricas['en_progreso'] }}</strong><span>En progreso</span></article>
        <article class="card"><strong class="metric">{{ $metricas['cumplidas'] }}</strong><span>Cumplidas</span></article>
    </section>

    <section class="panel">
        <div class="section-title">
            <div>
                <h2>Actividades agendadas</h2>
                <span>{{ $agendas->total() }} resultados</span>
            </div>
            <div class="agenda-actions">
                <a class="btn" href="{{ route($agendaRoutePrefix.'print', request()->only(['search', 'institucion', 'cumplimiento', 'fecha_desde', 'fecha_hasta'])) }}" target="_blank" rel="noopener"><i class="fa-solid fa-print" aria-hidden="true"></i> Imprimir agenda</a>
                <button class="btn btn-primary" type="button" data-open-create><i class="fa-solid fa-plus" aria-hidden="true"></i> Nueva actividad</button>
            </div>
        </div>

        @if (session('status'))
            <div class="alert">{{ session('status') }}</div>
        @endif

        @if ($errors->has('fecha_desde') || $errors->has('fecha_hasta'))
            <div class="errors" role="alert">{{ $errors->first('fecha_desde') ?: $errors->first('fecha_hasta') }}</div>
        @endif

        <form class="agenda-filters" method="GET" action="{{ route($agendaRoutePrefix.'index') }}" @if (! $institucionActiva) style="--agenda-filter-cols: 4" @endif>
            <div class="field-group agenda-filter-search">
                <label for="filtro_search">Buscar</label>
                <input id="filtro_search" class="input" type="text" name="search" placeholder="Actividad o responsable" value="{{ request('search') }}">
            </div>
            @if (! $institucionActiva)
                <div class="field-group">
                    <label for="filtro_institucion">Institucion</label>
                    <select id="filtro_institucion" class="select" name="institucion">
                        <option value="">Todas las instituciones</option>
                        @foreach ($instituciones as $institucion)
                            <option value="{{ $institucion->id }}" @selected((string) request('institucion') === (string) $institucion->id)>{{ $institucion->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="field-group">
                <label for="filtro_cumplimiento">Cumplimiento</label>
                <select id="filtro_cumplimiento" class="select" name="cumplimiento">
                    <option value="">Todos</option>
                    @foreach ($cumplimientos as $value => $label)
                        <option value="{{ $value }}" @selected(request('cumplimiento') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field-group">
                <label for="fecha_desde">Fecha desde</label>
                <input id="fecha_desde" class="input" type="date" name="fecha_desde" value="{{ request('fecha_desde') }}">
            </div>
            <div class="field-group">
                <label for="fecha_hasta">Fecha hasta</label>
                <input id="fecha_hasta" class="input" type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" min="{{ request('fecha_desde') }}">
            </div>
            <div class="agenda-filter-buttons">
                <button class="btn" type="submit"><i class="fa-solid fa-filter" aria-hidden="true"></i> Filtrar</button>
                <a class="btn" href="{{ route($agendaRoutePrefix.'index') }}">Limpiar</a>
            </div>
        </form>

        @if ($agendas->count())
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Horario</th>
                        <th>Descripcion de la actividad</th>
                        <th>Responsable</th>
                        @if (! $institucionActiva)
                            <th>Institucion</th>
                        @endif
                        <th>Cumplimiento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($agendas as $agenda)
                        <tr>
                            <td class="agenda-datetime">{{ $agenda->fecha->format('Y-m-d') }}</td>
                            <td class="agenda-datetime">{{ substr($agenda->hora_desde, 0, 5) }} - {{ substr($agenda->hora_hasta, 0, 5) }}</td>
                            <td class="agenda-description">{{ $agenda->descripcion }}</td>
                            <td>{{ $agenda->responsable }}</td>
                            @if (! $institucionActiva)
                                <td>{{ $agenda->institucion?->nombre ?: 'Sin institucion' }}</td>
                            @endif
                            <td>
                                <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $agenda->porcentaje_cumplimiento }}" aria-label="Porcentaje de cumplimiento">
                                    <div class="progress-track">
                                        <div class="progress-fill {{ $agenda->porcentaje_cumplimiento === 100 ? 'is-complete' : '' }}" style="width: {{ $agenda->porcentaje_cumplimiento }}%"></div>
                                    </div>
                                    <span class="progress-value">{{ $agenda->porcentaje_cumplimiento }}%</span>
                                </div>
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="btn record-action" href="{{ route($agendaRoutePrefix.'index', array_filter(['edit' => $agenda->id, 'search' => request('search'), 'institucion' => request('institucion'), 'cumplimiento' => request('cumplimiento'), 'fecha_desde' => request('fecha_desde'), 'fecha_hasta' => request('fecha_hasta')])) }}" aria-label="Editar registro" title="Editar"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></a>
                                    <form method="POST" action="{{ route($agendaRoutePrefix.'destroy', $agenda) }}" onsubmit="return confirm('Desea eliminar esta actividad de la agenda?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger record-action" type="submit" aria-label="Eliminar registro" title="Eliminar"><i class="fa-solid fa-trash-can" aria-hidden="true"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pager">
                @if ($agendas->onFirstPage())
                    <span class="btn">Anterior</span>
                @else
                    <a class="btn" href="{{ $agendas->previousPageUrl() }}">Anterior</a>
                @endif

                <span class="btn">Pagina {{ $agendas->currentPage() }} de {{ $agendas->lastPage() }}</span>

                @if ($agendas->hasMorePages())
                    <a class="btn" href="{{ $agendas->nextPageUrl() }}">Siguiente</a>
                @else
                    <span class="btn">Siguiente</span>
                @endif
            </div>
        @else
            <div class="empty">No hay actividades agendadas con los filtros actuales.</div>
        @endif
    </section>
@endsection

@section('dialogs')
    @foreach (array_filter(['create' => true, 'edit' => $agendaEditando]) as $modo => $registro)
        @php
            $editando = $modo === 'edit';
            $valor = fn (string $campo, $defecto = null) => old($campo, $editando ? $registro->{$campo} : $defecto);
        @endphp
        <dialog class="modal" id="{{ $modo }}-agenda-modal">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h3>{{ $editando ? 'Editar actividad' : 'Nueva actividad' }}</h3>
                        <span>{{ $editando ? 'Actualice los datos y el avance de la actividad.' : 'Registre una actividad en la agenda institucional.' }}</span>
                    </div>
                    <button class="btn" type="button" data-close-{{ $modo }}>x</button>
                </div>

                @if ($errors->any() && ($editando || ! $agendaEditando))
                    <div class="errors">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ $editando ? route($agendaRoutePrefix.'update', $registro) : route($agendaRoutePrefix.'store') }}">
                    @csrf
                    @if ($editando)
                        @method('PUT')
                    @endif

                    <div class="form-grid">
                        @if ($institucionActiva)
                            <div class="field-group full">
                                <label>Institucion</label>
                                <input class="input" type="text" value="{{ $institucionActiva->nombre }}" disabled>
                            </div>
                        @else
                            <div class="field-group full">
                                <label for="{{ $modo }}_institucion_id">Institucion</label>
                                <select id="{{ $modo }}_institucion_id" class="select" name="institucion_id" required>
                                    <option value="">Seleccione</option>
                                    @foreach ($instituciones as $institucion)
                                        <option value="{{ $institucion->id }}" @selected((string) $valor('institucion_id') === (string) $institucion->id)>{{ $institucion->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="field-group">
                            <label for="{{ $modo }}_fecha">Fecha</label>
                            <input id="{{ $modo }}_fecha" class="input" type="date" name="fecha" value="{{ old('fecha', $editando ? $registro->fecha->format('Y-m-d') : '') }}" required>
                        </div>
                        <div class="field-group">
                            <label for="{{ $modo }}_hora_desde">Hora desde</label>
                            <input id="{{ $modo }}_hora_desde" class="input" type="time" name="hora_desde" value="{{ old('hora_desde', $editando ? substr($registro->hora_desde, 0, 5) : '') }}" required>
                        </div>
                        <div class="field-group">
                            <label for="{{ $modo }}_hora_hasta">Hora hasta</label>
                            <input id="{{ $modo }}_hora_hasta" class="input" type="time" name="hora_hasta" value="{{ old('hora_hasta', $editando ? substr($registro->hora_hasta, 0, 5) : '') }}" required>
                        </div>
                        <div class="field-group full">
                            <label for="{{ $modo }}_descripcion">Descripcion de la actividad</label>
                            <textarea id="{{ $modo }}_descripcion" class="input" name="descripcion" rows="3" maxlength="2000" required>{{ $valor('descripcion') }}</textarea>
                        </div>
                        <div class="field-group">
                            <label for="{{ $modo }}_responsable">Responsable</label>
                            <input id="{{ $modo }}_responsable" class="input" type="text" name="responsable" maxlength="255" value="{{ $valor('responsable') }}" required>
                        </div>
                        <div class="field-group">
                            <label for="{{ $modo }}_porcentaje_cumplimiento">Porcentaje de cumplimiento (%)</label>
                            <input id="{{ $modo }}_porcentaje_cumplimiento" class="input" type="number" name="porcentaje_cumplimiento" min="0" max="100" step="1" value="{{ $valor('porcentaje_cumplimiento', 0) }}" required>
                        </div>
                    </div>

                    <div class="actions" style="margin-top: 18px;">
                        <button class="btn btn-primary" type="submit">{{ $editando ? 'Guardar cambios' : 'Agendar actividad' }}</button>
                        @if ($editando)
                            <a class="btn" href="{{ route($agendaRoutePrefix.'index') }}">Cancelar edicion</a>
                        @else
                            <button class="btn" type="button" data-close-create>Cancelar</button>
                        @endif
                    </div>
                </form>
            </div>
        </dialog>
    @endforeach
@endsection

@section('scripts')
    <script>
        const createModal = document.getElementById('create-agenda-modal');
        const editModal = document.getElementById('edit-agenda-modal');

        document.querySelectorAll('[data-open-create]').forEach((button) => {
            button.addEventListener('click', () => createModal?.showModal());
        });

        document.querySelectorAll('[data-close-create]').forEach((button) => {
            button.addEventListener('click', () => createModal?.close());
        });

        document.querySelectorAll('[data-close-edit]').forEach((button) => {
            button.addEventListener('click', () => editModal?.close());
        });

        [createModal, editModal].forEach((modal) => {
            modal?.addEventListener('click', (event) => {
                const rect = modal.getBoundingClientRect();
                const inside = rect.top <= event.clientY && event.clientY <= rect.top + rect.height && rect.left <= event.clientX && event.clientX <= rect.left + rect.width;
                if (!inside) {
                    modal.close();
                }
            });
        });

        @if ($agendaEditando)
            editModal?.showModal();
        @endif

        @if ($errors->any() && ! $agendaEditando && ! $errors->has('fecha_desde') && ! $errors->has('fecha_hasta'))
            createModal?.showModal();
        @endif
    </script>
@endsection
