@extends('layouts.admin-shell')

@section('title', 'Administracion de asignaturas')
@section('admin-nav', 'asignaturas')
@section('sidebar-panel-copy', 'Gestione el catalogo de asignaturas de la institucion activa.')

@section('sidebar-panel-items')
    <div>
        <strong>Catalogo institucional</strong>
        <p>Defina las asignaturas que conforman la oferta academica de la institucion.</p>
    </div>
    <div>
        <strong>Clasificacion por especialidad y curso</strong>
        <p>Asigne cada asignatura a una especialidad y a los cursos donde se imparte.</p>
    </div>
@endsection

@section('crumbs')
    <a href="{{ route('auth.form') }}">Menu principal</a>
    <span>/</span>
    <a href="{{ route('roles.dashboard', 'admin') }}">Dashboard admin</a>
    <span>/</span>
    <span>Administracion de asignaturas</span>
@endsection

@section('content')
    <style>
        .asignaturas-toolbar {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 18px;
            align-items: end;
            margin-bottom: 2px;
        }

        .asignaturas-filters {
            display: flex;
            flex-wrap: nowrap;
            gap: 12px;
            align-items: end;
        }

        .asignaturas-filters .filter-span-4 {
            flex: 1.7 1 300px;
        }

        .asignaturas-filters .filter-span-3,
        .asignaturas-filters .filter-span-2,
        .asignaturas-filters .filter-span-3-wide {
            flex: 1 1 180px;
            min-width: 0;
        }

        .asignaturas-filters .filter-span-6 {
            flex: 0 0 auto;
            margin-left: auto;
        }

        .filter-search {
            min-width: 0;
        }

        .filter-actions {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 10px;
            flex-wrap: nowrap;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .toolbar-actions .btn,
        .filter-actions .btn {
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }

        .toolbar-actions .btn {
            min-width: 156px;
        }

        .filter-actions .btn {
            min-width: 82px;
        }

        @media (max-width: 1180px) {
            .asignaturas-toolbar {
                grid-template-columns: 1fr;
            }

            .asignaturas-filters {
                flex-wrap: wrap;
            }

            .asignaturas-filters .filter-span-6 {
                margin-left: 0;
            }

            .toolbar-actions {
                justify-content: flex-start;
            }
        }

        @media (max-width: 860px) {
            .asignaturas-filters .filter-span-4,
            .asignaturas-filters .filter-span-3,
            .asignaturas-filters .filter-span-2,
            .asignaturas-filters .filter-span-3-wide {
                flex: 1 1 calc(50% - 6px);
            }

            .asignaturas-filters .filter-span-6 {
                flex: 1 1 100%;
            }
        }

        @media (max-width: 640px) {
            .asignaturas-filters {
                display: grid;
                grid-template-columns: 1fr;
            }

            .asignaturas-filters .filter-span-4,
            .asignaturas-filters .filter-span-3,
            .asignaturas-filters .filter-span-2,
            .asignaturas-filters .filter-span-3-wide,
            .asignaturas-filters .filter-span-6 {
                flex: initial;
                margin-left: 0;
            }

            .filter-actions,
            .toolbar-actions {
                width: 100%;
            }

            .filter-actions .btn,
            .toolbar-actions .btn {
                flex: 1 1 100%;
                width: 100%;
                min-width: 0;
            }
        }
    </style>

    <section class="hero" style="display:block;">
        <h2 style="margin:0 0 6px;font-size:22px;font-weight:700;line-height:1.2;">Administración de Asignaturas</h2>
        @if ($institucionActiva)
            <p style="margin:0;color:rgba(255,255,255,.65);font-size:13px;">
                Gestiona las asignaturas de <strong style="color:rgba(255,255,255,.9);">{{ $institucionActiva->nombre }}</strong>
            </p>
        @else
            <p style="margin:0;color:rgba(255,255,255,.65);font-size:13px;">Gestiona las asignaturas de todas las instituciones disponibles.</p>
        @endif
    </section>

    <section class="panel">
        <div class="section-title">
            <h2>Listado operativo</h2>
            <span>{{ $totalAsignaturas }} resultados</span>
        </div>

        @if (session('status'))
            <div class="alert">{{ session('status') }}</div>
        @endif

        <div class="toolbar asignaturas-toolbar">
            <form class="filters asignaturas-filters" method="GET" action="{{ route('admin.asignaturas.index') }}">
                <input class="input filter-search filter-span-4" type="text" name="search" placeholder="Buscar por nombre o código" value="{{ request('search') }}">

                @if (! $institucionActiva)
                    <select class="select filter-span-3" name="institucion">
                        <option value="">Todas las instituciones</option>
                        @foreach ($instituciones as $institucion)
                            <option value="{{ $institucion->id }}" @selected((string) request('institucion') === (string) $institucion->id)>{{ $institucion->nombre }}</option>
                        @endforeach
                    </select>
                @endif

                <select class="select filter-span-3" name="especialidad_id">
                    <option value="">Todas las especialidades</option>
                    <option value="ninguna" @selected(request('especialidad_id') === 'ninguna')>Sin especialidad (común)</option>
                    @foreach ($especialidades as $esp)
                        <option value="{{ $esp->id }}" @selected((string) request('especialidad_id') === (string) $esp->id)>{{ $esp->nombre }}</option>
                    @endforeach
                </select>

                <select class="select filter-span-3" name="curso_id">
                    <option value="">Todos los cursos</option>
                    @foreach ($niveles as $nivelVal => $nivelLabel)
                        @php $cursosFiltrados = $cursos->where('nivel', $nivelVal); @endphp
                        @if ($cursosFiltrados->isNotEmpty())
                            <optgroup label="{{ $nivelLabel }}">
                                @foreach ($cursosFiltrados as $curso)
                                    <option value="{{ $curso->id }}" @selected((string) request('curso_id') === (string) $curso->id)>{{ $curso->nombre }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                    @endforeach
                </select>

                <div class="filter-actions filter-span-6">
                    <button class="btn" type="submit" aria-label="Filtrar" title="Filtrar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </button>
                    <a class="btn" href="{{ route('admin.asignaturas.index') }}">Limpiar</a>
                </div>
            </form>

            <div class="toolbar-actions">
                <a class="btn" href="{{ route('admin.asignaturas.pdf') }}" target="_blank" style="display:inline-flex;align-items:center;gap:6px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    Exportar PDF
                </a>
                <button class="btn btn-primary" type="button" data-open-create>Nueva asignatura</button>
            </div>
        </div>

        @if ($totalAsignaturas > 0)
            <table>
                <thead>
                    <tr>
                        <th>Asignatura</th>
                        <th>Horas/sem.</th>
                        <th>Código</th>
                        @if (! $institucionActiva)
                            <th>Institución</th>
                        @endif
                        <th>Área</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalCols = $institucionActiva ? 6 : 7; @endphp
                    @foreach ($asignaturasAgrupadas as $especialidadNombre => $porCurso)
                        <tr>
                            <td colspan="{{ $totalCols }}" style="background:var(--navy-50,#eef1fb);padding:8px 14px;font-weight:700;font-size:12px;letter-spacing:.06em;text-transform:uppercase;color:var(--navy-700,#152b74);border-bottom:2px solid var(--navy-100,#ccd4f1);">
                                {{ $especialidadNombre }}
                            </td>
                        </tr>
                        @foreach ($porCurso as $cursoGroup)
                            <tr>
                                <td colspan="{{ $totalCols }}" style="background:var(--base-100,#f8f9fb);padding:6px 14px 6px 28px;font-weight:600;font-size:12px;color:var(--base-content,#303237);border-bottom:1px solid var(--base-200,#e5e7eb);">
                                    @if ($cursoGroup['curso'])
                                        {{ $cursoGroup['curso']->nombre }}
                                        <span style="font-weight:400;color:var(--muted,#64748b);margin-left:6px;">Grado {{ $cursoGroup['curso']->grado }}°</span>
                                    @else
                                        <span style="color:var(--muted,#64748b);font-weight:400;">Sin curso asignado</span>
                                    @endif
                                </td>
                            </tr>
                            @foreach ($cursoGroup['items'] as $asignatura)
                                <tr>
                                    <td class="name-cell" style="padding-left:28px;">
                                        <strong>{{ $asignatura->nombre }}</strong>
                                        @if ($asignatura->descripcion)
                                            <span style="display:block;font-size:11px;color:var(--muted);margin-top:2px;" title="{{ $asignatura->descripcion }}">{{ Str::limit($asignatura->descripcion, 55) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($asignatura->horas_semana)
                                            <span style="font-family:'DM Mono',monospace;font-size:13px;font-weight:600;">{{ $asignatura->horas_semana }}</span>
                                        @else
                                            <span style="color:var(--muted);">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($asignatura->codigo)
                                            <span style="font-family:'DM Mono',monospace;font-size:12px;">{{ $asignatura->codigo }}</span>
                                        @else
                                            <span style="color:var(--muted);">—</span>
                                        @endif
                                    </td>
                                    @if (! $institucionActiva)
                                        <td>{{ $asignatura->institucion?->nombre ?: 'Sin institución' }}</td>
                                    @endif
                                    <td>{{ $areas[$asignatura->area] ?? $asignatura->area }}</td>
                                    <td><span class="badge badge-{{ strtolower($asignatura->estado) }}">{{ $estados[$asignatura->estado] ?? $asignatura->estado }}</span></td>
                                    <td>
                                        <div class="actions">
                                            <a class="btn mini-btn" href="{{ route('admin.asignaturas.index', array_filter(['edit' => $asignatura->id, 'search' => request('search'), 'institucion' => request('institucion'), 'especialidad_id' => request('especialidad_id'), 'curso_id' => request('curso_id'), 'area' => request('area'), 'estado' => request('estado')])) }}">Editar</a>
                                            <form method="POST" action="{{ route('admin.asignaturas.destroy', $asignatura) }}" onsubmit="return confirm('¿Desea eliminar esta asignatura?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger mini-btn" type="submit">Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">No hay asignaturas con los filtros actuales.</div>
        @endif
    </section>

    <section class="stats">
        <article class="card"><strong class="metric">{{ $metricas['total'] }}</strong><span>Total asignaturas</span></article>
        <article class="card"><strong class="metric">{{ $metricas['activas'] }}</strong><span>Activas</span></article>
        <article class="card"><strong class="metric">{{ $metricas['inactivas'] }}</strong><span>Inactivas</span></article>
        <article class="card"><strong class="metric">{{ $metricas['areas'] }}</strong><span>Áreas distintas</span></article>
    </section>
@endsection

@section('dialogs')
    {{-- ── Modal: Nueva asignatura ─────────────────────────────────────── --}}
    <dialog class="modal" id="create-asignatura-modal">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <h3>Nueva asignatura</h3>
                    <span>Defina la asignatura dentro del catálogo académico institucional.</span>
                </div>
                <button class="btn" type="button" data-close-create>x</button>
            </div>

            @if ($errors->any() && ! $asignaturaEditando)
                <div class="errors">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.asignaturas.store') }}">
                @csrf
                <div class="form-grid">
                    @if ($institucionActiva)
                        <input type="hidden" name="institucion_id" value="{{ $institucionActiva->id }}">
                        <div class="field-group full">
                            <label>Institución</label>
                            <input class="input" type="text" value="{{ $institucionActiva->nombre }}" disabled>
                        </div>
                    @else
                        <div class="field-group full">
                            <label for="create_institucion_id">Institución</label>
                            <select id="create_institucion_id" class="select" name="institucion_id" required>
                                <option value="">Seleccione</option>
                                @foreach ($instituciones as $institucion)
                                    <option value="{{ $institucion->id }}" @selected((string) old('institucion_id') === (string) $institucion->id)>{{ $institucion->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="field-group full">
                        <label for="create_nombre">Nombre de la asignatura</label>
                        <input id="create_nombre" class="input" type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Ej: Matemática, Lengua y Literatura" required>
                    </div>

                    <div class="field-group">
                        <label for="create_codigo">Código <span style="color:var(--muted);font-weight:400;">(opcional)</span></label>
                        <input id="create_codigo" class="input" type="text" name="codigo" value="{{ old('codigo') }}" placeholder="Ej: MAT, LEN" maxlength="20">
                        <small class="field-note">Se guardará en mayúsculas.</small>
                    </div>

                    <div class="field-group">
                        <label for="create_area">Área del conocimiento</label>
                        <select id="create_area" class="select" name="area" required>
                            <option value="">Seleccione</option>
                            @foreach ($areas as $value => $label)
                                <option value="{{ $value }}" @selected(old('area') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field-group">
                        <label for="create_horas_semana">Horas / semana <span style="color:var(--muted);font-weight:400;">(opcional)</span></label>
                        <input id="create_horas_semana" class="input" type="number" name="horas_semana" value="{{ old('horas_semana') }}" min="1" max="40" placeholder="Ej: 5">
                    </div>

                    <div class="field-group full">
                        <label for="create_especialidad_id">Especialidad <span style="color:var(--muted);font-weight:400;">(opcional — dejar vacío si es común a todos)</span></label>
                        <select id="create_especialidad_id" class="select" name="especialidad_id">
                            <option value="">Sin especialidad (común)</option>
                            @foreach ($especialidades as $esp)
                                <option value="{{ $esp->id }}" @selected((string) old('especialidad_id') === (string) $esp->id)>{{ $esp->nombre }} ({{ $esp->codigo }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field-group full">
                        <label>Cursos donde se imparte</label>
                        <div style="display:flex;flex-direction:column;gap:10px;padding:10px;border:1px solid var(--base-300,#e2e8f0);border-radius:var(--r-md,6px);max-height:220px;overflow-y:auto;">
                            @foreach ($niveles as $nivelVal => $nivelLabel)
                                @php $cursosFiltrados = $cursos->where('nivel', $nivelVal); @endphp
                                @if ($cursosFiltrados->isNotEmpty())
                                    <div>
                                        <p style="margin:0 0 4px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);">{{ $nivelLabel }}</p>
                                        <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                            @foreach ($cursosFiltrados as $curso)
                                                <label style="display:flex;align-items:center;gap:5px;font-size:13px;cursor:pointer;">
                                                    <input type="checkbox" name="curso_ids[]" value="{{ $curso->id }}"
                                                        @checked(in_array((string) $curso->id, (array) old('curso_ids', [])))>
                                                    {{ $curso->nombre }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <div class="field-group full">
                        <label for="create_descripcion">Descripción <span style="color:var(--muted);font-weight:400;">(opcional)</span></label>
                        <textarea id="create_descripcion" class="textarea" name="descripcion" placeholder="Descripción breve de la asignatura" maxlength="500">{{ old('descripcion') }}</textarea>
                    </div>

                    <div class="field-group">
                        <label for="create_estado">Estado</label>
                        <select id="create_estado" class="select" name="estado" required>
                            @foreach ($estados as $value => $label)
                                <option value="{{ $value }}" @selected(old('estado', 'ACTIVO') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="actions" style="margin-top: 18px;">
                    <button class="btn btn-primary" type="submit">Crear asignatura</button>
                    <button class="btn" type="button" data-close-create>Cancelar</button>
                </div>
            </form>
        </div>
    </dialog>

    {{-- ── Modal: Editar asignatura ────────────────────────────────────── --}}
    @if ($asignaturaEditando)
        <dialog class="modal" id="edit-asignatura-modal">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h3>Editar asignatura</h3>
                        <span>Actualice los datos de la asignatura seleccionada.</span>
                    </div>
                    <button class="btn" type="button" data-close-edit>x</button>
                </div>

                @if ($errors->any())
                    <div class="errors">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.asignaturas.update', $asignaturaEditando) }}">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        @if ($institucionActiva)
                            <input type="hidden" name="institucion_id" value="{{ $institucionActiva->id }}">
                            <div class="field-group full">
                                <label>Institución</label>
                                <input class="input" type="text" value="{{ $institucionActiva->nombre }}" disabled>
                            </div>
                        @else
                            <div class="field-group full">
                                <label for="edit_institucion_id">Institución</label>
                                <select id="edit_institucion_id" class="select" name="institucion_id" required>
                                    <option value="">Seleccione</option>
                                    @foreach ($instituciones as $institucion)
                                        <option value="{{ $institucion->id }}" @selected((string) old('institucion_id', $asignaturaEditando->institucion_id) === (string) $institucion->id)>{{ $institucion->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="field-group full">
                            <label for="edit_nombre">Nombre de la asignatura</label>
                            <input id="edit_nombre" class="input" type="text" name="nombre" value="{{ old('nombre', $asignaturaEditando->nombre) }}" required>
                        </div>

                        <div class="field-group">
                            <label for="edit_codigo">Código <span style="color:var(--muted);font-weight:400;">(opcional)</span></label>
                            <input id="edit_codigo" class="input" type="text" name="codigo" value="{{ old('codigo', $asignaturaEditando->codigo) }}" maxlength="20">
                        </div>

                        <div class="field-group">
                            <label for="edit_area">Área del conocimiento</label>
                            <select id="edit_area" class="select" name="area" required>
                                <option value="">Seleccione</option>
                                @foreach ($areas as $value => $label)
                                    <option value="{{ $value }}" @selected(old('area', $asignaturaEditando->area) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field-group">
                            <label for="edit_horas_semana">Horas / semana <span style="color:var(--muted);font-weight:400;">(opcional)</span></label>
                            <input id="edit_horas_semana" class="input" type="number" name="horas_semana" value="{{ old('horas_semana', $asignaturaEditando->horas_semana) }}" min="1" max="40" placeholder="Ej: 5">
                        </div>

                        <div class="field-group full">
                            <label for="edit_especialidad_id">Especialidad <span style="color:var(--muted);font-weight:400;">(opcional)</span></label>
                            <select id="edit_especialidad_id" class="select" name="especialidad_id">
                                <option value="">Sin especialidad (común)</option>
                                @foreach ($especialidades as $esp)
                                    <option value="{{ $esp->id }}" @selected((string) old('especialidad_id', $asignaturaEditando->especialidad_id) === (string) $esp->id)>{{ $esp->nombre }} ({{ $esp->codigo }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field-group full">
                            <label>Cursos donde se imparte</label>
                            @php $cursosAsignados = old('curso_ids') !== null ? (array) old('curso_ids') : $asignaturaEditando->cursos->pluck('id')->map(fn($id) => (string) $id)->all(); @endphp
                            <div style="display:flex;flex-direction:column;gap:10px;padding:10px;border:1px solid var(--base-300,#e2e8f0);border-radius:var(--r-md,6px);max-height:220px;overflow-y:auto;">
                                @foreach ($niveles as $nivelVal => $nivelLabel)
                                    @php $cursosFiltrados = $cursos->where('nivel', $nivelVal); @endphp
                                    @if ($cursosFiltrados->isNotEmpty())
                                        <div>
                                            <p style="margin:0 0 4px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);">{{ $nivelLabel }}</p>
                                            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                                @foreach ($cursosFiltrados as $curso)
                                                    <label style="display:flex;align-items:center;gap:5px;font-size:13px;cursor:pointer;">
                                                        <input type="checkbox" name="curso_ids[]" value="{{ $curso->id }}"
                                                            @checked(in_array((string) $curso->id, $cursosAsignados))>
                                                        {{ $curso->nombre }}
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <div class="field-group full">
                            <label for="edit_descripcion">Descripción <span style="color:var(--muted);font-weight:400;">(opcional)</span></label>
                            <textarea id="edit_descripcion" class="textarea" name="descripcion" maxlength="500">{{ old('descripcion', $asignaturaEditando->descripcion) }}</textarea>
                        </div>

                        <div class="field-group">
                            <label for="edit_estado">Estado</label>
                            <select id="edit_estado" class="select" name="estado" required>
                                @foreach ($estados as $value => $label)
                                    <option value="{{ $value }}" @selected(old('estado', $asignaturaEditando->estado) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="actions" style="margin-top: 18px;">
                        <button class="btn btn-primary" type="submit">Guardar cambios</button>
                        <a class="btn" href="{{ route('admin.asignaturas.index') }}">Cancelar edición</a>
                    </div>
                </form>
            </div>
        </dialog>
    @endif
@endsection

@section('scripts')
    <script type="application/json" id="__ia_cfg">{"autoOpenEdit":@json((bool) $asignaturaEditando),"autoOpenCreate":@json($errors->any() && ! $asignaturaEditando)}</script>
    <script>
        const __cfg = JSON.parse(document.getElementById('__ia_cfg').textContent);
        const createModal = document.getElementById('create-asignatura-modal');
        const editModal   = document.getElementById('edit-asignatura-modal');

        document.querySelectorAll('[data-open-create]').forEach(b => b.addEventListener('click', () => createModal?.showModal()));
        document.querySelectorAll('[data-close-create]').forEach(b => b.addEventListener('click', () => createModal?.close()));
        document.querySelectorAll('[data-close-edit]').forEach(b => b.addEventListener('click', () => editModal?.close()));

        createModal?.addEventListener('click', (e) => {
            const r = createModal.getBoundingClientRect();
            if (!(r.top <= e.clientY && e.clientY <= r.top + r.height && r.left <= e.clientX && e.clientX <= r.left + r.width)) createModal.close();
        });

        editModal?.addEventListener('click', (e) => {
            const r = editModal.getBoundingClientRect();
            if (!(r.top <= e.clientY && e.clientY <= r.top + r.height && r.left <= e.clientX && e.clientX <= r.left + r.width)) editModal.close();
        });

        if (__cfg.autoOpenEdit)   editModal?.showModal();
        if (__cfg.autoOpenCreate) createModal?.showModal();
    </script>
@endsection
