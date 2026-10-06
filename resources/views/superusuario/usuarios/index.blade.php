<section class="instituciones-content zonas-content catalogo-content" aria-label="Gestión de Usuarios">
    <header class="hero">
        <div>
            <div class="eyebrow">Módulo estratégico</div>
            <h1>Gestión de Usuarios</h1>
            <p>Consulte todos los usuarios con rol de administrador de la plataforma.</p>
        </div>
        <div class="hero-side">
            <strong>Administradores</strong>
            <p>Revise sus datos de contacto, institución y estado de la cuenta.</p>
        </div>
    </header>

    <section class="panel">
        <div class="section-title">
            <h2>Listado operativo</h2>
            <span>{{ $usuariosAdministradores->total() }} resultados</span>
        </div>
        <div class="toolbar">
            <form class="distritos-filter filters" method="GET" action="{{ route('superusuario.usuarios.index') }}">
                <div class="field-group">
                    <label for="usuarios-busqueda">Buscar usuario</label>
                    <input class="input" id="usuarios-busqueda" name="q" type="search" maxlength="255" value="{{ $filtrosUsuarios['q'] }}" placeholder="AMIE, usuario, nombre, correo o institución">
                    @error('q') <span role="alert">{{ $message }}</span> @enderror
                </div>
                <div class="field-group">
                    <label for="usuarios-estado">Estado</label>
                    <select class="select" id="usuarios-estado" name="estado">
                        <option value="">Todos los estados</option>
                        @foreach (['ACTIVO' => 'Activo', 'INACTIVO' => 'Inactivo', 'OBSERVACION' => 'Observación'] as $estado => $etiqueta)
                            <option value="{{ $estado }}" @selected($filtrosUsuarios['estado'] === $estado)>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                    @error('estado') <span role="alert">{{ $message }}</span> @enderror
                </div>
                <button class="btn btn-primary icon-btn" type="submit" aria-label="Buscar" title="Buscar"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
                <a class="btn icon-btn" href="{{ route('superusuario.usuarios.index') }}" aria-label="Limpiar filtros" title="Limpiar filtros"><i class="fa-solid fa-filter-circle-xmark" aria-hidden="true"></i></a>
            </form>
        </div>
        @if (session('success'))
            <div class="alert" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="errors" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        @if ($usuarioEditando)
            <form class="zona-form" method="POST" action="{{ route('superusuario.usuarios.update', $usuarioEditando) }}">
                @csrf
                @method('PUT')
                <h2>Editar usuario — {{ $usuarioEditando->username }}</h2>
                <label for="usuario-username">Usuario</label>
                <input id="usuario-username" name="username" maxlength="255" required value="{{ old('username', $usuarioEditando->username) }}">
                <label for="usuario-email">Correo electrónico</label>
                <input id="usuario-email" name="email" type="email" maxlength="255" value="{{ old('email', $usuarioEditando->email) }}">
                <label for="usuario-password">Nueva contraseña (opcional)</label>
                <input id="usuario-password" name="password" type="password" minlength="8" maxlength="255" autocomplete="new-password" aria-describedby="usuario-password-ayuda">
                <span id="usuario-password-ayuda">Deje este campo vacío para conservar la contraseña actual.</span>
                <label for="usuario-password-confirmation">Confirmar nueva contraseña</label>
                <input id="usuario-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password">
                <div class="zona-form-actions">
                    <button class="btn btn-primary icon-btn" type="submit" aria-label="Guardar cambios" title="Guardar cambios"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i></button>
                    <a class="btn icon-btn" href="{{ route('superusuario.usuarios.index', request()->only(['q', 'estado', 'page'])) }}" aria-label="Cancelar" title="Cancelar"><i class="fa-solid fa-xmark" aria-hidden="true"></i></a>
                </div>
            </form>
        @endif
        <div class="sw-table-wrap">
            <table class="sw-table">
                <caption>Usuarios con rol de administrador</caption>
                <thead>
                    <tr>
                        <th scope="col">Usuario</th>
                        <th scope="col">Nombre completo</th>
                        <th scope="col">Correo electrónico</th>
                        <th scope="col">Institución</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Último acceso</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($usuariosAdministradores as $usuarioAdministrador)
                        <tr>
                            <td class="name-cell"><strong>{{ $usuarioAdministrador->username }}</strong></td>
                            <td>{{ $usuarioAdministrador->nombre_completo }}</td>
                            <td>{{ $usuarioAdministrador->email ?: 'Sin correo registrado' }}</td>
                            <td>{{ $usuarioAdministrador->institucion?->nombre ?: 'Sin institución asignada' }}</td>
                            <td>{{ $usuarioAdministrador->estado }}</td>
                            <td>{{ $usuarioAdministrador->ultimo_acceso?->format('d/m/Y H:i') ?? 'Sin accesos registrados' }}</td>
                            <td>
                                <div class="actions">
                                    <a class="btn mini-btn icon-btn" href="{{ route('superusuario.usuarios.index', array_merge(request()->only(['q', 'estado', 'page']), ['edit' => $usuarioAdministrador->id])) }}" aria-label="Editar {{ $usuarioAdministrador->username }}" title="Editar {{ $usuarioAdministrador->username }}"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></a>
                                    @if (strtoupper($usuarioAdministrador->estado) === 'INACTIVO')
                                        <form method="POST" action="{{ route('superusuario.usuarios.desbloquear', $usuarioAdministrador) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn mini-btn icon-btn" type="submit" aria-label="Desbloquear {{ $usuarioAdministrador->username }}" title="Desbloquear {{ $usuarioAdministrador->username }}"><i class="fa-solid fa-unlock" aria-hidden="true"></i></button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('superusuario.usuarios.bloquear', $usuarioAdministrador) }}" onsubmit="return confirm('¿Desea bloquear el acceso de este usuario?');">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn mini-btn btn-danger icon-btn" type="submit" aria-label="Bloquear {{ $usuarioAdministrador->username }}" title="Bloquear {{ $usuarioAdministrador->username }}"><i class="fa-solid fa-lock" aria-hidden="true"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty">{{ $filtrosUsuarios['q'] !== '' || $filtrosUsuarios['estado'] !== '' ? 'No se encontraron administradores con los filtros seleccionados.' : 'No hay usuarios con rol de administrador registrados.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($usuariosAdministradores->hasPages())
            <nav class="distritos-pagination" aria-label="Páginas de usuarios">
                @if ($usuariosAdministradores->onFirstPage())
                    <span class="btn icon-btn" aria-label="Anterior" title="Anterior" aria-disabled="true"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></span>
                @else
                    <a href="{{ $usuariosAdministradores->previousPageUrl() }}" class="btn icon-btn" aria-label="Anterior" title="Anterior"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></a>
                @endif
                <span>Página {{ $usuariosAdministradores->currentPage() }} de {{ $usuariosAdministradores->lastPage() }}</span>
                @if ($usuariosAdministradores->hasMorePages())
                    <a href="{{ $usuariosAdministradores->nextPageUrl() }}" class="btn icon-btn" aria-label="Siguiente" title="Siguiente"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></a>
                @else
                    <span class="btn icon-btn" aria-label="Siguiente" title="Siguiente" aria-disabled="true"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></span>
                @endif
            </nav>
        @endif
    </section>
</section>
