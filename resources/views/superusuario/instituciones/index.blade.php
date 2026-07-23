<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administracion de Instituciones - IntegraEdu360</title>
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
            --accent: #7a2fc1;
            --success: #2c7a4b;
            --muted: #617487;
            --danger: #8f2d2d;
            --shadow-soft: 0 24px 52px rgba(8, 26, 43, 0.12);
            --shadow-card: 0 16px 32px rgba(8, 26, 43, 0.08);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            color: var(--slate-700);
            background:
                radial-gradient(circle at top right, rgba(122, 47, 193, 0.16), transparent 24%),
                linear-gradient(160deg, #eef3f8 0%, #f8fbfd 54%, #eef3f8 100%);
        }

        .app-shell { display: grid; grid-template-columns: 300px minmax(0, 1fr); min-height: 100vh; }
        .sidebar {
            background: linear-gradient(180deg, rgba(8, 26, 43, 0.99), rgba(17, 49, 79, 0.97));
            color: var(--white);
            padding: 28px 22px;
            position: relative;
            overflow: hidden;
        }
        .sidebar::after {
            content: "";
            position: absolute;
            right: -100px;
            bottom: -110px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(122, 47, 193, 0.28), transparent 68%);
        }
        .brand, .profile-card, .sidebar-nav, .sidebar-panel { position: relative; z-index: 1; }
        .brand { display: flex; align-items: center; gap: 14px; margin-bottom: 30px; }
        .brand-mark { width: 52px; height: 52px; border-radius: 16px; background: linear-gradient(145deg, var(--accent), color-mix(in srgb, var(--accent) 45%, white)); color: var(--navy-950); display: grid; place-items: center; font-weight: 900; letter-spacing: .08em; }
        .brand strong { display: block; font-size: 1rem; letter-spacing: .08em; text-transform: uppercase; }
        .brand span, .profile-card p, .sidebar-panel p, .nav-item small { color: rgba(255, 255, 255, .72); }
        .profile-card, .sidebar-panel { background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.1); border-radius: 24px; padding: 20px; backdrop-filter: blur(8px); }
        .profile-card { margin-bottom: 18px; }
        .role-badge { display: inline-flex; align-items: center; justify-content: center; width: 58px; height: 58px; border-radius: 18px; background: linear-gradient(145deg, var(--accent), color-mix(in srgb, var(--accent) 38%, white)); color: var(--navy-950); font-weight: 900; font-size: 1.15rem; margin-bottom: 16px; }
        .profile-card h2, .sidebar-panel h3 { margin: 0 0 12px; }
        .sidebar-nav { display: grid; gap: 10px; margin: 18px 0; }
        .nav-item { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; border-radius: 16px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.06); color: var(--white); text-decoration: none; font-weight: 700; }
        .nav-item.active { background: linear-gradient(90deg, rgba(122, 47, 193, .28), rgba(255,255,255,.06)); border-color: rgba(122, 47, 193, .42); }
        .sidebar-list { display: grid; gap: 12px; margin-top: 14px; }
        .sidebar-list div { padding: 14px; border-radius: 16px; background: rgba(255,255,255,.05); }
        .sidebar-list strong { display: block; margin-bottom: 6px; }
        .main { padding: 28px; }
        .page { width: 100%; max-width: none; margin: 0; padding: 0; }
        .topbar, .panel, .card { background: rgba(255,255,255,.95); border: 1px solid rgba(212,223,235,.9); box-shadow: var(--shadow-card); }
        .topbar { border-radius: 24px; padding: 18px 22px; display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
        .crumbs { display: flex; flex-wrap: wrap; gap: 10px; font-weight: 700; color: var(--slate-500); }
        .crumbs a { color: var(--navy-900); text-decoration: none; }
        .btn, button { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0 16px; border-radius: 14px; border: 1px solid var(--slate-200); background: #fff; color: var(--navy-900); font: inherit; font-weight: 800; text-decoration: none; cursor: pointer; }
        .btn-primary { background: linear-gradient(135deg, var(--navy-900), var(--accent)); border-color: transparent; color: var(--white); box-shadow: 0 16px 28px rgba(17, 49, 79, 0.18); }
        .btn-danger { color: var(--danger); background: #fff6f6; border-color: #f2c9c9; }
        .hero { border-radius: 30px; padding: 28px; margin-bottom: 22px; display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.8fr); gap: 18px; position: relative; overflow: hidden; background: linear-gradient(135deg, rgba(8,26,43,.98), rgba(17,49,79,.95)); color: var(--white); box-shadow: var(--shadow-soft); }
        .hero::after { content: ""; position: absolute; right: -90px; top: -90px; width: 260px; height: 260px; border-radius: 50%; background: radial-gradient(circle, rgba(122,47,193,.28), transparent 68%); }
        .hero > * { position: relative; z-index: 1; }
        .eyebrow { display: inline-flex; padding: 8px 12px; border-radius: 999px; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.12); text-transform: uppercase; letter-spacing: .08em; font-size: .78rem; font-weight: 800; margin-bottom: 14px; }
        .hero h1 { margin: 0 0 10px; font-size: clamp(2rem, 3vw, 3rem); line-height: 1.06; }
        .hero p { margin: 0; color: rgba(255,255,255,.8); line-height: 1.68; }
        .hero-side { background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.12); border-radius: 24px; padding: 22px; }
        .hero-side strong { display: block; margin-bottom: 10px; font-size: 1.05rem; }
        .stats { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 18px; margin-bottom: 22px; }
        .card { border-radius: 24px; padding: 22px; }
        .metric { display: block; color: var(--navy-950); font-size: 2rem; margin-bottom: 8px; }
        .panel { border-radius: 28px; padding: 24px; }
        .section-title { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 18px; }
        .section-title h2 { margin: 0; color: var(--navy-950); font-size: 1.35rem; }
        .section-title span, .card span, .name-cell span, .empty, .page-note { color: var(--slate-500); }
        .toolbar { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 14px; margin-bottom: 18px; }
        .filters, .actions, .pager { display: flex; flex-wrap: wrap; gap: 12px; }
        .filters { flex: 1 1 720px; }
        .actions-bar { display: flex; flex-wrap: wrap; gap: 12px; }
        .input, .select, .textarea { width: 100%; border-radius: 14px; border: 1px solid var(--slate-200); background: #fff; color: var(--slate-700); font: inherit; }
        .input, .select { min-height: 44px; padding: 0 14px; }
        .filters .input { flex: 1 1 280px; }
        .filters .select { flex: 0 1 220px; }
        .alert { margin-bottom: 16px; padding: 14px 16px; border-radius: 16px; background: #edf8f1; border: 1px solid #cce7d5; color: #1f5f36; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 14px 12px; text-align: left; border-bottom: 1px solid var(--slate-100); vertical-align: top; }
        th { color: var(--navy-900); font-size: .8rem; text-transform: uppercase; letter-spacing: .06em; }
        .name-cell strong { display: block; color: var(--navy-900); margin-bottom: 8px; }
        .badge { display: inline-flex; align-items: center; justify-content: center; padding: 6px 10px; border-radius: 999px; font-size: .78rem; font-weight: 800; }
        .badge-activo { background: #e8f7ed; color: var(--success); }
        .badge-inactivo { background: #eef2f6; color: var(--muted); }
        .mini-btn { min-height: 36px; padding: 0 12px; border-radius: 12px; font-size: .9rem; font-weight: 700; }
        .empty { padding: 32px 20px; text-align: center; }
        .pager { justify-content: flex-end; margin-top: 18px; }
        .page-note { display: inline-flex; align-items: center; min-height: 44px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 14px; }
        .field-group { display: grid; gap: 8px; }
        .field-group.full { grid-column: 1 / -1; }
        .field-group label { color: var(--navy-900); font-size: .9rem; font-weight: 700; }
        .textarea { min-height: 120px; padding: 12px 14px; resize: vertical; }
        .errors { margin-bottom: 16px; padding: 14px 16px; border-radius: 16px; background: #fff4f4; border: 1px solid #efcaca; color: var(--danger); }
        .errors ul { margin: 0; padding-left: 18px; }
        dialog.modal { width: min(860px, calc(100% - 24px)); border: 0; border-radius: 28px; padding: 0; box-shadow: var(--shadow-soft); }
        dialog.modal::backdrop { background: rgba(8, 26, 43, 0.45); backdrop-filter: blur(2px); }
        .modal-card { padding: 24px; background: #fff; border-radius: 28px; }
        .modal-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 18px; }
        .modal-header h3 { margin: 0; color: var(--navy-950); font-size: 1.4rem; }
        .modal-close { min-width: 44px; padding: 0; }
        @media (max-width: 1180px) { .app-shell { grid-template-columns: 1fr; } .hero, .stats { grid-template-columns: 1fr; } .toolbar { flex-direction: column; align-items: stretch; } }
        @media (max-width: 720px) {
            .main { padding: 18px; }
            .topbar { flex-direction: column; align-items: flex-start; }
            .form-grid { grid-template-columns: 1fr; }
            table, thead, tbody, th, td, tr { display: block; }
            thead { display: none; }
            tr { padding: 14px 0; border-bottom: 1px solid var(--slate-100); }
            td { padding: 6px 0; border-bottom: 0; }
        }
    </style>
</head>
<body>
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
                <p>Supervision completa de instituciones, usuarios y parametros criticos.</p>
            </div>

            <nav class="sidebar-nav">
                <a class="nav-item" href="{{ route('roles.dashboard', 'superusuario') }}">
                    <span>Resumen ejecutivo</span>
                    <small>Hoy</small>
                </a>
                <a class="nav-item active" href="{{ route('superusuario.instituciones.index') }}">
                    <span>Administracion de instituciones</span>
                    <small>CRUD</small>
                </a>
                <a class="nav-item" href="{{ route('auth.form') }}">
                    <span>Cambiar acceso</span>
                    <small>Menu</small>
                </a>
            </nav>

            <div class="sidebar-panel">
                <h3>Acciones recomendadas</h3>
                <p>Centralice el control de instituciones y mantenga la informacion principal actualizada.</p>
                <div class="sidebar-list">
                    <div>
                        <strong>Revision operativa</strong>
                        <p>Valide datos de contacto y estado institucional.</p>
                    </div>
                    <div>
                        <strong>Alta ordenada</strong>
                        <p>Cree nuevas instituciones con datos minimos confiables.</p>
                    </div>
                </div>
            </div>
        </aside>

        <main class="main">
            <div class="page">
                <div class="topbar">
                    <div class="crumbs">
                        <a href="{{ route('auth.form') }}">Menu principal</a>
                        <span>/</span>
                        <a href="{{ route('roles.dashboard', 'superusuario') }}">Dashboard superusuario</a>
                        <span>/</span>
                        <span>Administracion de instituciones</span>
                    </div>
                    <a class="btn" href="{{ route('roles.dashboard', 'superusuario') }}">Volver al dashboard</a>
                </div>

                <section class="hero">
                    <div>
                        <div class="eyebrow">Modulo estrategico</div>
                        <h1>Administracion de instituciones</h1>
                        <p>Consolide sedes, contactos y datos generales desde un panel centralizado.</p>
                    </div>
                    <div class="hero-side">
                        <strong>Control central</strong>
                        <p>Edite instituciones sin salir del panel y mantenga la operacion uniforme.</p>
                    </div>
                </section>

                <section class="stats">
                    <article class="card"><strong class="metric">{{ $metricas['total'] }}</strong><span>Total instituciones</span></article>
                    <article class="card"><strong class="metric">{{ $metricas['activos'] }}</strong><span>Activas</span></article>
                    <article class="card"><strong class="metric">{{ $metricas['inactivos'] }}</strong><span>Inactivas</span></article>
                </section>

                <section class="panel">
                    <div class="section-title">
                        <h2>Listado operativo</h2>
                        <span>{{ $instituciones->total() }} resultados</span>
                    </div>

                    @if (session('status'))
                        <div class="alert">{{ session('status') }}</div>
                    @endif

                    <div class="toolbar">
                        <form class="filters" method="GET" action="{{ route('superusuario.instituciones.index') }}">
                        <input class="input" type="text" name="search" placeholder="Buscar por nombre, provincia, canton, parroquia, correo o telefono" value="{{ request('search') }}">
                            <select class="select" name="estado">
                                <option value="">Todos los estados</option>
                                @foreach ($estados as $value => $label)
                                    <option value="{{ $value }}" @selected(request('estado') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button class="btn" type="submit">Filtrar</button>
                            <a class="btn" href="{{ route('superusuario.instituciones.index') }}">Limpiar</a>
                        </form>

                        <div class="actions-bar">
                            <button class="btn btn-primary" type="button" data-open-create>Nueva institucion</button>
                        </div>
                    </div>

                    @if ($instituciones->count())
                        <table>
                            <thead>
                                <tr>
                                    <th>Institucion</th>
                                    <th>Contacto</th>
                                    <th>Provincia</th>
                                    <th>Canton</th>
                                    <th>Parroquia</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($instituciones as $institucion)
                                    <tr>
                                        <td class="name-cell">
                                            <strong>{{ $institucion->nombre }}</strong>
                                            <span>AMIE: {{ $institucion->codigo_amie }} | {{ $institucion->email ?: 'Sin correo' }}</span>
                                        </td>
                                        <td>{{ $institucion->telefono ?: 'Sin telefono' }}</td>
                                        <td>{{ $institucion->provincia ?: 'No registrado' }}</td>
                                        <td>{{ $institucion->canton ?: 'No registrado' }}</td>
                                        <td>{{ $institucion->parroquia ?: 'No registrado' }}</td>
                                        <td><span class="badge badge-{{ strtolower($institucion->estado) }}">{{ $estados[$institucion->estado] ?? $institucion->estado }}</span></td>
                                        <td>
                                            <div class="actions">
                                                <a class="btn mini-btn" href="{{ route('superusuario.instituciones.index', array_filter(['edit' => $institucion->id, 'search' => request('search'), 'estado' => request('estado')])) }}">Editar</a>
                                                <form method="POST" action="{{ route('superusuario.instituciones.destroy', $institucion) }}" onsubmit="return confirm('Desea eliminar esta institucion?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn mini-btn btn-danger" type="submit">Eliminar</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="pager">
                            @if ($instituciones->onFirstPage())
                                <span class="btn">Anterior</span>
                            @else
                                <a class="btn" href="{{ $instituciones->previousPageUrl() }}">Anterior</a>
                            @endif
                            <span class="page-note">Pagina {{ $instituciones->currentPage() }} de {{ $instituciones->lastPage() }}</span>
                            @if ($instituciones->hasMorePages())
                                <a class="btn" href="{{ $instituciones->nextPageUrl() }}">Siguiente</a>
                            @else
                                <span class="btn">Siguiente</span>
                            @endif
                        </div>
                    @else
                        <div class="empty">No hay instituciones con los filtros actuales.</div>
                    @endif
                </section>
            </div>
        </main>
    </div>

    <dialog class="modal" id="create-institucion-modal">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <h3>Nueva institucion</h3>
                    <span>Registre una institucion con datos basicos.</span>
                </div>
                <button class="btn modal-close" type="button" data-close-create>x</button>
            </div>

            @if ($errors->any() && ! $institucionEditando)
                <div class="errors">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="create-institucion-form" method="POST" action="{{ route('superusuario.instituciones.store') }}">
                @csrf
                <div class="form-grid">
                    <div class="field-group">
                        <label for="create_codigo_amie">Codigo AMIE</label>
                        <input id="create_codigo_amie" class="input" type="text" name="codigo_amie" value="{{ old('codigo_amie') }}" required>
                    </div>
                    <div class="field-group full">
                        <label for="create_nombre">Nombre</label>
                        <input id="create_nombre" class="input" type="text" name="nombre" value="{{ old('nombre') }}" required>
                    </div>
                    <div class="field-group">
                        <label for="create_sostenimiento_id">Sostenimiento</label>
                        <select id="create_sostenimiento_id" class="select" name="sostenimiento_id" required>
                            <option value="">Seleccione</option>
                            @foreach ($sostenimientos as $value => $label)
                                <option value="{{ $value }}" @selected((string) old('sostenimiento_id') === (string) $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field-group">
                        <label for="create_regimen_id">Regimen escolar</label>
                        <select id="create_regimen_id" class="select" name="regimen_id" required>
                            <option value="">Seleccione</option>
                            @foreach ($regimenes as $value => $label)
                                <option value="{{ $value }}" @selected((string) old('regimen_id') === (string) $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field-group">
                        <label for="create_provincia">Provincia</label>
                        <input id="create_provincia" class="input" type="text" name="provincia" value="{{ old('provincia') }}" required>
                    </div>
                    <div class="field-group">
                        <label for="create_canton">Canton</label>
                        <input id="create_canton" class="input" type="text" name="canton" value="{{ old('canton') }}" required>
                    </div>
                    <div class="field-group">
                        <label for="create_parroquia">Parroquia</label>
                        <input id="create_parroquia" class="input" type="text" name="parroquia" value="{{ old('parroquia') }}">
                    </div>
                    <div class="field-group full">
                        <label for="create_direccion">Direccion</label>
                        <input id="create_direccion" class="input" type="text" name="direccion" value="{{ old('direccion') }}">
                    </div>
                    <div class="field-group">
                        <label for="create_telefono">Telefono</label>
                        <input id="create_telefono" class="input" type="text" name="telefono" value="{{ old('telefono') }}">
                    </div>
                    <div class="field-group">
                        <label for="create_email">Correo</label>
                        <input id="create_email" class="input" type="email" name="email" value="{{ old('email') }}">
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
                    <button class="btn btn-primary" type="submit" form="create-institucion-form">Crear institucion</button>
                    <button class="btn" type="button" data-close-create>Cancelar</button>
                </div>
            </form>
        </div>
    </dialog>

    @if ($institucionEditando)
        <dialog class="modal" id="edit-institucion-modal">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h3>Editar institucion</h3>
                        <span>Actualice los datos principales.</span>
                    </div>
                    <button class="btn modal-close" type="button" data-close-edit>x</button>
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

                <form id="edit-institucion-form" method="POST" action="{{ route('superusuario.instituciones.update', $institucionEditando) }}">
                    @csrf
                    @method('PUT')
                    <div class="form-grid">
                        <div class="field-group">
                            <label for="edit_codigo_amie">Codigo AMIE</label>
                            <input id="edit_codigo_amie" class="input" type="text" name="codigo_amie" value="{{ old('codigo_amie', $institucionEditando->codigo_amie) }}" required>
                        </div>
                        <div class="field-group full">
                            <label for="edit_nombre">Nombre</label>
                            <input id="edit_nombre" class="input" type="text" name="nombre" value="{{ old('nombre', $institucionEditando->nombre) }}" required>
                        </div>
                        <div class="field-group">
                            <label for="edit_sostenimiento_id">Sostenimiento</label>
                            <select id="edit_sostenimiento_id" class="select" name="sostenimiento_id" required>
                                <option value="">Seleccione</option>
                                @foreach ($sostenimientos as $value => $label)
                                    <option value="{{ $value }}" @selected((string) old('sostenimiento_id', $institucionEditando->sostenimiento_id) === (string) $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group">
                            <label for="edit_regimen_id">Regimen escolar</label>
                            <select id="edit_regimen_id" class="select" name="regimen_id" required>
                                <option value="">Seleccione</option>
                                @foreach ($regimenes as $value => $label)
                                    <option value="{{ $value }}" @selected((string) old('regimen_id', $institucionEditando->regimen_id) === (string) $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group">
                            <label for="edit_provincia">Provincia</label>
                            <input id="edit_provincia" class="input" type="text" name="provincia" value="{{ old('provincia', $institucionEditando->provincia) }}" required>
                        </div>
                        <div class="field-group">
                            <label for="edit_canton">Canton</label>
                            <input id="edit_canton" class="input" type="text" name="canton" value="{{ old('canton', $institucionEditando->canton) }}" required>
                        </div>
                        <div class="field-group">
                            <label for="edit_parroquia">Parroquia</label>
                            <input id="edit_parroquia" class="input" type="text" name="parroquia" value="{{ old('parroquia', $institucionEditando->parroquia) }}">
                        </div>
                        <div class="field-group full">
                            <label for="edit_direccion">Direccion</label>
                            <input id="edit_direccion" class="input" type="text" name="direccion" value="{{ old('direccion', $institucionEditando->direccion) }}">
                        </div>
                        <div class="field-group">
                            <label for="edit_telefono">Telefono</label>
                            <input id="edit_telefono" class="input" type="text" name="telefono" value="{{ old('telefono', $institucionEditando->telefono) }}">
                        </div>
                        <div class="field-group">
                            <label for="edit_email">Correo</label>
                            <input id="edit_email" class="input" type="email" name="email" value="{{ old('email', $institucionEditando->email) }}">
                        </div>
                        <div class="field-group">
                            <label for="edit_estado">Estado</label>
                            <select id="edit_estado" class="select" name="estado" required>
                                @foreach ($estados as $value => $label)
                                    <option value="{{ $value }}" @selected(old('estado', $institucionEditando->estado) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="actions" style="margin-top: 18px;">
                        <button class="btn btn-primary" type="submit" form="edit-institucion-form">Guardar cambios</button>
                        <a class="btn" href="{{ route('superusuario.instituciones.index') }}">Cancelar edicion</a>
                    </div>
                </form>
            </div>
        </dialog>
    @endif

    <script>
        const createModal = document.getElementById('create-institucion-modal');
        const editModal = document.getElementById('edit-institucion-modal');
        const createForm = document.getElementById('create-institucion-form');
        const editForm = document.getElementById('edit-institucion-form');
        const openCreateButtons = document.querySelectorAll('[data-open-create]');
        const closeCreateButtons = document.querySelectorAll('[data-close-create]');
        const closeEditButtons = document.querySelectorAll('[data-close-edit]');

        openCreateButtons.forEach((button) => {
            button.addEventListener('click', () => createModal.showModal());
        });

        closeCreateButtons.forEach((button) => {
            button.addEventListener('click', () => createModal.close());
        });

        closeEditButtons.forEach((button) => {
            button.addEventListener('click', () => editModal?.close());
        });

        const syncFormFields = (form) => {
            if (!form) {
                return;
            }

            ['codigo_amie', 'sostenimiento_id', 'regimen_id', 'provincia', 'canton', 'estado'].forEach((name) => {
                const field = form.querySelector(`[name="${name}"]`);

                if (!field) {
                    return;
                }

                if (field.tagName === 'SELECT') {
                    field.value = field.options[field.selectedIndex]?.value ?? field.value;
                    return;
                }

                field.value = field.value.trim();
            });
        };

        createForm?.addEventListener('submit', () => syncFormFields(createForm));
        editForm?.addEventListener('submit', () => syncFormFields(editForm));

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

        @if ($institucionEditando)
            editModal?.showModal();
        @endif

        @if ($errors->any() && ! $institucionEditando)
            createModal.showModal();
        @endif
    </script>
</body>
</html>
