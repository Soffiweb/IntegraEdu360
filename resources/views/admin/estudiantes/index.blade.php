@extends('layouts.admin-shell')

@php
    $personaEditando = $estudianteEditando?->persona;
@endphp

@section('title', 'Administracion de alumnos')
@section('admin-nav', 'estudiantes')
@section('sidebar-panel-copy', 'Gestione el alumnado desde la misma base compartida del dashboard administrativo.')

@section('sidebar-panel-items')
    <div>
        <strong>Alta consistente</strong>
        <p>El sistema asigna automaticamente el rol estudiante al crear un nuevo registro.</p>
    </div>
    <div>
        <strong>Seguimiento de acceso</strong>
        <p>Revise estado y ultimo acceso para detectar cuentas que requieren actualizacion.</p>
    </div>
@endsection

@section('crumbs')
    <a href="{{ route('auth.form') }}">Menu principal</a>
    <span>/</span>
    <a href="{{ route('roles.dashboard', 'admin') }}">Dashboard admin</a>
    <span>/</span>
    <span>Administracion de alumnos</span>
@endsection

@section('content')
    <section class="hero" style="display: block;">
        <h2 style="margin: 0 0 6px; font-size: 22px; font-weight: 700; line-height: 1.2;">Administración de Alumnos</h2>
        @if ($institucionActiva)
            <p style="margin: 0; color: rgba(255,255,255,0.65); font-size: 13px;">
                Gestiona el alumnado de <strong style="color: rgba(255,255,255,0.9);">{{ $institucionActiva->nombre }}</strong>
            </p>
        @else
            <p style="margin: 0; color: rgba(255,255,255,0.65); font-size: 13px;">Gestiona el alumnado de todas las instituciones disponibles.</p>
        @endif
    </section>

    <section class="stats">
        <article class="card"><strong class="metric">{{ $metricas['total'] }}</strong><span>Total alumnos</span></article>
        <article class="card"><strong class="metric">{{ $metricas['activos'] }}</strong><span>Activos</span></article>
        <article class="card"><strong class="metric">{{ $metricas['observacion'] }}</strong><span>En observacion</span></article>
        <article class="card"><strong class="metric">{{ $metricas['inactivos'] }}</strong><span>Inactivos</span></article>
    </section>

    <section class="panel">
        <div class="section-title">
            <h2>Listado operativo</h2>
            <span>{{ $estudiantes->total() }} resultados</span>
        </div>

        @if (session('status'))
            <div class="alert">{{ session('status') }}</div>
        @endif

        <div class="toolbar">
            <form class="filters" method="GET" action="{{ route('admin.estudiantes.index') }}">
                <input class="input" type="text" name="search" placeholder="Buscar por nombre, cedula, usuario, correo o telefono" value="{{ request('search') }}">
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
                <a class="btn" href="{{ route('admin.estudiantes.index') }}">Limpiar</a>
            </form>

            <button class="btn btn-primary" type="button" data-open-create>Nuevo alumno</button>
        </div>

        @if ($estudiantes->count())
            <table>
                <thead>
                    <tr>
                        <th>Alumno</th>
                        @if (! $institucionActiva)
                            <th>Institucion</th>
                        @endif
                        <th>Acceso</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($estudiantes as $estudiante)
                        @php
                            $persona = $estudiante->persona;
                            $nombre = trim(implode(' ', array_filter([$persona?->primer_nombre, $persona?->segundo_nombre, $persona?->primer_apellido, $persona?->segundo_apellido])));
                        @endphp
                        <tr>
                            <td class="name-cell">
                                <strong>{{ $nombre !== '' ? $nombre : 'Alumno sin nombre cargado' }}</strong>
                                <span>ID: {{ $persona?->numero_identificacion ?: 'Sin cedula' }} | {{ $persona?->email ?: ($estudiante->email ?: 'Sin correo') }}</span>
                            </td>
                            @if (! $institucionActiva)
                                <td>{{ $estudiante->institucion?->nombre ?: 'Sin institucion' }}</td>
                            @endif
                            <td>
                                <strong>{{ $estudiante->username }}</strong>
                                <br>
                                <span>Ultimo acceso: {{ $estudiante->ultimo_acceso?->format('Y-m-d H:i') ?: 'Sin registro' }}</span>
                            </td>
                            <td><span class="badge badge-{{ strtolower($estudiante->estado) }}">{{ $estados[$estudiante->estado] ?? $estudiante->estado }}</span></td>
                            <td>
                                <div class="actions">
                                    <a class="btn mini-btn" href="{{ route('admin.estudiantes.index', array_filter(['edit' => $estudiante->id, 'search' => request('search'), 'institucion' => request('institucion'), 'estado' => request('estado')])) }}">Editar</a>
                                    <form method="POST" action="{{ route('admin.estudiantes.destroy', $estudiante) }}" onsubmit="return confirm('Desea eliminar este alumno?');">
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
                @if ($estudiantes->onFirstPage())
                    <span class="btn">Anterior</span>
                @else
                    <a class="btn" href="{{ $estudiantes->previousPageUrl() }}">Anterior</a>
                @endif

                <span class="btn">Pagina {{ $estudiantes->currentPage() }} de {{ $estudiantes->lastPage() }}</span>

                @if ($estudiantes->hasMorePages())
                    <a class="btn" href="{{ $estudiantes->nextPageUrl() }}">Siguiente</a>
                @else
                    <span class="btn">Siguiente</span>
                @endif
            </div>
        @else
            <div class="empty">No hay alumnos con los filtros actuales.</div>
        @endif
    </section>
