@extends('layouts.admin-shell')

@section('title', 'Administracion de paralelos')
@section('admin-nav', 'paralelos')
@section('sidebar-panel-copy', 'Defina y supervise los paralelos academicos de la institucion activa.')

@section('sidebar-panel-items')
    <div>
        <strong>Secciones por curso</strong>
        <p>Cada paralelo representa una seccion (A, B, C…) dentro de un curso de la institucion.</p>
    </div>
    <div>
        <strong>Control de capacidad</strong>
        <p>Establezca el cupo maximo de estudiantes por paralelo para una gestion ordenada.</p>
    </div>
@endsection

@section('crumbs')
    <a href="{{ route('auth.form') }}">Menu principal</a>
    <span>/</span>
    <a href="{{ route('roles.dashboard', 'admin') }}">Dashboard admin</a>
    <span>/</span>
    <span>Administracion de paralelos</span>
@endsection

@section('content')
    <section class="hero" style="display:block;">
        <h2 style="margin:0 0 6px;font-size:22px;font-weight:700;line-height:1.2;">Administración de Paralelos</h2>
        @if ($institucionActiva)
            <p style="margin:0;color:rgba(255,255,255,.65);font-size:13px;">
                Gestiona los paralelos de <strong style="color:rgba(255,255,255,.9);">{{ $institucionActiva->nombre }}</strong>
            </p>
        @else
            <p style="margin:0;color:rgba(255,255,255,.65);font-size:13px;">Gestiona los paralelos de todas las instituciones disponibles.</p>
        @endif
    </section>

    <section class="panel">
        <div class="section-title">
            <h2>Listado operativo</h2>
            <span>{{ $totalParalelos }} resultados</span>
        </div>

        @if (session('status'))
            <div class="alert">{{ session('status') }}</div>
        @endif

        <div class="toolbar">
            <form class="filters" method="GET" action="{{ route('admin.paralelos.index') }}">
                <input class="input" type="text" name="search" placeholder="Buscar por letra, curso o nivel" value="{{ request('search') }}">
                @if (! $institucionActiva)
                    <select class="select" name="institucion">
                        <option value="">Todas las instituciones</option>
                        @foreach ($instituciones as $inst)
                            <option value="{{ $inst->id }}" @selected((string) request('institucion') === (string) $inst->id)>{{ $inst->nombre }}</option>
                        @endforeach
                    </select>
                @endif
                <select class="select" name="curso_id">
                    <option value="">Todos los cursos</option>
                    @foreach ($cursosDisponibles as $curso)
                        <option value="{{ $curso->id }}" @selected((string) request('curso_id') === (string) $curso->id)>{{ $curso->nombre }}</option>
                    @endforeach
                </select>
                <select class="select" name="estado">
                    <option value="">Todos los estados</option>
                    @foreach ($estados as $value => $label)
                        <option value="{{ $value }}" @selected(request('estado') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="btn" type="submit">Filtrar</button>
                <a class="btn" href="{{ route('admin.paralelos.index') }}">Limpiar</a>
            </form>

            <button class="btn btn-primary" type="button" data-open-create>Nuevo paralelo</button>
        </div>

        @if ($totalParalelos > 0)
            <table>
                <thead>
                    <tr>
                        <th>Paralelo</th>
                        @if (! $institucionActiva)
                            <th>Institucion</th>
                        @endif
                        <th>Capacidad</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($paralelosAgrupados as $especialidadNombre => $porCurso)
                        <tr>
                            <td colspan="{{ $institucionActiva ? 4 : 5 }}" style="background:var(--navy-50,#eef1fb);padding:8px 14px;font-weight:700;font-size:12px;letter-spacing:.06em;text-transform:uppercase;color:var(--navy-700,#152b74);border-bottom:2px solid var(--navy-100,#ccd4f1);">
                                {{ $especialidadNombre }}
                                <span style="font-weight:400;color:var(--muted,#64748b);margin-left:8px;">{{ $porCurso->flatten()->count() }} {{ $porCurso->flatten()->count() === 1 ? 'paralelo' : 'paralelos' }}</span>
                            </td>
                        </tr>
                        @foreach ($porCurso as $cursoNombre => $grupoParalelos)
                            <tr>
                                <td colspan="{{ $institucionActiva ? 4 : 5 }}" style="background:var(--base-100,#f8f9fb);padding:6px 14px 6px 28px;font-weight:600;font-size:12px;color:var(--base-content,#303237);border-bottom:1px solid var(--base-200,#e5e7eb);">
                                    {{ $cursoNombre }}
                                    @php $primerCurso = $grupoParalelos->first()?->curso; @endphp
                                    @if ($primerCurso)
                                        <span style="font-weight:400;color:var(--muted,#64748b);margin-left:6px;">Grado {{ $primerCurso->grado }}°</span>
                                    @endif
                                </td>
                            </tr>
                            @foreach ($grupoParalelos as $paralelo)
                                <tr>
                                    <td class="name-cell" style="padding-left:28px;">
                                        <strong>Paralelo {{ $paralelo->letra }}</strong>
                                    </td>
                                    @if (! $institucionActiva)
                                        <td>{{ $paralelo->curso?->institucion?->nombre ?: 'Sin institucion' }}</td>
                                    @endif
                                    <td>{{ $paralelo->capacidad }} alumnos</td>
                                    <td><span class="badge badge-{{ strtolower($paralelo->estado) }}">{{ $estados[$paralelo->estado] ?? $paralelo->estado }}</span></td>
                                    <td>
                                        <div class="actions">
                                            <a class="btn mini-btn" href="{{ route('admin.paralelos.index', array_filter(['edit' => $paralelo->id, 'search' => request('search'), 'institucion' => request('institucion'), 'curso_id' => request('curso_id'), 'estado' => request('estado')])) }}">Editar</a>
                                            <form method="POST" action="{{ route('admin.paralelos.destroy', $paralelo) }}" onsubmit="return confirm('Desea eliminar este paralelo?');">
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
            <div class="empty">No hay paralelos con los filtros actuales.</div>
        @endif
    </section>

    <div id="stats-resumen">
        <style>
            #stats-resumen .stats .card {
                min-height: 80px;
                padding-top: 10px;
                padding-bottom: 10px;
                padding-left: 11px;
                padding-right: 10px;
            }
            #stats-resumen .metric {
                font-size: 1.125rem;
                margin-bottom: 3px;
            }
        </style>
        <section class="stats">
            <article class="card"><strong class="metric">{{ $metricas['total'] }}</strong><span>Total paralelos</span></article>
            <article class="card"><strong class="metric">{{ $metricas['activos'] }}</strong><span>Activos</span></article>
            <article class="card"><strong class="metric">{{ $metricas['inactivos'] }}</strong><span>Inactivos</span></article>
            <article class="card"><strong class="metric">{{ $metricas['cursos'] }}</strong><span>Cursos con paralelos</span></article>
        </section>
    </div>
