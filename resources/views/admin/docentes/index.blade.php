@extends('layouts.admin-shell')

@php
    $personaEditando = $docenteEditando?->persona;
@endphp

@section('title', 'Administracion de docentes')
@section('admin-nav', 'docentes')
@section('sidebar-panel-copy', 'Gestione el personal docente desde la misma base compartida del dashboard administrativo.')

@section('sidebar-panel-items')
    <div>
        <strong>Alta consistente</strong>
        <p>El sistema asigna automaticamente el rol docente al crear un nuevo registro.</p>
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
    <span>Administracion de docentes</span>
@endsection

@section('head')
    <link rel="stylesheet" href="{{ asset('css/docentes.css') }}">
@endsection

@section('content')
    <div class="docentes-view">
    <section class="panel">
    <div class="docentes-top">
    <header class="docentes-header">
        <div>
            <h2>Administración de docentes</h2>
            <p>{{ $institucionActiva?->nombre ?? 'Todas las instituciones' }}</p>
        </div>
        <div class="actions">
            <a class="btn" href="{{ route('admin.docentes.print', request()->only(['search', 'estado', 'institucion'])) }}" target="_blank" rel="noopener"><i class="fa-solid fa-print" aria-hidden="true"></i> Imprimir PDF</a>
            <a class="btn" href="{{ route('admin.docentes.signatures', request()->only(['search', 'estado', 'institucion'])) }}" target="_blank" rel="noopener"><i class="fa-solid fa-file-signature" aria-hidden="true"></i> Lista para firmas</a>
            <a class="btn" href="{{ route('admin.docentes.excel', request()->only(['search', 'estado', 'institucion'])) }}"><i class="fa-solid fa-file-excel" aria-hidden="true"></i> Exportar a Excel</a>
            <button class="btn btn-primary" type="button" data-open-create><i class="fa-solid fa-plus" aria-hidden="true"></i> Nuevo docente</button>
        </div>
    </header>
    <div class="docentes-summary" aria-label="Resumen de docentes">
        <span><strong>{{ $metricas['total'] }}</strong> docentes</span>
        <span><strong>{{ $metricas['activos'] }}</strong> activos</span>
        <span><strong>{{ $metricas['observacion'] }}</strong> en observación</span>
        <span><strong>{{ $metricas['inactivos'] }}</strong> inactivos</span>
    </div>

        <div class="section-title">
            <h2>Listado de docentes</h2>
            <span>{{ $docentes->total() }} resultados</span>
        </div>

        @if (session('status'))
            <div class="alert">{{ session('status') }}</div>
        @endif

        <div class="toolbar">
            <form class="filters" method="GET" action="{{ route('admin.docentes.index') }}">
                <input class="input" type="text" name="search" aria-label="Buscar docentes" placeholder="Nombre, cédula, usuario o correo" value="{{ request('search') }}">
                @if (! $institucionActiva)
                    <select class="select" name="institucion">
                        <option value="">Todas las instituciones</option>
                        @foreach ($instituciones as $institucion)
                            <option value="{{ $institucion->id }}" @selected((string) request('institucion') === (string) $institucion->id)>{{ $institucion->nombre }}</option>
                        @endforeach
                    </select>
                @endif
                <select class="select" name="estado" aria-label="Filtrar por estado">
                    <option value="">Todos los estados</option>
                    @foreach ($estados as $value => $label)
                        <option value="{{ $value }}" @selected(request('estado') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <label class="docentes-page-size" for="docentes-per-page">Mostrar
                    <select id="docentes-per-page" class="select" name="per_page">
                        @foreach ([25, 50, 100] as $cantidad)
                            <option value="{{ $cantidad }}" @selected($docentes->perPage() === $cantidad)>{{ $cantidad }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="btn" type="submit">Filtrar</button>
                <a class="btn" href="{{ route('admin.docentes.index') }}">Limpiar</a>
            </form>

        </div>

    </div>

        @if ($docentes->count())
            <div class="docentes-table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Docente</th>
                        @if (! $institucionActiva)
                            <th>Institucion</th>
                        @endif
                        <th>Acceso</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($docentes as $docente)
                        @php
                            $persona = $docente->persona;
                            $nombre = trim(implode(' ', array_filter([$persona?->tratamiento, $persona?->primer_nombre, $persona?->segundo_nombre, $persona?->primer_apellido, $persona?->segundo_apellido])));
                        @endphp
                        <tr>
                            <td class="name-cell">
                                <strong>{{ $nombre !== '' ? $nombre : 'Docente sin nombre cargado' }}</strong>
                                <span>ID: {{ $persona?->numero_identificacion ?: 'Sin cedula' }} | {{ $persona?->email ?: ($docente->email ?: 'Sin correo') }}</span>
                            </td>
                            @if (! $institucionActiva)
                                <td>{{ $docente->institucion?->nombre ?: 'Sin institucion' }}</td>
                            @endif
                            <td>
                                <strong>{{ $docente->username }}</strong>
                                <br>
                                <span>Ultimo acceso: {{ $docente->ultimo_acceso?->format('Y-m-d H:i') ?: 'Sin registro' }}</span>
                            </td>
                            <td><span class="badge badge-{{ strtolower($docente->estado) }}">{{ $estados[$docente->estado] ?? $docente->estado }}</span></td>
                            <td>
                                <div class="actions">
                                    <button class="btn mini-btn record-action" type="button" data-open-datos="datos-docente-{{ $docente->id }}" aria-label="Ver datos del docente" title="Ver datos"><i class="fa-solid fa-eye" aria-hidden="true"></i></button>
                                    <a class="btn mini-btn record-action" href="{{ route('admin.docentes.index', array_filter(['edit' => $docente->id, 'search' => request('search'), 'institucion' => request('institucion'), 'estado' => request('estado'), 'per_page' => $docentes->perPage(), 'page' => request('page')])) }}" aria-label="Editar registro" title="Editar"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></a>
                                    <form method="POST" action="{{ route('admin.docentes.destroy', $docente) }}" onsubmit="return confirm('Desea eliminar este docente?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger mini-btn record-action" type="submit" aria-label="Eliminar registro" title="Eliminar"><i class="fa-solid fa-trash-can" aria-hidden="true"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>

            <div class="pager">
                <span class="docentes-range">{{ $docentes->firstItem() }}–{{ $docentes->lastItem() }} de {{ $docentes->total() }} docentes</span>
                @if ($docentes->onFirstPage())
                    <span class="btn">Anterior</span>
                @else
                    <a class="btn" href="{{ $docentes->previousPageUrl() }}">Anterior</a>
                @endif

                <span class="btn">Pagina {{ $docentes->currentPage() }} de {{ $docentes->lastPage() }}</span>

                @if ($docentes->hasMorePages())
                    <a class="btn" href="{{ $docentes->nextPageUrl() }}">Siguiente</a>
                @else
                    <span class="btn">Siguiente</span>
                @endif
            </div>
        @else
            <div class="empty">No hay docentes con los filtros actuales.</div>
        @endif
    </section>
    </div>
@endsection

@section('dialogs')
    @include('admin.docentes.partials.datos')

    <dialog class="modal" id="create-docente-modal">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <h3>Nuevo docente</h3>
                    <span>Ingrese datos personales y de acceso.</span>
                </div>
                <button class="btn" type="button" data-close-create>x</button>
            </div>

            @if ($errors->any() && ! $docenteEditando)
                <div class="errors">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="create-docente-form" method="POST" action="{{ route('admin.docentes.store') }}">
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
                        <label for="create_tratamiento">Título o tratamiento</label>
                        <input id="create_tratamiento" class="input" type="text" name="tratamiento" value="{{ old('tratamiento') }}" maxlength="120" placeholder="Ej.: Lic., Ing., Mgtr.">
                    </div>
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

                <div class="field-note">El sistema asigna automaticamente el rol DOCENTE al guardar.</div>

                <div class="actions" style="margin-top: 18px;">
                    <button class="btn btn-primary" type="submit">Crear docente</button>
                    <button class="btn" type="button" data-close-create>Cancelar</button>
                </div>
            </form>
        </div>
    </dialog>

    @if ($docenteEditando)
        <dialog class="modal" id="edit-docente-modal">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h3>Editar docente</h3>
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

                <form id="edit-docente-form" method="POST" action="{{ route('admin.docentes.update', $docenteEditando) }}">
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
                                        <option value="{{ $institucion->id }}" @selected((string) old('institucion_id', $docenteEditando->institucion_id) === (string) $institucion->id)>{{ $institucion->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="field-group">
                            <label for="edit_tratamiento">Título o tratamiento</label>
                            <input id="edit_tratamiento" class="input" type="text" name="tratamiento" value="{{ old('tratamiento', $personaEditando?->tratamiento) }}" maxlength="120" placeholder="Ej.: Lic., Ing., Mgtr.">
                        </div>
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
                            <input id="edit_email" class="input" type="email" name="email" value="{{ old('email', $docenteEditando->email) }}">
                        </div>
                        <div class="field-group">
                            <label for="edit_username">Usuario</label>
                            <input id="edit_username" class="input" type="text" name="username" value="{{ old('username', $docenteEditando->username) }}" required>
                        </div>
                        <div class="field-group">
                            <label for="edit_password_hash">Contrasena</label>
                            <input id="edit_password_hash" class="input" type="text" name="password_hash" value="{{ old('password_hash') }}">
                        </div>
                        <div class="field-group">
                            <label for="edit_estado">Estado</label>
                            <select id="edit_estado" class="select" name="estado" required>
                                @foreach ($estados as $value => $label)
                                    <option value="{{ $value }}" @selected(old('estado', $docenteEditando->estado) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group">
                            <label for="edit_ultimo_acceso">Ultimo acceso</label>
                            <input id="edit_ultimo_acceso" class="input" type="datetime-local" name="ultimo_acceso" value="{{ old('ultimo_acceso', optional($docenteEditando->ultimo_acceso)->format('Y-m-d\\TH:i')) }}">
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
                        <a class="btn" href="{{ route('admin.docentes.index') }}">Cancelar edicion</a>
                    </div>
                </form>
            </div>
        </dialog>
    @endif
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('[data-open-datos]').forEach((button) => {
            button.addEventListener('click', () => document.getElementById(button.dataset.openDatos)?.showModal());
        });
        document.querySelectorAll('[data-close-datos]').forEach((button) => {
            button.addEventListener('click', () => button.closest('dialog')?.close());
        });
        document.getElementById('docentes-per-page')?.addEventListener('change', (event) => {
            event.target.form.requestSubmit();
        });
        const createModal = document.getElementById('create-docente-modal');
        const editModal = document.getElementById('edit-docente-modal');

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

        @if ($docenteEditando)
            editModal?.showModal();
        @endif

        @if ($errors->any() && ! $docenteEditando)
            createModal?.showModal();
        @endif
    </script>
@endsection