@endsection

@section('dialogs')
    <dialog class="modal" id="create-estudiante-modal">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <h3>Nuevo alumno</h3>
                    <span>Ingrese datos personales y de acceso.</span>
                </div>
                <button class="btn" type="button" data-close-create>x</button>
            </div>

            @if ($errors->any() && ! $estudianteEditando)
                <div class="errors">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="create-estudiante-form" method="POST" action="{{ route('admin.estudiantes.store') }}">
                @csrf

                <div class="form-grid">
                    <div class="field-group">
                        <label for="create_numero_identificacion">Numero de identificacion</label>
                        <input id="create_numero_identificacion" class="input" type="text" name="numero_identificacion" value="{{ old('numero_identificacion') }}" required>
                    </div>
                    @if ($institucionActiva)
                        <input type="hidden" name="institucion_id" value="{{ $institucionActiva->id }}">
                        <div class="field-group">
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
                        <label for="create_primer_nombre">Primer nombre</label>
                        <input id="create_primer_nombre" class="input" type="text" name="primer_nombre" value="{{ old('primer_nombre') }}" required>
                    </div>
                    <div class="field-group">
                        <label for="create_segundo_nombre">Segundo nombre</label>
                        <input id="create_segundo_nombre" class="input" type="text" name="segundo_nombre" value="{{ old('segundo_nombre') }}">
                    </div>
                    <div class="field-group">
                        <label for="create_primer_apellido">Primer apellido</label>
                        <input id="create_primer_apellido" class="input" type="text" name="primer_apellido" value="{{ old('primer_apellido') }}" required>
                    </div>
                    <div class="field-group">
                        <label for="create_segundo_apellido">Segundo apellido</label>
                        <input id="create_segundo_apellido" class="input" type="text" name="segundo_apellido" value="{{ old('segundo_apellido') }}">
                    </div>
                    <div class="field-group">
                        <label for="create_fecha_nacimiento">Fecha de nacimiento</label>
                        <input id="create_fecha_nacimiento" class="input" type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}">
                    </div>
                    <div class="field-group">
                        <label for="create_sexo">Sexo</label>
                        <select id="create_sexo" class="select" name="sexo">
                            <option value="">Seleccione</option>
                            @foreach ($sexos as $value => $label)
                                <option value="{{ $value }}" @selected(old('sexo') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field-group">
                        <label for="create_telefono">Telefono</label>
                        <input id="create_telefono" class="input" type="text" name="telefono" value="{{ old('telefono') }}">
                    </div>
                    <div class="field-group">
                        <label for="create_celular">Celular</label>
                        <input id="create_celular" class="input" type="text" name="celular" value="{{ old('celular') }}">
                    </div>
                    <div class="field-group">
                        <label for="create_email_persona">Correo personal</label>
                        <input id="create_email_persona" class="input" type="email" name="email_persona" value="{{ old('email_persona') }}">
                    </div>
                    <div class="field-group">
                        <label for="create_email">Correo de acceso</label>
                        <input id="create_email" class="input" type="email" name="email" value="{{ old('email') }}">
                    </div>
                    <div class="field-group">
                        <label for="create_username">Usuario</label>
                        <input id="create_username" class="input" type="text" name="username" value="{{ old('username') }}" required>
                    </div>
                    <div class="field-group">
                        <label for="create_password_hash">Contrasena</label>
                        <input id="create_password_hash" class="input" type="text" name="password_hash" value="{{ old('password_hash') }}" required>
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
                        <label for="create_provincia">Provincia</label>
                        <input id="create_provincia" class="input" type="text" name="provincia" value="{{ old('provincia') }}">
                    </div>
                    <div class="field-group">
                        <label for="create_canton">Canton</label>
                        <input id="create_canton" class="input" type="text" name="canton" value="{{ old('canton') }}">
                    </div>
                    <div class="field-group">
                        <label for="create_parroquia">Parroquia</label>
                        <input id="create_parroquia" class="input" type="text" name="parroquia" value="{{ old('parroquia') }}">
                    </div>
                    <div class="field-group full">
                        <label for="create_direccion">Direccion</label>
                        <input id="create_direccion" class="input" type="text" name="direccion" value="{{ old('direccion') }}">
                    </div>
                </div>

                <div class="field-note">El sistema asigna automaticamente el rol ESTUDIANTE al guardar.</div>

                <div class="actions" style="margin-top: 18px;">
                    <button class="btn btn-primary" type="submit">Crear alumno</button>
                    <button class="btn" type="button" data-close-create>Cancelar</button>
                </div>
            </form>
        </div>
    </dialog>

    @if ($estudianteEditando)
        <dialog class="modal" id="edit-estudiante-modal">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h3>Editar alumno</h3>
                        <span>Actualice los datos personales y de acceso.</span>
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

                <form id="edit-estudiante-form" method="POST" action="{{ route('admin.estudiantes.update', $estudianteEditando) }}">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        <div class="field-group">
                            <label for="edit_numero_identificacion">Numero de identificacion</label>
                            <input id="edit_numero_identificacion" class="input" type="text" name="numero_identificacion" value="{{ old('numero_identificacion', $personaEditando?->numero_identificacion) }}" required>
                        </div>
                        @if ($institucionActiva)
                            <input type="hidden" name="institucion_id" value="{{ $institucionActiva->id }}">
                            <div class="field-group">
                                <label>Institucion</label>
                                <input class="input" type="text" value="{{ $institucionActiva->nombre }}" disabled>
                            </div>
                        @else
                            <div class="field-group">
                                <label for="edit_institucion_id">Institucion</label>
                                <select id="edit_institucion_id" class="select" name="institucion_id" required>
                                    <option value="">Seleccione</option>
                                    @foreach ($instituciones as $institucion)
                                        <option value="{{ $institucion->id }}" @selected((string) old('institucion_id', $estudianteEditando->institucion_id) === (string) $institucion->id)>{{ $institucion->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="field-group">
                            <label for="edit_primer_nombre">Primer nombre</label>
                            <input id="edit_primer_nombre" class="input" type="text" name="primer_nombre" value="{{ old('primer_nombre', $personaEditando?->primer_nombre) }}" required>
                        </div>
                        <div class="field-group">
                            <label for="edit_segundo_nombre">Segundo nombre</label>
                            <input id="edit_segundo_nombre" class="input" type="text" name="segundo_nombre" value="{{ old('segundo_nombre', $personaEditando?->segundo_nombre) }}">
                        </div>
                        <div class="field-group">
                            <label for="edit_primer_apellido">Primer apellido</label>
                            <input id="edit_primer_apellido" class="input" type="text" name="primer_apellido" value="{{ old('primer_apellido', $personaEditando?->primer_apellido) }}" required>
                        </div>
                        <div class="field-group">
                            <label for="edit_segundo_apellido">Segundo apellido</label>
                            <input id="edit_segundo_apellido" class="input" type="text" name="segundo_apellido" value="{{ old('segundo_apellido', $personaEditando?->segundo_apellido) }}">
                        </div>
                        <div class="field-group">
                            <label for="edit_fecha_nacimiento">Fecha de nacimiento</label>
                            <input id="edit_fecha_nacimiento" class="input" type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $personaEditando?->fecha_nacimiento) }}">
                        </div>
                        <div class="field-group">
                            <label for="edit_sexo">Sexo</label>
                            <select id="edit_sexo" class="select" name="sexo">
                                <option value="">Seleccione</option>
                                @foreach ($sexos as $value => $label)
                                    <option value="{{ $value }}" @selected(old('sexo', $personaEditando?->sexo) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group">
                            <label for="edit_telefono">Telefono</label>
                            <input id="edit_telefono" class="input" type="text" name="telefono" value="{{ old('telefono', $personaEditando?->telefono) }}">
                        </div>
                        <div class="field-group">
                            <label for="edit_celular">Celular</label>
                            <input id="edit_celular" class="input" type="text" name="celular" value="{{ old('celular', $personaEditando?->celular) }}">
                        </div>
                        <div class="field-group">
                            <label for="edit_email_persona">Correo personal</label>
                            <input id="edit_email_persona" class="input" type="email" name="email_persona" value="{{ old('email_persona', $personaEditando?->email) }}">
                        </div>
                        <div class="field-group">
                            <label for="edit_email">Correo de acceso</label>
                            <input id="edit_email" class="input" type="email" name="email" value="{{ old('email', $estudianteEditando->email) }}">
                        </div>
                        <div class="field-group">
                            <label for="edit_username">Usuario</label>
                            <input id="edit_username" class="input" type="text" name="username" value="{{ old('username', $estudianteEditando->username) }}" required>
                        </div>
                        <div class="field-group">
                            <label for="edit_password_hash">Contrasena</label>
                            <input id="edit_password_hash" class="input" type="text" name="password_hash" value="{{ old('password_hash') }}">
                        </div>
                        <div class="field-group">
                            <label for="edit_estado">Estado</label>
                            <select id="edit_estado" class="select" name="estado" required>
                                @foreach ($estados as $value => $label)
                                    <option value="{{ $value }}" @selected(old('estado', $estudianteEditando->estado) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group">
                            <label for="edit_ultimo_acceso">Ultimo acceso</label>
                            <input id="edit_ultimo_acceso" class="input" type="datetime-local" name="ultimo_acceso" value="{{ old('ultimo_acceso', optional($estudianteEditando->ultimo_acceso)->format('Y-m-d\\TH:i')) }}">
                        </div>
                        <div class="field-group">
                            <label for="edit_provincia">Provincia</label>
                            <input id="edit_provincia" class="input" type="text" name="provincia" value="{{ old('provincia', $personaEditando?->provincia) }}">
                        </div>
                        <div class="field-group">
                            <label for="edit_canton">Canton</label>
                            <input id="edit_canton" class="input" type="text" name="canton" value="{{ old('canton', $personaEditando?->canton) }}">
                        </div>
                        <div class="field-group">
                            <label for="edit_parroquia">Parroquia</label>
                            <input id="edit_parroquia" class="input" type="text" name="parroquia" value="{{ old('parroquia', $personaEditando?->parroquia) }}">
                        </div>
                        <div class="field-group full">
                            <label for="edit_direccion">Direccion</label>
                            <input id="edit_direccion" class="input" type="text" name="direccion" value="{{ old('direccion', $personaEditando?->direccion) }}">
                        </div>
                    </div>

                    <div class="field-note">Deje la contrasena vacia si no desea cambiarla.</div>

                    <div class="actions" style="margin-top: 18px;">
                        <button class="btn btn-primary" type="submit">Guardar cambios</button>
                        <a class="btn" href="{{ route('admin.estudiantes.index') }}">Cancelar edicion</a>
                    </div>
                </form>
            </div>
        </dialog>
    @endif
@endsection

@section('scripts')
    <script>
        const createModal = document.getElementById('create-estudiante-modal');
        const editModal = document.getElementById('edit-estudiante-modal');

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

        @if ($estudianteEditando)
            editModal?.showModal();
        @endif

        @if ($errors->any() && ! $estudianteEditando)
            createModal?.showModal();
        @endif
    </script>
@endsection