@endsection

@section('dialogs')
    <dialog class="modal" id="create-paralelo-modal">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <h3>Nuevo paralelo</h3>
                    <span>Defina una seccion dentro de un curso de la institucion.</span>
                </div>
                <button class="btn" type="button" data-close-create>x</button>
            </div>

            @if ($errors->any() && ! $paraleloEditando)
                <div class="errors">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.paralelos.store') }}">
                @csrf
                <div class="form-grid">
                    <div class="field-group full">
                        <label for="create_curso_id">Curso</label>
                        <select id="create_curso_id" class="select" name="curso_id" required>
                            <option value="">Seleccione un curso</option>
                            @foreach ($cursosDisponibles as $curso)
                                <option value="{{ $curso->id }}" @selected((string) old('curso_id') === (string) $curso->id)>
                                    {{ $curso->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field-group">
                        <label for="create_letra">Letra / Seccion</label>
                        <select id="create_letra" class="select" name="letra" required>
                            <option value="">Seleccione</option>
                            @foreach ($letras as $letra)
                                <option value="{{ $letra }}" @selected(old('letra') === $letra)>{{ $letra }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field-group">
                        <label for="create_capacidad">Capacidad (alumnos)</label>
                        <input id="create_capacidad" class="input" type="number" name="capacidad" value="{{ old('capacidad', 35) }}" min="1" max="100" required>
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

                <div class="field-note">El paralelo queda asociado al curso seleccionado. Cada curso admite una sola seccion por letra.</div>

                <div class="actions" style="margin-top: 18px;">
                    <button class="btn btn-primary" type="submit">Crear paralelo</button>
                    <button class="btn" type="button" data-close-create>Cancelar</button>
                </div>
            </form>
        </div>
    </dialog>

    @if ($paraleloEditando)
        <dialog class="modal" id="edit-paralelo-modal">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h3>Editar paralelo</h3>
                        <span>Actualice los datos del paralelo seleccionado.</span>
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

                <form method="POST" action="{{ route('admin.paralelos.update', $paraleloEditando) }}">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        <div class="field-group full">
                            <label for="edit_curso_id">Curso</label>
                            <select id="edit_curso_id" class="select" name="curso_id" required>
                                <option value="">Seleccione un curso</option>
                                @foreach ($cursosDisponibles as $curso)
                                    <option value="{{ $curso->id }}" @selected((string) old('curso_id', $paraleloEditando->curso_id) === (string) $curso->id)>
                                        {{ $curso->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group">
                            <label for="edit_letra">Letra / Seccion</label>
                            <select id="edit_letra" class="select" name="letra" required>
                                <option value="">Seleccione</option>
                                @foreach ($letras as $letra)
                                    <option value="{{ $letra }}" @selected(old('letra', $paraleloEditando->letra) === $letra)>{{ $letra }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group">
                            <label for="edit_capacidad">Capacidad (alumnos)</label>
                            <input id="edit_capacidad" class="input" type="number" name="capacidad" value="{{ old('capacidad', $paraleloEditando->capacidad) }}" min="1" max="100" required>
                        </div>
                        <div class="field-group">
                            <label for="edit_estado">Estado</label>
                            <select id="edit_estado" class="select" name="estado" required>
                                @foreach ($estados as $value => $label)
                                    <option value="{{ $value }}" @selected(old('estado', $paraleloEditando->estado) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="actions" style="margin-top: 18px;">
                        <button class="btn btn-primary" type="submit">Guardar cambios</button>
                        <a class="btn" href="{{ route('admin.paralelos.index') }}">Cancelar edicion</a>
                    </div>
                </form>
            </div>
        </dialog>
    @endif
@endsection

@section('scripts')
    <script>
        const createModal = document.getElementById('create-paralelo-modal');
        const editModal = document.getElementById('edit-paralelo-modal');

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

        @if ($paraleloEditando)
            editModal?.showModal();
        @endif

        @if ($errors->any() && ! $paraleloEditando)
            createModal?.showModal();
        @endif
    </script>
@endsection
