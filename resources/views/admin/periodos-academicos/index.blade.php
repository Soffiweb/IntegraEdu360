@extends('layouts.admin-shell')

@section('title', 'Periodos academicos')
@section('admin-nav', 'periodos')
@section('sidebar-panel-copy', 'Defina cronogramas, estados y ventanas lectivas desde la misma base visual del dashboard administrativo.')

@section('sidebar-panel-items')
    <div>
        <strong>Planeacion clara</strong>
        <p>Registre fechas de inicio y cierre para el calendario institucional activo.</p>
    </div>
    <div>
        <strong>Seguimiento rapido</strong>
        <p>Filtre por estado para identificar periodos activos, planificados o cerrados.</p>
    </div>
@endsection

@section('crumbs')
    <a href="{{ route('auth.form') }}">Menu principal</a>
    <span>/</span>
    <a href="{{ route('roles.dashboard', 'admin') }}">Dashboard admin</a>
    <span>/</span>
    <span>Periodos academicos</span>
@endsection

@section('content')
    <section class="hero">
        <div>
            <h2>Periodos Academicos</h2>
            <!-- <p>Cree, edite y cierre periodos academicos de la institucion activa sin salir de la base compartida del panel administrativo.</p> -->
        </div>
    </section>

    <section class="stats">
        <article class="card"><strong class="metric">{{ $metricas['total'] }}</strong><span>Total periodos</span></article>
        <article class="card"><strong class="metric">{{ $metricas['activos'] }}</strong><span>Activos</span></article>
        <article class="card"><strong class="metric">{{ $metricas['planificados'] }}</strong><span>Planificados</span></article>
        <article class="card"><strong class="metric">{{ $metricas['cerrados'] }}</strong><span>Cerrados</span></article>
    </section>

    <section class="panel">
        <div class="section-title">
            <h2>Listado operativo</h2>
            <span>{{ $periodos->total() }} resultados</span>
        </div>

        @if (session('status'))
            <div class="alert">{{ session('status') }}</div>
        @endif

        <div class="toolbar">
            <form class="filters" method="GET" action="{{ route('admin.periodos.index') }}">
                <input class="input" type="text" name="search" placeholder="Buscar por periodo{{ $institucionActiva ? '' : ' o institucion' }}" value="{{ request('search') }}">
                @if (! $institucionActiva)
                    <select class="select" name="institucion">
                        <option value="">Todas las instituciones</option>
                        @foreach ($instituciones as $institucion)
                            <option value="{{ $institucion->id }}" @selected((string) request('institucion') === (string) $institucion->id)>{{ $institucion->nombre }}</option>
                        @endforeach
                    </select>
                @endif
                <select class="select" name="estado">
                    <option value="">Todos los estados</option>
                    @foreach ($estados as $value => $label)
                        <option value="{{ $value }}" @selected(request('estado') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="btn" type="submit">Filtrar</button>
                <a class="btn" href="{{ route('admin.periodos.index') }}">Limpiar</a>
            </form>

            <button class="btn btn-primary" type="button" data-open-create>Nuevo periodo</button>
        </div>

        @if ($periodos->count())
            <table>
                <thead>
                    <tr>
                        <th>Periodo</th>
                        @if (! $institucionActiva)
                            <th>Institucion</th>
                        @endif
                        <th>Fechas</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($periodos as $periodo)
                        <tr>
                            <td class="name-cell">
                                <strong>{{ $periodo->nombre }}</strong>
                                <span>{{ $periodo->es_activo ? 'Periodo activo' : 'Periodo no activo' }}</span>
                            </td>
                            @if (! $institucionActiva)
                                <td>{{ $periodo->institucion?->nombre ?: 'Sin institucion' }}</td>
                            @endif
                            <td>
                                <strong>{{ optional($periodo->fecha_inicio)->format('Y-m-d') }}</strong>
                                <br>
                                <span>Hasta {{ optional($periodo->fecha_fin)->format('Y-m-d') }}</span>
                            </td>
                            <td><span class="badge badge-{{ strtolower($periodo->estado) }}">{{ $estados[$periodo->estado] ?? $periodo->estado }}</span></td>
                            <td>
                                <div class="actions">
                                    <a class="btn" href="{{ route('admin.periodos.index', array_filter(['edit' => $periodo->id, 'search' => request('search'), 'institucion' => request('institucion'), 'estado' => request('estado')])) }}">Editar</a>
                                    <form method="POST" action="{{ route('admin.periodos.destroy', $periodo) }}" onsubmit="return confirm('Desea eliminar este periodo academico?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger" type="submit">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pager">
                @if ($periodos->onFirstPage())
                    <span class="btn">Anterior</span>
                @else
                    <a class="btn" href="{{ $periodos->previousPageUrl() }}">Anterior</a>
                @endif

                <span class="btn">Pagina {{ $periodos->currentPage() }} de {{ $periodos->lastPage() }}</span>

                @if ($periodos->hasMorePages())
                    <a class="btn" href="{{ $periodos->nextPageUrl() }}">Siguiente</a>
                @else
                    <span class="btn">Siguiente</span>
                @endif
            </div>
        @else
            <div class="empty">No hay periodos academicos con los filtros actuales.</div>
        @endif
    </section>
@endsection

@section('dialogs')
    <dialog class="modal" id="create-periodo-modal">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <h3>Nuevo periodo academico</h3>
                    <span>Configure una ventana academica{{ $institucionActiva ? ' para la institucion activa.' : ' para una institucion.' }}</span>
                </div>
                <button class="btn" type="button" data-close-create>x</button>
            </div>

            @if ($errors->any() && ! $periodoEditando)
                <div class="errors">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.periodos.store') }}">
                @csrf
                <div class="form-grid">
                    @if ($institucionActiva)
                        <input type="hidden" name="institucion_id" value="{{ $institucionActiva->id }}">
                        <div class="field-group full">
                            <label>Institucion</label>
                            <input class="input" type="text" value="{{ $institucionActiva->nombre }}" disabled>
                        </div>
                    @else
                        <div class="field-group">
                            <label for="create_institucion_id">Institucion</label>
                            <select id="create_institucion_id" class="select" name="institucion_id" required>
                                <option value="">Seleccione</option>
                                @foreach ($instituciones as $institucion)
                                    <option value="{{ $institucion->id }}" @selected((string) old('institucion_id') === (string) $institucion->id)>{{ $institucion->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="field-group">
                        <label for="create_estado">Estado</label>
                        <select id="create_estado" class="select" name="estado" required>
                            @foreach ($estados as $value => $label)
                                <option value="{{ $value }}" @selected(old('estado', 'PLANIFICADO') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field-group full">
                        <label for="create_nombre">Nombre</label>
                        <input id="create_nombre" class="input" type="text" name="nombre" value="{{ old('nombre') }}" required>
                    </div>
                    <div class="field-group">
                        <label for="create_fecha_inicio">Fecha inicio</label>
                        <input id="create_fecha_inicio" class="input" type="date" name="fecha_inicio" value="{{ old('fecha_inicio') }}" required>
                    </div>
                    <div class="field-group">
                        <label for="create_fecha_fin">Fecha fin</label>
                        <input id="create_fecha_fin" class="input" type="date" name="fecha_fin" value="{{ old('fecha_fin') }}" required>
                    </div>
                </div>

                <div class="actions" style="margin-top: 18px;">
                    <button class="btn btn-primary" type="submit">Crear periodo</button>
                    <button class="btn" type="button" data-close-create>Cancelar</button>
                </div>
            </form>
        </div>
    </dialog>

    @if ($periodoEditando)
        <dialog class="modal" id="edit-periodo-modal">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h3>Editar periodo academico</h3>
                        <span>Actualice fechas,{{ $institucionActiva ? '' : ' institucion y' }} estado del periodo.</span>
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

                <form method="POST" action="{{ route('admin.periodos.update', $periodoEditando) }}">
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
                            <div class="field-group">
                                <label for="edit_institucion_id">Institucion</label>
                                <select id="edit_institucion_id" class="select" name="institucion_id" required>
                                    <option value="">Seleccione</option>
                                    @foreach ($instituciones as $institucion)
                                        <option value="{{ $institucion->id }}" @selected((string) old('institucion_id', $periodoEditando->institucion_id) === (string) $institucion->id)>{{ $institucion->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="field-group">
                            <label for="edit_estado">Estado</label>
                            <select id="edit_estado" class="select" name="estado" required>
                                @foreach ($estados as $value => $label)
                                    <option value="{{ $value }}" @selected(old('estado', $periodoEditando->estado) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group full">
                            <label for="edit_nombre">Nombre</label>
                            <input id="edit_nombre" class="input" type="text" name="nombre" value="{{ old('nombre', $periodoEditando->nombre) }}" required>
                        </div>
                        <div class="field-group">
                            <label for="edit_fecha_inicio">Fecha inicio</label>
                            <input id="edit_fecha_inicio" class="input" type="date" name="fecha_inicio" value="{{ old('fecha_inicio', optional($periodoEditando->fecha_inicio)->format('Y-m-d')) }}" required>
                        </div>
                        <div class="field-group">
                            <label for="edit_fecha_fin">Fecha fin</label>
                            <input id="edit_fecha_fin" class="input" type="date" name="fecha_fin" value="{{ old('fecha_fin', optional($periodoEditando->fecha_fin)->format('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div class="actions" style="margin-top: 18px;">
                        <button class="btn btn-primary" type="submit">Guardar cambios</button>
                        <a class="btn" href="{{ route('admin.periodos.index') }}">Cancelar edicion</a>
                    </div>
                </form>
            </div>
        </dialog>
    @endif
@endsection

@section('scripts')
    <script>
        const createModal = document.getElementById('create-periodo-modal');
        const editModal = document.getElementById('edit-periodo-modal');

        document.querySelectorAll('[data-open-create]').forEach((button) => {
            button.addEventListener('click', () => createModal?.showModal());
        });

        document.querySelectorAll('[data-close-create]').forEach((button) => {
            button.addEventListener('click', () => createModal?.close());
        });

        document.querySelectorAll('[data-close-edit]').forEach((button) => {
            button.addEventListener('click', () => editModal?.close());
        });

        createModal?.addEventListener('click', (event) => {
            const rect = createModal.getBoundingClientRect();
            const inside = rect.top <= event.clientY && event.clientY <= rect.top + rect.height && rect.left <= event.clientX && event.clientX <= rect.left + rect.width;
            if (!inside) {
                createModal.close();
            }
        });

        editModal?.addEventListener('click', (event) => {
            const rect = editModal.getBoundingClientRect();
            const inside = rect.top <= event.clientY && event.clientY <= rect.top + rect.height && rect.left <= event.clientX && event.clientX <= rect.left + rect.width;
            if (!inside) {
                editModal.close();
            }
        });

        @if ($periodoEditando)
            editModal?.showModal();
        @endif

        @if ($errors->any() && ! $periodoEditando)
            createModal?.showModal();
        @endif
    </script>
@endsection
