@extends('layouts.admin-shell')

@section('title', 'Administracion de usuarios')
@section('admin-nav', 'usuarios')
@section('sidebar-panel-copy', 'Mantenga el control de cuentas, roles y estados desde la misma base del dashboard administrativo.')

@section('sidebar-panel-items')
    <div>
        <strong>Revision diaria</strong>
        <p>Filtre por estado para detectar usuarios inactivos o pendientes de seguimiento del plantel actual.</p>
    </div>
    <div>
        <strong>Alta ordenada</strong>
        <p>Cree nuevas cuentas desde el modal sin salir del panel operativo de su institucion.</p>
    </div>
@endsection

@section('crumbs')
    <a href="{{ route('auth.form') }}">Menu principal</a>
    <span>/</span>
    <a href="{{ route('roles.dashboard', 'admin') }}">Dashboard admin</a>
    <span>/</span>
    <span>Administracion de usuarios</span>
@endsection

@section('content')
    @php
        $personaOldCreate = (string) old('persona_id');
        $personaOldEdit = $usuarioEditando ? (string) old('persona_id', $usuarioEditando->persona_id) : '';
    @endphp

    <section class="hero" style="grid-template-columns: 1fr; display: block;">
        <h2 style="margin: 0 0 6px; font-size: 22px; font-weight: 700; line-height: 1.2;">Gestion profesional de usuarios</h2>
        @if ($institucionActiva)
            <p style="margin: 0; color: rgba(255,255,255,0.65); font-size: 13px;">
                Administra los usuarios registrados en <strong style="color: rgba(255,255,255,0.9);">{{ $institucionActiva->nombre }}</strong>
            </p>
        @else
            <p style="margin: 0; color: rgba(255,255,255,0.65); font-size: 13px;">Administra usuarios de todas las instituciones disponibles.</p>
        @endif
    </section>

    <section class="stats">
        <article class="card"><strong class="metric">{{ $metricas['total'] }}</strong><span>Registros en usuarios</span></article>
        <article class="card"><strong class="metric">{{ $metricas['activos'] }}</strong><span>Usuarios activos</span></article>
        <article class="card"><strong class="metric">{{ $metricas['observacion'] }}</strong><span>En observacion</span></article>
        <article class="card"><strong class="metric">{{ $metricas['inactivos'] }}</strong><span>Usuarios inactivos</span></article>
    </section>

    <section class="panel">
        <div class="section-title">
            <h2>Listado operativo</h2>
            <span>{{ $usuarios->total() }} resultados</span>
        </div>

        @if (session('status'))
            <div class="alert">{{ session('status') }}</div>
        @endif

        <div class="toolbar">
            <form class="filters" method="GET" action="{{ route('admin.usuarios.index') }}">
                <input class="input" type="text" name="search" placeholder="Buscar por nombre, usuario, correo o telefono" value="{{ request('search') }}">
                @if (! $institucionActiva)
                    <select class="select" name="institucion">
                        <option value="">Todas las instituciones</option>
                        @foreach ($instituciones as $institucion)
                            <option value="{{ $institucion->id }}" @selected((string) request('institucion') === (string) $institucion->id)>{{ $institucion->nombre }}</option>
                        @endforeach
                    </select>
                @endif
                <select class="select" name="rol">
                    <option value="">Todos los roles</option>
                    @foreach ($roles as $rol)
                        <option value="{{ $rol->id }}" @selected((string) request('rol') === (string) $rol->id)>{{ $rol->nombre }}</option>
                    @endforeach
                </select>
                <select class="select" name="estado">
                    <option value="">Todos los estados</option>
                    @foreach ($estados as $value => $label)
                        <option value="{{ $value }}" @selected(request('estado') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="btn" type="submit">Filtrar</button>
                <a class="btn" href="{{ route('admin.usuarios.index') }}">Limpiar</a>
            </form>

            <button class="btn btn-primary" type="button" data-open-create>Nuevo usuario</button>
        </div>

        @if ($usuarios->count())
            <table>
                <thead>
                    <tr>
                        <th>Usuario</th>
                        @if (! $institucionActiva)
                            <th>Institucion</th>
                        @endif
                        <th>Roles</th>
                        <th>Estado</th>
                        <th>Ultimo acceso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($usuarios as $usuario)
                        @php
                            $persona = $usuario->persona;
                            $nombre = trim(implode(' ', array_filter([$persona?->primer_nombre, $persona?->segundo_nombre, $persona?->primer_apellido, $persona?->segundo_apellido])));
                        @endphp
                        <tr>
                            <td class="name-cell">
                                <strong>{{ $nombre !== '' ? $nombre : $usuario->username }}</strong>
                                <span>{{ $usuario->username }} | {{ $usuario->email ?: ($persona?->email ?: 'Sin correo') }}</span>
                            </td>
                            @if (! $institucionActiva)
                                <td>{{ $usuario->institucion?->nombre ?: 'Sin institucion' }}</td>
                            @endif
                            <td>{{ $usuario->roles->pluck('nombre')->join(', ') ?: 'Sin roles' }}</td>
                            <td><span class="badge badge-{{ strtolower($usuario->estado) }}">{{ $estados[$usuario->estado] ?? $usuario->estado }}</span></td>
                            <td>{{ $usuario->ultimo_acceso?->format('Y-m-d H:i') ?: 'Sin registro' }}</td>
                            <td>
                                <div class="actions">
                                    <a class="btn mini-btn" href="{{ route('admin.usuarios.index', array_filter(['edit' => $usuario->id, 'search' => request('search'), 'institucion' => request('institucion'), 'rol' => request('rol'), 'estado' => request('estado')])) }}">Editar</a>
                                    <form method="POST" action="{{ route('admin.usuarios.destroy', $usuario) }}" onsubmit="return confirm('Desea eliminar este usuario de la tabla usuarios?');">
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
                @if ($usuarios->onFirstPage())
                    <span class="btn">Anterior</span>
                @else
                    <a class="btn" href="{{ $usuarios->previousPageUrl() }}">Anterior</a>
                @endif

                <span class="btn">Pagina {{ $usuarios->currentPage() }} de {{ $usuarios->lastPage() }}</span>

                @if ($usuarios->hasMorePages())
                    <a class="btn" href="{{ $usuarios->nextPageUrl() }}">Siguiente</a>
                @else
                    <span class="btn">Siguiente</span>
                @endif
            </div>
        @else
            <div class="empty">No hay usuarios con los filtros actuales.</div>
        @endif
    </section>
@endsection

@section('dialogs')
    @if ($usuarioEditando)
        <dialog class="modal" id="edit-user-modal">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h3>Editar usuario</h3>
                        <span>Actualice datos, roles y estado del usuario seleccionado.</span>
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

                @php
                    $rolesSeleccionados = collect(old('rol_ids', $usuarioEditando->roles->pluck('id')->all()))
                        ->map(fn ($id) => (string) $id)
                        ->all();
                @endphp

                <form method="POST" action="{{ route('admin.usuarios.update', $usuarioEditando) }}">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        <div class="field-group full">
                            <label for="edit_persona_id">Persona vinculada</label>
                            <input
                                id="edit_persona_search"
                                class="input persona-search"
                                type="search"
                                placeholder="Buscar por apellido, nombre, identificacion o correo"
                                data-persona-search-for="edit_persona_id"
                                style="margin-bottom: 10px;"
                            >
                            <select id="edit_persona_id" class="select" name="persona_id">
                                <option value="">Sin vincular</option>
                                @foreach ($personas as $persona)
                                    <option
                                        value="{{ $persona->id }}"
                                        data-search="{{ $persona->nombre_busqueda }}"
                                        data-instituciones="{{ implode(',', $persona->institucion_ids) }}"
                                        @selected((string) old('persona_id', $usuarioEditando->persona_id) === (string) $persona->id)
                                    >{{ $persona->nombre_mostrar }}</option>
                                @endforeach
                            </select>
                            <small class="persona-search-feedback" data-persona-feedback-for="edit_persona_id">Escriba para filtrar personas vinculadas a la institucion.</small>
                        </div>
                        <div class="field-group full">
                            <label for="edit_institucion_id">Institucion</label>
                            @if ($institucionActiva)
                                <input type="hidden" name="institucion_id" value="{{ $institucionActiva->id }}">
                                <input class="input" type="text" value="{{ $institucionActiva->nombre }}" disabled>
                            @else
                                <select id="edit_institucion_id" class="select" name="institucion_id" required>
                                    <option value="">Seleccione una institucion</option>
                                    @foreach ($instituciones as $institucion)
                                        <option value="{{ $institucion->id }}" @selected((string) old('institucion_id', $usuarioEditando->institucion_id) === (string) $institucion->id)>{{ $institucion->nombre }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="field-group">
                            <label for="edit_username">Username</label>
                            <input id="edit_username" class="input" type="text" name="username" value="{{ old('username', $usuarioEditando->username) }}" required>
                        </div>
                        <div class="field-group">
                            <label for="edit_email">Correo</label>
                            <input id="edit_email" class="input" type="email" name="email" value="{{ old('email', $usuarioEditando->email) }}">
                        </div>
                        <div class="field-group">
                            <label for="edit_rol_ids">Roles</label>
                            <select id="edit_rol_ids" class="select" name="rol_ids[]" multiple size="4" required>
                                @foreach ($roles as $rol)
                                    <option value="{{ $rol->id }}" @selected(in_array((string) $rol->id, $rolesSeleccionados, true))>{{ $rol->nombre }}</option>
                                @endforeach
                            </select>
                            <small class="role-feedback" data-role-feedback-for="edit_rol_ids">Use Ctrl o Ctrl+Click para seleccionar varios roles.</small>
                        </div>
                        <div class="field-group">
                            <label for="edit_estado">Estado</label>
                            <select id="edit_estado" class="select" name="estado" required>
                                @foreach ($estados as $value => $label)
                                    <option value="{{ $value }}" @selected(old('estado', $usuarioEditando->estado) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group">
                            <label for="edit_ultimo_acceso">Ultimo acceso</label>
                            <input id="edit_ultimo_acceso" class="input" type="datetime-local" name="ultimo_acceso" value="{{ old('ultimo_acceso', $usuarioEditando->ultimo_acceso?->format('Y-m-d\\TH:i')) }}">
                        </div>
                        <div class="field-group">
                            <label for="edit_password_hash">Password hash (opcional)</label>
                            <input id="edit_password_hash" class="input" type="text" name="password_hash" value="{{ old('password_hash') }}">
                        </div>
                    </div>

                    <div class="actions" style="margin-top: 18px;">
                        <button class="btn btn-primary" type="submit">Guardar cambios</button>
                        <a class="btn" href="{{ route('admin.usuarios.index') }}">Cancelar edicion</a>
                    </div>
                </form>
            </div>
        </dialog>
    @endif

    <dialog class="modal" id="create-user-modal">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <h3>Nuevo usuario</h3>
                    <span>Cree el registro directamente en la tabla usuarios.</span>
                </div>
                <button class="btn" type="button" data-close-create>x</button>
            </div>

            @if ($errors->any() && ! $usuarioEditando)
                <div class="errors">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.usuarios.store') }}">
                @csrf

                    <div class="form-grid">
                    <div class="field-group full">
                        <label for="create_persona_id">Persona vinculada</label>
                        <input
                            id="create_persona_search"
                            class="input persona-search"
                            type="search"
                            placeholder="Buscar por apellido, nombre, identificacion o correo"
                            data-persona-search-for="create_persona_id"
                            style="margin-bottom: 10px;"
                        >
                        <select id="create_persona_id" class="select" name="persona_id">
                            <option value="">Sin vincular</option>
                            @foreach ($personas as $persona)
                                <option
                                    value="{{ $persona->id }}"
                                    data-search="{{ $persona->nombre_busqueda }}"
                                    data-instituciones="{{ implode(',', $persona->institucion_ids) }}"
                                    @selected((string) old('persona_id') === (string) $persona->id)
                                >{{ $persona->nombre_mostrar }}</option>
                            @endforeach
                        </select>
                        <small class="persona-search-feedback" data-persona-feedback-for="create_persona_id">Escriba para filtrar personas vinculadas a la institucion.</small>
                    </div>
                    <div class="field-group full">
                        <label for="create_institucion_id">Institucion</label>
                        @if ($institucionActiva)
                            <input type="hidden" name="institucion_id" value="{{ $institucionActiva->id }}">
                            <input class="input" type="text" value="{{ $institucionActiva->nombre }}" disabled>
                        @else
                            <select id="create_institucion_id" class="select" name="institucion_id" required>
                                <option value="">Seleccione una institucion</option>
                                @foreach ($instituciones as $institucion)
                                    <option value="{{ $institucion->id }}" @selected((string) old('institucion_id') === (string) $institucion->id)>{{ $institucion->nombre }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    <div class="field-group">
                        <label for="create_username">Username</label>
                        <input id="create_username" class="input" type="text" name="username" value="{{ old('username') }}" required>
                    </div>
                    <div class="field-group">
                        <label for="create_email">Correo</label>
                        <input id="create_email" class="input" type="email" name="email" value="{{ old('email') }}">
                    </div>
                    <div class="field-group">
                        <label for="create_rol_ids">Roles</label>
                        <select id="create_rol_ids" class="select" name="rol_ids[]" multiple size="4" required>
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->id }}" @selected(in_array((string) $rol->id, collect(old('rol_ids', []))->map(fn ($id) => (string) $id)->all(), true))>{{ $rol->nombre }}</option>
                            @endforeach
                        </select>
                        <small class="role-feedback" data-role-feedback-for="create_rol_ids">Use Ctrl o Ctrl+Click para seleccionar varios roles.</small>
                    </div>
                    <div class="field-group">
                        <label for="create_estado">Estado</label>
                        <select id="create_estado" class="select" name="estado" required>
                            @foreach ($estados as $value => $label)
                                <option value="{{ $value }}" @selected(old('estado', 'ACTIVO') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field-group">
                        <label for="create_ultimo_acceso">Ultimo acceso</label>
                        <input id="create_ultimo_acceso" class="input" type="datetime-local" name="ultimo_acceso" value="{{ old('ultimo_acceso') }}">
                    </div>
                    <div class="field-group">
                        <label for="create_password_hash">Password hash</label>
                        <input id="create_password_hash" class="input" type="text" name="password_hash" value="{{ old('password_hash') }}" required>
                    </div>
                </div>

                <div class="actions" style="margin-top: 18px;">
                    <button class="btn btn-primary" type="submit">Crear usuario</button>
                    <button class="btn" type="button" data-close-create>Cancelar</button>
                </div>
            </form>
        </div>
    </dialog>
@endsection

@section('scripts')
    <script type="application/json" id="__ie_cfg">{"fixedInstitucionId":@json($institucionActiva ? (string) $institucionActiva->id : null),"rolesPorInstitucion":@json($rolesPorInstitucion),"personaOldCreate":@json($personaOldCreate !== ''),"personaOldEdit":@json($personaOldEdit !== ''),"autoOpenEdit":@json((bool) $usuarioEditando),"autoOpenCreate":@json($errors->any() && ! $usuarioEditando)}</script>
    <script>
        const __cfg = JSON.parse(document.getElementById('__ie_cfg').textContent);
        const createModal = document.getElementById('create-user-modal');
        const editModal = document.getElementById('edit-user-modal');
        const normalizeSearch = (value) =>
            (value || '')
                .toString()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .trim();

        const initPersonaSearch = (config) => {
            const searchInput = document.getElementById(config.searchId);
            const select = document.getElementById(config.selectId);
            const institutionSelect = document.getElementById(config.institutionSelectId);
            const feedback = document.querySelector(`[data-persona-feedback-for="${config.selectId}"]`);

            if (!searchInput || !select) {
                return;
            }

            const baseOptions = Array.from(select.options).map((option) => ({
                value: option.value,
                label: option.textContent,
                selected: option.selected,
                search: normalizeSearch(option.dataset.search || option.textContent),
                instituciones: (option.dataset.instituciones || '')
                    .split(',')
                    .map((id) => id.trim())
                    .filter(Boolean),
                isEmpty: option.value === '',
            }));

            const renderOptions = () => {
                const institutionId = institutionSelect?.value?.trim() || config.fixedInstitutionId || '';
                const term = normalizeSearch(searchInput.value);
                const previousValue = select.value;

                select.innerHTML = '';

                let visibleCount = 0;

                baseOptions.forEach((optionData) => {
                    const matchesInstitution = optionData.isEmpty
                        || (
                            institutionId !== ''
                            && (
                                optionData.instituciones.includes(institutionId)
                                || (config.preserveSelected && optionData.value === previousValue)
                            )
                        );
                    const matchesTerm = optionData.isEmpty || term === '' || optionData.search.includes(term);

                    if (!matchesInstitution || !matchesTerm) {
                        return;
                    }

                    const option = document.createElement('option');
                    option.value = optionData.value;
                    option.textContent = optionData.label;
                    option.selected = optionData.value === previousValue;
                    select.appendChild(option);

                    if (!optionData.isEmpty) {
                        visibleCount++;
                    }
                });

                if (!Array.from(select.options).some((option) => option.value === previousValue) && select.options.length > 0) {
                    select.value = '';
                }

                if (feedback) {
                    if (institutionId === '' && !config.fixedInstitutionId) {
                        feedback.textContent = 'Seleccione una institucion para acotar la lista de personas.';
                    } else if (visibleCount === 0) {
                        feedback.textContent = 'No hay personas vinculadas con la institucion y el criterio de busqueda.';
                    } else {
                        feedback.textContent = `${visibleCount} persona(s) disponible(s), ordenadas por apellido y nombre.`;
                    }
                }
            };

            searchInput.addEventListener('input', renderOptions);
            institutionSelect?.addEventListener('change', renderOptions);
            renderOptions();
        };

        const initRoleFilter = (config) => {
            const select = document.getElementById(config.selectId);
            const institutionSelect = document.getElementById(config.institutionSelectId);
            const feedback = document.querySelector(`[data-role-feedback-for="${config.selectId}"]`);
            const roleMap = config.roleMap || {};

            if (!select) {
                return;
            }

            const baseOptions = Array.from(select.options).map((option) => ({
                value: option.value,
                label: option.textContent,
                selected: option.selected,
            }));

            const renderRoles = () => {
                const institutionId = institutionSelect?.value?.trim() || config.fixedInstitutionId || '';
                const allowedRoleIds = institutionId !== '' ? (roleMap[institutionId] || []) : [];
                const previousValues = Array.from(select.selectedOptions).map((option) => option.value);

                select.innerHTML = '';

                baseOptions.forEach((optionData) => {
                    if (institutionId === '' || !allowedRoleIds.includes(Number(optionData.value))) {
                        return;
                    }

                    const option = document.createElement('option');
                    option.value = optionData.value;
                    option.textContent = optionData.label;
                    option.selected = previousValues.includes(optionData.value);
                    select.appendChild(option);
                });

                if (feedback) {
                    if (institutionId === '' && !config.fixedInstitutionId) {
                        feedback.textContent = 'Seleccione una institucion para ver los roles habilitados.';
                    } else if (select.options.length === 0) {
                        feedback.textContent = 'La institucion seleccionada no tiene roles habilitados para asignar usuarios.';
                    } else {
                        feedback.textContent = 'Use Ctrl o Ctrl+Click para seleccionar varios roles.';
                    }
                }
            };

            institutionSelect?.addEventListener('change', renderRoles);
            renderRoles();
        };

        document.querySelectorAll('[data-open-create]').forEach((button) => {
            button.addEventListener('click', () => createModal?.showModal());
        });

        document.querySelectorAll('[data-close-create]').forEach((button) => {
            button.addEventListener('click', () => createModal?.close());
        });

        document.querySelectorAll('[data-close-edit]').forEach((button) => {
            button.addEventListener('click', () => editModal?.close());
        });

        initPersonaSearch({
            searchId: 'create_persona_search',
            selectId: 'create_persona_id',
            institutionSelectId: 'create_institucion_id',
            fixedInstitutionId: __cfg.fixedInstitucionId,
            preserveSelected: false,
        });

        initPersonaSearch({
            searchId: 'edit_persona_search',
            selectId: 'edit_persona_id',
            institutionSelectId: 'edit_institucion_id',
            fixedInstitutionId: __cfg.fixedInstitucionId,
            preserveSelected: true,
        });

        initRoleFilter({
            selectId: 'create_rol_ids',
            institutionSelectId: 'create_institucion_id',
            fixedInstitutionId: __cfg.fixedInstitucionId,
            roleMap: __cfg.rolesPorInstitucion,
        });

        initRoleFilter({
            selectId: 'edit_rol_ids',
            institutionSelectId: 'edit_institucion_id',
            fixedInstitutionId: __cfg.fixedInstitucionId,
            roleMap: __cfg.rolesPorInstitucion,
        });

        const createPersonaSearch = document.getElementById('create_persona_search');
        if (createPersonaSearch && __cfg.personaOldCreate) {
            createPersonaSearch.value = '';
        }

        const editPersonaSearch = document.getElementById('edit_persona_search');
        if (editPersonaSearch && __cfg.personaOldEdit) {
            editPersonaSearch.value = '';
        }

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

        if (__cfg.autoOpenEdit) {
            editModal?.showModal();
        }

        if (__cfg.autoOpenCreate) {
            createModal?.showModal();
        }
    </script>
@endsection
