@extends('layouts.admin-shell')

@section('title', 'Administracion de especialidades')
@section('admin-nav', 'especialidades')
@section('sidebar-panel-copy', 'Administre la oferta de especialidades academicas de la institucion activa.')

@section('sidebar-panel-items')
    <div>
        <strong>Oferta academica</strong>
        <p>Defina las especialidades que ofrece la institucion dentro del bachillerato.</p>
    </div>
    <div>
        <strong>Clasificacion por tipo</strong>
        <p>Diferencie entre BGU, Tecnico y Tecnico Productivo para una gestion ordenada.</p>
    </div>
@endsection

@section('crumbs')
    <a href="{{ route('auth.form') }}">Menu principal</a>
    <span>/</span>
    <a href="{{ route('roles.dashboard', 'admin') }}">Dashboard admin</a>
    <span>/</span>
    <span>Administracion de especialidades</span>
@endsection

@section('content')
    <section class="hero" style="display:block;">
        <h2 style="margin:0 0 6px;font-size:22px;font-weight:700;line-height:1.2;">Administración de Especialidades</h2>
        @if ($institucionActiva)
            <p style="margin:0;color:rgba(255,255,255,.65);font-size:13px;">
                Gestiona las especialidades de <strong style="color:rgba(255,255,255,.9);">{{ $institucionActiva->nombre }}</strong>
            </p>
        @else
            <p style="margin:0;color:rgba(255,255,255,.65);font-size:13px;">Gestiona las especialidades de todas las instituciones disponibles.</p>
        @endif
    </section>

    <section class="stats">
        <article class="card"><strong class="metric">{{ $metricas['total'] }}</strong><span>Total especialidades</span></article>
        <article class="card"><strong class="metric">{{ $metricas['activos'] }}</strong><span>Activas</span></article>
        <article class="card"><strong class="metric">{{ $metricas['inactivos'] }}</strong><span>Inactivas</span></article>
        <article class="card"><strong class="metric">{{ $metricas['tipos'] }}</strong><span>Tipos distintos</span></article>
    </section>

    <section class="panel">
        <div class="section-title">
            <h2>Listado operativo</h2>
            <span>{{ $especialidades->total() }} resultados</span>
        </div>

        @if (session('status'))
            <div class="alert">{{ session('status') }}</div>
        @endif

        <div class="toolbar">
            <form class="filters" method="GET" action="{{ route('admin.especialidades.index') }}">
                <input class="input" type="text" name="search" placeholder="Buscar por nombre, codigo o tipo" value="{{ request('search') }}">
                @if (! $institucionActiva)
                    <select class="select" name="institucion">
                        <option value="">Todas las instituciones</option>
                        @foreach ($instituciones as $inst)
                            <option value="{{ $inst->id }}" @selected((string) request('institucion') === (string) $inst->id)>{{ $inst->nombre }}</option>
                        @endforeach
                    </select>
                @endif
                <select class="select" name="tipo">
                    <option value="">Todos los tipos</option>
                    @foreach ($tipos as $value => $label)
                        <option value="{{ $value }}" @selected(request('tipo') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select class="select" name="estado">
                    <option value="">Todos los estados</option>
                    @foreach ($estados as $value => $label)
                        <option value="{{ $value }}" @selected(request('estado') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="btn" type="submit">Filtrar</button>
                <a class="btn" href="{{ route('admin.especialidades.index') }}">Limpiar</a>
            </form>

            <button class="btn btn-primary" type="button" data-open-create>Nueva especialidad</button>
        </div>

        @if ($especialidades->count())
            <table>
                <thead>
                    <tr>
                        <th>Especialidad</th>
                        @if (! $institucionActiva)
                            <th>Institucion</th>
                        @endif
                        <th>Tipo</th>
                        <th>Codigo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($especialidades as $especialidad)
                        <tr>
                            <td class="name-cell">
                                <strong>{{ $especialidad->nombre }}</strong>
                            </td>
                            @if (! $institucionActiva)
                                <td>{{ $especialidad->institucion?->nombre ?: 'Sin institucion' }}</td>
                            @endif
                            <td>{{ $tipos[$especialidad->tipo] ?? $especialidad->tipo }}</td>
                            <td>{{ $especialidad->codigo ?: '—' }}</td>
                            <td><span class="badge badge-{{ strtolower($especialidad->estado) }}">{{ $estados[$especialidad->estado] ?? $especialidad->estado }}</span></td>
                            <td>
                                <div class="actions">
                                    <a class="btn mini-btn" href="{{ route('admin.especialidades.index', array_filter(['edit' => $especialidad->id, 'search' => request('search'), 'institucion' => request('institucion'), 'tipo' => request('tipo'), 'estado' => request('estado')])) }}">Editar</a>
                                    <form method="POST" action="{{ route('admin.especialidades.destroy', $especialidad) }}" onsubmit="return confirm('Desea eliminar esta especialidad?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger mini-btn" type="submit">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pager">
                @if ($especialidades->onFirstPage())
                    <span class="btn">Anterior</span>
                @else
                    <a class="btn" href="{{ $especialidades->previousPageUrl() }}">Anterior</a>
                @endif

                <span class="btn">Pagina {{ $especialidades->currentPage() }} de {{ $especialidades->lastPage() }}</span>

                @if ($especialidades->hasMorePages())
                    <a class="btn" href="{{ $especialidades->nextPageUrl() }}">Siguiente</a>
                @else
                    <span class="btn">Siguiente</span>
                @endif
            </div>
        @else
            <div class="empty">No hay especialidades con los filtros actuales.</div>
        @endif
    </section>
@endsection

@section('dialogs')
    <dialog class="modal" id="create-especialidad-modal">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <h3>Nueva especialidad</h3>
                    <span>Defina una especialidad dentro de la oferta academica institucional.</span>
                </div>
                <button class="btn" type="button" data-close-create>x</button>
            </div>

            @if ($errors->any() && ! $especialidadEditando)
                <div class="errors">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.especialidades.store') }}">
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
                                @foreach ($instituciones as $inst)
                                    <option value="{{ $inst->id }}" @selected((string) old('institucion_id') === (string) $inst->id)>{{ $inst->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="field-group full">
                        <label for="create_nombre">Nombre de la especialidad</label>
                        <input id="create_nombre" class="input" type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Ej: Bachillerato en Ciencias" required>
                    </div>
                    <div class="field-group">
                        <label for="create_tipo">Tipo</label>
                        <select id="create_tipo" class="select" name="tipo" required>
                            <option value="">Seleccione</option>
                            @foreach ($tipos as $value => $label)
                                <option value="{{ $value }}" @selected(old('tipo', 'GENERAL') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field-group">
                        <label for="create_codigo">Codigo (opcional)</label>
                        <input id="create_codigo" class="input" type="text" name="codigo" value="{{ old('codigo') }}" placeholder="Ej: BGU, BTI">
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
                    <button class="btn btn-primary" type="submit">Crear especialidad</button>
                    <button class="btn" type="button" data-close-create>Cancelar</button>
                </div>
            </form>
        </div>
    </dialog>

    @if ($especialidadEditando)
        <dialog class="modal" id="edit-especialidad-modal">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h3>Editar especialidad</h3>
                        <span>Actualice los datos de la especialidad seleccionada.</span>
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

                <form method="POST" action="{{ route('admin.especialidades.update', $especialidadEditando) }}">
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
                                    @foreach ($instituciones as $inst)
                                        <option value="{{ $inst->id }}" @selected((string) old('institucion_id', $especialidadEditando->institucion_id) === (string) $inst->id)>{{ $inst->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="field-group full">
                            <label for="edit_nombre">Nombre de la especialidad</label>
                            <input id="edit_nombre" class="input" type="text" name="nombre" value="{{ old('nombre', $especialidadEditando->nombre) }}" required>
                        </div>
                        <div class="field-group">
                            <label for="edit_tipo">Tipo</label>
                            <select id="edit_tipo" class="select" name="tipo" required>
                                <option value="">Seleccione</option>
                                @foreach ($tipos as $value => $label)
                                    <option value="{{ $value }}" @selected(old('tipo', $especialidadEditando->tipo) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group">
                            <label for="edit_codigo">Codigo (opcional)</label>
                            <input id="edit_codigo" class="input" type="text" name="codigo" value="{{ old('codigo', $especialidadEditando->codigo) }}" placeholder="Ej: BGU, BTI">
                        </div>
                        <div class="field-group">
                            <label for="edit_estado">Estado</label>
                            <select id="edit_estado" class="select" name="estado" required>
                                @foreach ($estados as $value => $label)
                                    <option value="{{ $value }}" @selected(old('estado', $especialidadEditando->estado) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="actions" style="margin-top: 18px;">
                        <button class="btn btn-primary" type="submit">Guardar cambios</button>
                        <a class="btn" href="{{ route('admin.especialidades.index') }}">Cancelar edicion</a>
                    </div>
                </form>
            </div>
        </dialog>
    @endif
@endsection

@section('scripts')
    <script>
        const createModal = document.getElementById('create-especialidad-modal');
        const editModal = document.getElementById('edit-especialidad-modal');

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

        @if ($especialidadEditando)
            editModal?.showModal();
        @endif

        @if ($errors->any() && ! $especialidadEditando)
            createModal?.showModal();
        @endif
    </script>
@endsection
