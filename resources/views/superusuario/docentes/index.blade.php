<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administracion de Docentes - IntegraEdu360</title>
    <style>
        :root {
            --navy-950: #081a2b;
            --navy-900: #11314f;
            --slate-50: #f4f7fb;
            --slate-100: #e6edf5;
            --slate-200: #d4dfeb;
            --slate-500: #5c6f83;
            --slate-700: #2a3d51;
            --white: #ffffff;
            --accent: #2f80c1;
            --success: #2c7a4b;
            --warning: #9a670d;
            --muted: #617487;
            --danger: #8f2d2d;
        }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; font-family: "Segoe UI", Arial, sans-serif; color: var(--slate-700); background: radial-gradient(circle at top right, rgba(47, 128, 193, 0.16), transparent 24%), linear-gradient(160deg, #eef3f8 0%, #f8fbfd 54%, #eef3f8 100%); }
        .app-shell { display: grid; grid-template-columns: 300px minmax(0, 1fr); min-height: 100vh; }
        .sidebar { background: linear-gradient(180deg, rgba(8, 26, 43, 0.99), rgba(17, 49, 79, 0.97)); color: var(--white); padding: 28px 22px; }
        .brand { display: flex; align-items: center; gap: 14px; margin-bottom: 30px; }
        .brand-mark, .role-badge { display: grid; place-items: center; color: var(--navy-950); background: linear-gradient(145deg, var(--accent), color-mix(in srgb, var(--accent) 45%, white)); font-weight: 900; }
        .brand-mark { width: 52px; height: 52px; border-radius: 16px; }
        .role-badge { width: 58px; height: 58px; border-radius: 18px; font-size: 1.15rem; margin-bottom: 16px; }
        .brand strong { display: block; font-size: 1rem; letter-spacing: .08em; text-transform: uppercase; }
        .brand span, .profile-card p, .sidebar-panel p, .nav-item small { color: rgba(255, 255, 255, .72); }
        .profile-card, .sidebar-panel { background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.1); border-radius: 24px; padding: 20px; }
        .profile-card { margin-bottom: 18px; }
        .sidebar-nav { display: grid; gap: 10px; margin: 18px 0; }
        .nav-item { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; border-radius: 16px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.06); color: var(--white); text-decoration: none; font-weight: 700; }
        .nav-item.active { background: linear-gradient(90deg, rgba(47, 128, 193, .28), rgba(255,255,255,.06)); border-color: rgba(47, 128, 193, .42); }
        .sidebar-list { display: grid; gap: 12px; margin-top: 14px; }
        .sidebar-list div { padding: 14px; border-radius: 16px; background: rgba(255,255,255,.05); }
        .main { padding: 28px; }
        .topbar, .hero, .stats article, .panel, .modal-card { background: rgba(255,255,255,.95); border: 1px solid rgba(212,223,235,.9); box-shadow: 0 16px 32px rgba(8, 26, 43, 0.08); border-radius: 24px; }
        .topbar { padding: 18px 22px; display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
        .crumbs { display: flex; flex-wrap: wrap; gap: 10px; font-weight: 700; color: var(--slate-500); }
        .crumbs a { color: var(--navy-900); text-decoration: none; }
        .hero { padding: 28px; margin-bottom: 22px; background: linear-gradient(135deg, rgba(8,26,43,.98), rgba(17,49,79,.95)); color: var(--white); }
        .hero p { color: rgba(255,255,255,.8); max-width: 820px; }
        .eyebrow { display: inline-flex; padding: 8px 12px; border-radius: 999px; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.12); text-transform: uppercase; font-size: .78rem; font-weight: 800; margin-bottom: 14px; }
        .stats { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 18px; margin-bottom: 22px; }
        .stats article { padding: 22px; }
        .metric { display: block; color: var(--navy-950); font-size: 2rem; margin-bottom: 8px; }
        .panel { padding: 24px; }
        .section-title, .toolbar, .actions, .pager, .filters { display: flex; flex-wrap: wrap; gap: 12px; }
        .section-title, .toolbar { justify-content: space-between; align-items: center; }
        .section-title { margin-bottom: 18px; }
        .section-title h2 { margin: 0; color: var(--navy-950); }
        .input, .select, .btn { min-height: 44px; border-radius: 14px; font: inherit; }
        .input, .select { width: 100%; padding: 0 14px; border: 1px solid var(--slate-200); background: #fff; color: var(--slate-700); }
        .filters { flex: 1 1 720px; }
        .filters .input { flex: 1 1 280px; }
        .filters .select { flex: 0 1 220px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 0 16px; border: 1px solid var(--slate-200); background: #fff; color: var(--navy-900); font-weight: 800; text-decoration: none; cursor: pointer; }
        .btn-primary { background: linear-gradient(135deg, var(--navy-900), var(--accent)); border-color: transparent; color: var(--white); }
        .btn-danger { color: var(--danger); background: #fff6f6; border-color: #f2c9c9; }
        .alert { margin-bottom: 16px; padding: 14px 16px; border-radius: 16px; background: #edf8f1; border: 1px solid #cce7d5; color: #1f5f36; font-weight: 700; }
        .errors { margin-bottom: 16px; padding: 14px 16px; border-radius: 16px; background: #fff4f4; border: 1px solid #efcaca; color: var(--danger); }
        .errors ul { margin: 0; padding-left: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 14px 12px; text-align: left; border-bottom: 1px solid var(--slate-100); vertical-align: top; }
        th { color: var(--navy-900); font-size: .8rem; text-transform: uppercase; }
        .name-cell strong { display: block; color: var(--navy-900); margin-bottom: 8px; }
        .badge { display: inline-flex; align-items: center; justify-content: center; padding: 6px 10px; border-radius: 999px; font-size: .78rem; font-weight: 800; }
        .badge-activo { background: #e8f7ed; color: var(--success); }
        .badge-observacion { background: #fff4df; color: var(--warning); }
        .badge-inactivo { background: #eef2f6; color: var(--muted); }
        .pager { justify-content: flex-end; margin-top: 18px; }
        .empty { padding: 32px 20px; text-align: center; color: var(--slate-500); }
        dialog.modal { width: min(960px, calc(100% - 24px)); border: 0; border-radius: 28px; padding: 0; box-shadow: 0 24px 52px rgba(8, 26, 43, 0.12); }
        dialog.modal::backdrop { background: rgba(8, 26, 43, 0.45); }
        .modal-card { padding: 24px; }
        .modal-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 18px; }
        .modal-header h3 { margin: 0; color: var(--navy-950); }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 14px; }
        .field-group { display: grid; gap: 8px; }
        .field-group.full { grid-column: 1 / -1; }
        .field-group label { color: var(--navy-900); font-size: .9rem; font-weight: 700; }
        .field-note { margin-top: 14px; color: var(--slate-500); }
        @media (max-width: 1180px) { .app-shell { grid-template-columns: 1fr; } .stats { grid-template-columns: repeat(2, minmax(0,1fr)); } }
        @media (max-width: 720px) { .main { padding: 18px; } .topbar { flex-direction: column; align-items: flex-start; } .form-grid, .stats { grid-template-columns: 1fr; } table, thead, tbody, th, td, tr { display: block; } thead { display: none; } td { padding: 6px 0; border-bottom: 0; } tr { padding: 14px 0; border-bottom: 1px solid var(--slate-100); } }
    </style>
</head>
<body>
    @php
        $personaEditando = $docenteEditando?->persona;
    @endphp
    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-mark">IE</div>
                <div>
                    <strong>IntegraEdu360</strong>
                    <span>Dashboard institucional</span>
                </div>
            </div>
            <div class="profile-card">
                <div class="role-badge">SU</div>
                <h2>Superusuario</h2>
                <p>Centralice la gestion de docentes junto con instituciones y accesos clave.</p>
            </div>
            <nav class="sidebar-nav">
                <a class="nav-item" href="{{ route('roles.dashboard', 'superusuario') }}"><span>Resumen ejecutivo</span><small>Hoy</small></a>
                <a class="nav-item" href="{{ route('superusuario.instituciones.index') }}"><span>Administracion de instituciones</span><small>CRUD</small></a>
                <a class="nav-item active" href="{{ route('superusuario.docentes.index') }}"><span>Administracion de docentes</span></a>
                <a class="nav-item" href="{{ route('auth.form') }}"><span>Cambiar acceso</span><small>Menu</small></a>
            </nav>
        </aside>
        <main class="main">
            <div class="topbar">
                <div class="crumbs">
                    <a href="{{ route('auth.form') }}">Menu principal</a>
                    <span>/</span>
                    <a href="{{ route('roles.dashboard', 'superusuario') }}">Dashboard superusuario</a>
                    <span>/</span>
                    <span>Administracion de docentes</span>
                </div>
                <a class="btn" href="{{ route('roles.dashboard', 'superusuario') }}">Volver al dashboard</a>
            </div>
            <section class="hero">
                <h1>Administracion de docentes</h1>
            </section>
            <section class="stats">
                <article><strong class="metric">{{ $metricas['total'] }}</strong><span>Total docentes</span></article>
                <article><strong class="metric">{{ $metricas['activos'] }}</strong><span>Activos</span></article>
                <article><strong class="metric">{{ $metricas['observacion'] }}</strong><span>En observacion</span></article>
                <article><strong class="metric">{{ $metricas['inactivos'] }}</strong><span>Inactivos</span></article>
            </section>
            <section class="panel">
                <div class="section-title">
                    <h2>Listado operativo</h2>
                    <span>{{ $docentes->total() }} resultados</span>
                </div>
                @if (session('status'))
                    <div class="alert">{{ session('status') }}</div>
                @endif
                <div class="toolbar">
                    <form class="filters" method="GET" action="{{ route('superusuario.docentes.index') }}">
                        <input class="input" type="text" name="search" placeholder="Buscar por nombre, cedula, usuario, correo o telefono" value="{{ request('search') }}">
                        <select class="select" name="institucion">
                            <option value="">Todas las instituciones</option>
                            @foreach ($instituciones as $institucion)
                                <option value="{{ $institucion->id }}" @selected((string) request('institucion') === (string) $institucion->id)>{{ $institucion->nombre }}</option>
                            @endforeach
                        </select>
                        <select class="select" name="estado">
                            <option value="">Todos los estados</option>
                            @foreach ($estados as $value => $label)
                                <option value="{{ $value }}" @selected(request('estado') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button class="btn" type="submit">Filtrar</button>
                        <a class="btn" href="{{ route('superusuario.docentes.index') }}">Limpiar</a>
                    </form>
                    <button class="btn btn-primary" type="button" data-open-create>Nuevo docente</button>
                </div>
                @if ($docentes->count())
                    <table>
                        <thead>
                            <tr>
                                <th>Docente</th>
                                <th>Institucion</th>
                                <th>Acceso</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($docentes as $docente)
                                @php
                                    $persona = $docente->persona;
                                    $nombre = trim(implode(' ', array_filter([$persona?->primer_nombre, $persona?->segundo_nombre, $persona?->primer_apellido, $persona?->segundo_apellido])));
                                @endphp
                                <tr>
                                    <td class="name-cell"><strong>{{ $nombre !== '' ? $nombre : 'Docente sin nombre cargado' }}</strong><span>ID: {{ $persona?->numero_identificacion ?: 'Sin cedula' }} | {{ $persona?->email ?: ($docente->email ?: 'Sin correo') }}</span></td>
                                    <td>{{ $docente->institucion?->nombre ?: 'Sin institucion' }}</td>
                                    <td><strong>{{ $docente->username }}</strong><br><span>Ultimo acceso: {{ $docente->ultimo_acceso?->format('Y-m-d H:i') ?: 'Sin registro' }}</span></td>
                                    <td><span class="badge badge-{{ strtolower($docente->estado) }}">{{ $estados[$docente->estado] ?? $docente->estado }}</span></td>
                                    <td>
                                        <div class="actions">
                                            <a class="btn" href="{{ route('superusuario.docentes.index', array_filter(['edit' => $docente->id, 'search' => request('search'), 'institucion' => request('institucion'), 'estado' => request('estado')])) }}">Editar</a>
                                            <form method="POST" action="{{ route('superusuario.docentes.destroy', $docente) }}" onsubmit="return confirm('Desea eliminar este docente?');">
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
                        @if ($docentes->onFirstPage()) <span class="btn">Anterior</span> @else <a class="btn" href="{{ $docentes->previousPageUrl() }}">Anterior</a> @endif
                        <span class="btn">Pagina {{ $docentes->currentPage() }} de {{ $docentes->lastPage() }}</span>
                        @if ($docentes->hasMorePages()) <a class="btn" href="{{ $docentes->nextPageUrl() }}">Siguiente</a> @else <span class="btn">Siguiente</span> @endif
                    </div>
                @else
                    <div class="empty">No hay docentes con los filtros actuales.</div>
                @endif
            </section>
        </main>
    </div>
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
                <div class="errors"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <form id="create-docente-form" method="POST" action="{{ route('superusuario.docentes.store') }}">
                @csrf
                <div class="form-grid">
                    <div class="field-group"><label for="create_numero_identificacion">Numero de identificacion</label><input id="create_numero_identificacion" class="input" type="text" name="numero_identificacion" value="{{ old('numero_identificacion') }}" required></div>
                    <div class="field-group"><label for="create_institucion_id">Institucion</label><select id="create_institucion_id" class="select" name="institucion_id" required><option value="">Seleccione</option>@foreach ($instituciones as $institucion)<option value="{{ $institucion->id }}" @selected((string) old('institucion_id') === (string) $institucion->id)>{{ $institucion->nombre }}</option>@endforeach</select></div>
                    <div class="field-group"><label for="create_primer_nombre">Primer nombre</label><input id="create_primer_nombre" class="input" type="text" name="primer_nombre" value="{{ old('primer_nombre') }}" required></div>
                    <div class="field-group"><label for="create_segundo_nombre">Segundo nombre</label><input id="create_segundo_nombre" class="input" type="text" name="segundo_nombre" value="{{ old('segundo_nombre') }}"></div>
                    <div class="field-group"><label for="create_primer_apellido">Primer apellido</label><input id="create_primer_apellido" class="input" type="text" name="primer_apellido" value="{{ old('primer_apellido') }}" required></div>
                    <div class="field-group"><label for="create_segundo_apellido">Segundo apellido</label><input id="create_segundo_apellido" class="input" type="text" name="segundo_apellido" value="{{ old('segundo_apellido') }}"></div>
                    <div class="field-group"><label for="create_fecha_nacimiento">Fecha de nacimiento</label><input id="create_fecha_nacimiento" class="input" type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}"></div>
                    <div class="field-group"><label for="create_sexo">Sexo</label><select id="create_sexo" class="select" name="sexo"><option value="">Seleccione</option>@foreach ($sexos as $value => $label)<option value="{{ $value }}" @selected(old('sexo') === $value)>{{ $label }}</option>@endforeach</select></div>
                    <div class="field-group"><label for="create_telefono">Telefono</label><input id="create_telefono" class="input" type="text" name="telefono" value="{{ old('telefono') }}"></div>
                    <div class="field-group"><label for="create_celular">Celular</label><input id="create_celular" class="input" type="text" name="celular" value="{{ old('celular') }}"></div>
                    <div class="field-group"><label for="create_email_persona">Correo personal</label><input id="create_email_persona" class="input" type="email" name="email_persona" value="{{ old('email_persona') }}"></div>
                    <div class="field-group"><label for="create_email">Correo de acceso</label><input id="create_email" class="input" type="email" name="email" value="{{ old('email') }}"></div>
                    <div class="field-group"><label for="create_username">Usuario</label><input id="create_username" class="input" type="text" name="username" value="{{ old('username') }}" required></div>
                    <div class="field-group"><label for="create_password_hash">Contrasena</label><input id="create_password_hash" class="input" type="text" name="password_hash" value="{{ old('password_hash') }}" required></div>
                    <div class="field-group"><label for="create_estado">Estado</label><select id="create_estado" class="select" name="estado" required>@foreach ($estados as $value => $label)<option value="{{ $value }}" @selected(old('estado', 'ACTIVO') === $value)>{{ $label }}</option>@endforeach</select></div>
                    <div class="field-group"><label for="create_ultimo_acceso">Ultimo acceso</label><input id="create_ultimo_acceso" class="input" type="datetime-local" name="ultimo_acceso" value="{{ old('ultimo_acceso') }}"></div>
                    <div class="field-group"><label for="create_provincia">Provincia</label><input id="create_provincia" class="input" type="text" name="provincia" value="{{ old('provincia') }}"></div>
                    <div class="field-group"><label for="create_canton">Canton</label><input id="create_canton" class="input" type="text" name="canton" value="{{ old('canton') }}"></div>
                    <div class="field-group"><label for="create_parroquia">Parroquia</label><input id="create_parroquia" class="input" type="text" name="parroquia" value="{{ old('parroquia') }}"></div>
                    <div class="field-group full"><label for="create_direccion">Direccion</label><input id="create_direccion" class="input" type="text" name="direccion" value="{{ old('direccion') }}"></div>
                </div>
                <div class="field-note">El sistema asigna automaticamente el rol DOCENTE al guardar.</div>
                <div class="actions" style="margin-top: 18px;"><button class="btn btn-primary" type="submit">Crear docente</button><button class="btn" type="button" data-close-create>Cancelar</button></div>
            </form>
        </div>
    </dialog>
    @if ($docenteEditando)
        <dialog class="modal" id="edit-docente-modal">
            <div class="modal-card">
                <div class="modal-header">
                    <div><h3>Editar docente</h3><span>Actualice los datos personales y de acceso.</span></div>
                    <button class="btn" type="button" data-close-edit>x</button>
                </div>
                @if ($errors->any())
                    <div class="errors"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <form id="edit-docente-form" method="POST" action="{{ route('superusuario.docentes.update', $docenteEditando) }}">
                    @csrf
                    @method('PUT')
                    <div class="form-grid">
                        <div class="field-group"><label for="edit_numero_identificacion">Numero de identificacion</label><input id="edit_numero_identificacion" class="input" type="text" name="numero_identificacion" value="{{ old('numero_identificacion', $personaEditando?->numero_identificacion) }}" required></div>
                        <div class="field-group"><label for="edit_institucion_id">Institucion</label><select id="edit_institucion_id" class="select" name="institucion_id" required><option value="">Seleccione</option>@foreach ($instituciones as $institucion)<option value="{{ $institucion->id }}" @selected((string) old('institucion_id', $docenteEditando->institucion_id) === (string) $institucion->id)>{{ $institucion->nombre }}</option>@endforeach</select></div>
                        <div class="field-group"><label for="edit_primer_nombre">Primer nombre</label><input id="edit_primer_nombre" class="input" type="text" name="primer_nombre" value="{{ old('primer_nombre', $personaEditando?->primer_nombre) }}" required></div>
                        <div class="field-group"><label for="edit_segundo_nombre">Segundo nombre</label><input id="edit_segundo_nombre" class="input" type="text" name="segundo_nombre" value="{{ old('segundo_nombre', $personaEditando?->segundo_nombre) }}"></div>
                        <div class="field-group"><label for="edit_primer_apellido">Primer apellido</label><input id="edit_primer_apellido" class="input" type="text" name="primer_apellido" value="{{ old('primer_apellido', $personaEditando?->primer_apellido) }}" required></div>
                        <div class="field-group"><label for="edit_segundo_apellido">Segundo apellido</label><input id="edit_segundo_apellido" class="input" type="text" name="segundo_apellido" value="{{ old('segundo_apellido', $personaEditando?->segundo_apellido) }}"></div>
                        <div class="field-group"><label for="edit_fecha_nacimiento">Fecha de nacimiento</label><input id="edit_fecha_nacimiento" class="input" type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $personaEditando?->fecha_nacimiento) }}"></div>
                        <div class="field-group"><label for="edit_sexo">Sexo</label><select id="edit_sexo" class="select" name="sexo"><option value="">Seleccione</option>@foreach ($sexos as $value => $label)<option value="{{ $value }}" @selected(old('sexo', $personaEditando?->sexo) === $value)>{{ $label }}</option>@endforeach</select></div>
                        <div class="field-group"><label for="edit_telefono">Telefono</label><input id="edit_telefono" class="input" type="text" name="telefono" value="{{ old('telefono', $personaEditando?->telefono) }}"></div>
                        <div class="field-group"><label for="edit_celular">Celular</label><input id="edit_celular" class="input" type="text" name="celular" value="{{ old('celular', $personaEditando?->celular) }}"></div>
                        <div class="field-group"><label for="edit_email_persona">Correo personal</label><input id="edit_email_persona" class="input" type="email" name="email_persona" value="{{ old('email_persona', $personaEditando?->email) }}"></div>
                        <div class="field-group"><label for="edit_email">Correo de acceso</label><input id="edit_email" class="input" type="email" name="email" value="{{ old('email', $docenteEditando->email) }}"></div>
                        <div class="field-group"><label for="edit_username">Usuario</label><input id="edit_username" class="input" type="text" name="username" value="{{ old('username', $docenteEditando->username) }}" required></div>
                        <div class="field-group"><label for="edit_password_hash">Contrasena</label><input id="edit_password_hash" class="input" type="text" name="password_hash" value="{{ old('password_hash') }}"></div>
                        <div class="field-group"><label for="edit_estado">Estado</label><select id="edit_estado" class="select" name="estado" required>@foreach ($estados as $value => $label)<option value="{{ $value }}" @selected(old('estado', $docenteEditando->estado) === $value)>{{ $label }}</option>@endforeach</select></div>
                        <div class="field-group"><label for="edit_ultimo_acceso">Ultimo acceso</label><input id="edit_ultimo_acceso" class="input" type="datetime-local" name="ultimo_acceso" value="{{ old('ultimo_acceso', optional($docenteEditando->ultimo_acceso)->format('Y-m-d\TH:i')) }}"></div>
                        <div class="field-group"><label for="edit_provincia">Provincia</label><input id="edit_provincia" class="input" type="text" name="provincia" value="{{ old('provincia', $personaEditando?->provincia) }}"></div>
                        <div class="field-group"><label for="edit_canton">Canton</label><input id="edit_canton" class="input" type="text" name="canton" value="{{ old('canton', $personaEditando?->canton) }}"></div>
                        <div class="field-group"><label for="edit_parroquia">Parroquia</label><input id="edit_parroquia" class="input" type="text" name="parroquia" value="{{ old('parroquia', $personaEditando?->parroquia) }}"></div>
                        <div class="field-group full"><label for="edit_direccion">Direccion</label><input id="edit_direccion" class="input" type="text" name="direccion" value="{{ old('direccion', $personaEditando?->direccion) }}"></div>
                    </div>
                    <div class="field-note">Deje la contrasena vacia si no desea cambiarla.</div>
                    <div class="actions" style="margin-top: 18px;"><button class="btn btn-primary" type="submit">Guardar cambios</button><a class="btn" href="{{ route('superusuario.docentes.index') }}">Cancelar edicion</a></div>
                </form>
            </div>
        </dialog>
    @endif
    <script>
        const createModal = document.getElementById('create-docente-modal');
        const editModal = document.getElementById('edit-docente-modal');
        document.querySelectorAll('[data-open-create]').forEach((button) => button.addEventListener('click', () => createModal?.showModal()));
        document.querySelectorAll('[data-close-create]').forEach((button) => button.addEventListener('click', () => createModal?.close()));
        document.querySelectorAll('[data-close-edit]').forEach((button) => button.addEventListener('click', () => editModal?.close()));
        createModal?.addEventListener('click', (event) => {
            const rect = createModal.getBoundingClientRect();
            const inside = rect.top <= event.clientY && event.clientY <= rect.top + rect.height && rect.left <= event.clientX && event.clientX <= rect.left + rect.width;
            if (!inside) createModal.close();
        });
        editModal?.addEventListener('click', (event) => {
            const rect = editModal.getBoundingClientRect();
            const inside = rect.top <= event.clientY && event.clientY <= rect.top + rect.height && rect.left <= event.clientX && event.clientX <= rect.left + rect.width;
            if (!inside) editModal.close();
        });
        @if ($docenteEditando)
            editModal?.showModal();
        @endif
        @if ($errors->any() && ! $docenteEditando)
            createModal?.showModal();
        @endif
    </script>
</body>
</html>
