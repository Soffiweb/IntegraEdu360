@extends('layouts.admin-shell')

@section('title', 'Administracion de cursos')
@section('admin-nav', 'cursos')
@section('sidebar-panel-copy', 'Organice la estructura academica de cursos de la institucion activa.')

@section('sidebar-panel-items')
    <div>
        <strong>Catalogo institucional</strong>
        <p>Defina los cursos que conforman la oferta academica de la institucion.</p>
    </div>
    <div>
        <strong>Clasificacion por nivel</strong>
        <p>Filtre por nivel educativo para una gestion mas ordenada del catalogo.</p>
    </div>
@endsection

@section('crumbs')
    <a href="{{ route('auth.form') }}">Menu principal</a>
    <span>/</span>
    <a href="{{ route('roles.dashboard', 'admin') }}">Dashboard admin</a>
    <span>/</span>
    <span>Administracion de cursos</span>
@endsection

@section('content')
    <section class="hero" style="display:block;">
        <h2 style="margin:0 0 6px;font-size:22px;font-weight:700;line-height:1.2;">Administración de Cursos</h2>
        @if ($institucionActiva)
            <p style="margin:0;color:rgba(255,255,255,.65);font-size:13px;">
                Gestiona los cursos de <strong style="color:rgba(255,255,255,.9);">{{ $institucionActiva->nombre }}</strong>
            </p>
        @else
            <p style="margin:0;color:rgba(255,255,255,.65);font-size:13px;">Gestiona los cursos de todas las instituciones disponibles.</p>
        @endif
    </section>

    <section class="stats">
        <article class="card"><strong class="metric">{{ $metricas['total'] }}</strong><span>Total cursos</span></article>
        <article class="card"><strong class="metric">{{ $metricas['activos'] }}</strong><span>Activos</span></article>
        <article class="card"><strong class="metric">{{ $metricas['inactivos'] }}</strong><span>Inactivos</span></article>
        <article class="card"><strong class="metric">{{ $metricas['niveles'] }}</strong><span>Niveles distintos</span></article>
    </section>

    <section class="panel">
        <div class="section-title">
            <h2>Listado operativo</h2>
            <span>{{ $totalCursos }} resultados</span>
        </div>

        @if (session('status'))
            <div class="alert">{{ session('status') }}</div>
        @endif

        <div class="toolbar">
            <form class="filters" method="GET" action="{{ route('admin.cursos.index') }}">
                <input class="input" type="text" name="search" placeholder="Buscar por nombre o nivel" value="{{ request('search') }}">
                @if (! $institucionActiva)
                    <select class="select" name="institucion">
                        <option value="">Todas las instituciones</option>
                        @foreach ($instituciones as $institucion)
                            <option value="{{ $institucion->id }}" @selected((string) request('institucion') === (string) $institucion->id)>{{ $institucion->nombre }}</option>
                        @endforeach
                    </select>
                @endif
                <select class="select" name="nivel">
                    <option value="">Todos los niveles</option>
                    @foreach ($niveles as $value => $label)
                        <option value="{{ $value }}" @selected(request('nivel') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select class="select" name="estado">
                    <option value="">Todos los estados</option>
                    @foreach ($estados as $value => $label)
                        <option value="{{ $value }}" @selected(request('estado') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="btn" type="submit">Filtrar</button>
                <a class="btn" href="{{ route('admin.cursos.index') }}">Limpiar</a>
            </form>

            <button class="btn btn-primary" type="button" data-open-create>Nuevo curso</button>
        </div>

        @if ($totalCursos > 0)
            <table>
                <thead>
                    <tr>
                        <th>Curso</th>
                        @if (! $institucionActiva)
                            <th>Institucion</th>
                        @endif
                        <th>Nivel</th>
                        <th>Grado</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cursosAgrupados as $especialidadNombre => $grupoCursos)
                        <tr>
                            <td colspan="{{ $institucionActiva ? 5 : 6 }}" style="background:var(--navy-50,#eef1fb);padding:8px 14px;font-weight:700;font-size:12px;letter-spacing:.06em;text-transform:uppercase;color:var(--navy-700,#152b74);border-bottom:2px solid var(--navy-100,#ccd4f1);">
                                {{ $especialidadNombre }}
                                <span style="font-weight:400;color:var(--muted,#64748b);margin-left:8px;">{{ $grupoCursos->count() }} {{ $grupoCursos->count() === 1 ? 'curso' : 'cursos' }}</span>
                            </td>
                        </tr>
                        @foreach ($grupoCursos as $curso)
                            <tr>
                                <td class="name-cell">
                                    <strong>{{ $curso->nombre }}</strong>
                                </td>
                                @if (! $institucionActiva)
                                    <td>{{ $curso->institucion?->nombre ?: 'Sin institucion' }}</td>
                                @endif
                                <td>{{ $niveles[$curso->nivel] ?? $curso->nivel }}</td>
                                <td>{{ $curso->grado }}°</td>
                                <td><span class="badge badge-{{ strtolower($curso->estado) }}">{{ $estados[$curso->estado] ?? $curso->estado }}</span></td>
                                <td>
                                    <div class="actions">
                                        <a class="btn mini-btn" href="{{ route('admin.cursos.index', array_filter(['edit' => $curso->id, 'search' => request('search'), 'institucion' => request('institucion'), 'nivel' => request('nivel'), 'estado' => request('estado')])) }}">Editar</a>
                                        <form method="POST" action="{{ route('admin.cursos.destroy', $curso) }}" onsubmit="return confirm('Desea eliminar este curso?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger mini-btn" type="submit">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">No hay cursos con los filtros actuales.</div>
        @endif
    </section>
@endsection

@section('dialogs')
    <dialog class="modal" id="create-curso-modal">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <h3>Nuevo curso</h3>
                    <span>Defina el curso dentro de la estructura academica institucional.</span>
                </div>
                <button class="btn" type="button" data-close-create>x</button>
            </div>

            @if ($errors->any() && ! $cursoEditando)
                <div class="errors">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.cursos.store') }}">
                @csrf
                <div class="form-grid">
                    @if ($institucionActiva)
                        <input type="hidden" name="institucion_id" value="{{ $institucionActiva->id }}">
                        <div class="field-group full">
                            <label>Institucion</label>
                            <input class="input" type="text" value="{{ $institucionActiva->nombre }}" disabled>
                        </div>
                    @else
                        <div class="field-group full">
                            <label for="create_institucion_id">Institucion</label>
                            <select id="create_institucion_id" class="select" name="institucion_id" required>
                                <option value="">Seleccione</option>
                                @foreach ($instituciones as $institucion)
                                    <option value="{{ $institucion->id }}" @selected((string) old('institucion_id') === (string) $institucion->id)>{{ $institucion->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="field-group full">
                        <label for="create_nombre">Nombre del curso</label>
                        <input id="create_nombre" class="input" type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Ej: Décimo de Básica" required>
                    </div>
                    <div class="field-group full">
                        <label for="create_especialidad_id">Especialidad <span style="color:var(--muted);font-weight:400;">(opcional)</span></label>
                        <select id="create_especialidad_id" class="select" name="especialidad_id">
                            <option value="">Sin especialidad</option>
                            @foreach ($especialidadesDisponibles as $esp)
                                <option value="{{ $esp->id }}" @selected((string) old('especialidad_id') === (string) $esp->id)>
                                    {{ $esp->nombre }}{{ $esp->codigo ? ' ('.$esp->codigo.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field-group">
                        <label for="create_nivel">Nivel educativo</label>
                        <select id="create_nivel" class="select" name="nivel" required>
                            <option value="">Seleccione</option>
                            @foreach ($niveles as $value => $label)
                                <option value="{{ $value }}" @selected(old('nivel') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field-group">
                        <label for="create_grado">Grado</label>
                        <input id="create_grado" class="input" type="number" name="grado" value="{{ old('grado') }}" min="1" max="13" placeholder="Ej: 10" required>
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
                    <button class="btn btn-primary" type="submit">Crear curso</button>
                    <button class="btn" type="button" data-close-create>Cancelar</button>
                </div>
            </form>
        </div>
    </dialog>

    @if ($cursoEditando)
        <dialog class="modal" id="edit-curso-modal">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h3>Editar curso</h3>
                        <span>Actualice los datos del curso seleccionado.</span>
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

                <form method="POST" action="{{ route('admin.cursos.update', $cursoEditando) }}">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        @if ($institucionActiva)
                            <input type="hidden" name="institucion_id" value="{{ $institucionActiva->id }}">
                            <div class="field-group full">
                                <label>Institucion</label>
                                <input class="input" type="text" value="{{ $institucionActiva->nombre }}" disabled>
                            </div>
                        @else
                            <div class="field-group full">
                                <label for="edit_institucion_id">Institucion</label>
                                <select id="edit_institucion_id" class="select" name="institucion_id" required>
                                    <option value="">Seleccione</option>
                                    @foreach ($instituciones as $institucion)
                                        <option value="{{ $institucion->id }}" @selected((string) old('institucion_id', $cursoEditando->institucion_id) === (string) $institucion->id)>{{ $institucion->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="field-group full">
                            <label for="edit_nombre">Nombre del curso</label>
                            <input id="edit_nombre" class="input" type="text" name="nombre" value="{{ old('nombre', $cursoEditando->nombre) }}" required>
                        </div>
                        <div class="field-group full">
                            <label for="edit_especialidad_id">Especialidad <span style="color:var(--muted);font-weight:400;">(opcional)</span></label>
                            <select id="edit_especialidad_id" class="select" name="especialidad_id">
                                <option value="">Sin especialidad</option>
                                @foreach ($especialidadesDisponibles as $esp)
                                    <option value="{{ $esp->id }}" @selected((string) old('especialidad_id', $cursoEditando->especialidad_id) === (string) $esp->id)>
                                        {{ $esp->nombre }}{{ $esp->codigo ? ' ('.$esp->codigo.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group">
                            <label for="edit_nivel">Nivel educativo</label>
                            <select id="edit_nivel" class="select" name="nivel" required>
                                <option value="">Seleccione</option>
                                @foreach ($niveles as $value => $label)
                                    <option value="{{ $value }}" @selected(old('nivel', $cursoEditando->nivel) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group">
                            <label for="edit_grado">Grado</label>
                            <input id="edit_grado" class="input" type="number" name="grado" value="{{ old('grado', $cursoEditando->grado) }}" min="1" max="13" required>
                        </div>
                        <div class="field-group">
                            <label for="edit_estado">Estado</label>
                            <select id="edit_estado" class="select" name="estado" required>
                                @foreach ($estados as $value => $label)
                                    <option value="{{ $value }}" @selected(old('estado', $cursoEditando->estado) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="actions" style="margin-top: 18px;">
                        <button class="btn btn-primary" type="submit">Guardar cambios</button>
                        <a class="btn" href="{{ route('admin.cursos.index') }}">Cancelar edicion</a>
                    </div>
                </form>
            </div>
        </dialog>
    @endif
@endsection

@section('scripts')
    <script>
        const createModal = document.getElementById('create-curso-modal');
        const editModal = document.getElementById('edit-curso-modal');

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

        @if ($cursoEditando)
            editModal?.showModal();
        @endif

        @if ($errors->any() && ! $cursoEditando)
            createModal?.showModal();
        @endif
    </script>
@endsection
