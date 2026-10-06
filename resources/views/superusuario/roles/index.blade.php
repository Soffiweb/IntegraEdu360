<section class="instituciones-content zonas-content catalogo-content" aria-label="Roles de Usuario">
    <header class="hero">
        <div>
            <div class="eyebrow">Módulo estratégico</div>
            <h1>Roles de Usuario</h1>
            <p>Consulte los roles disponibles para las cuentas de la plataforma.</p>
        </div>
        <div class="hero-side">
            <strong>Organización de usuarios</strong>
            <p>Identifique cada rol por su código y nombre.</p>
        </div>
    </header>

    <section class="panel">
        <div class="section-title">
            <h2>Listado operativo</h2>
            <span>{{ $rolesUsuario->count() }} resultados</span>
            <a class="btn btn-primary icon-btn" href="{{ route('superusuario.roles.index', ['create' => 1]) }}" aria-label="Agregar Nuevo" title="Agregar Nuevo"><i class="fa-solid fa-plus" aria-hidden="true"></i></a>
        </div>
        @if (session('success'))
            <div class="alert" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="errors" role="alert">
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @if ($crearRol || $rolEditando)
            <form class="zona-form" method="POST" action="{{ $rolEditando ? route('superusuario.roles.update', $rolEditando) : route('superusuario.roles.store') }}">
                @csrf
                @if ($rolEditando) @method('PUT') @endif
                <h2>{{ $rolEditando ? 'Editar rol' : 'Agregar Nuevo' }}</h2>
                <label for="rol-codigo">Código</label>
                <input id="rol-codigo" name="codigo" maxlength="255" required value="{{ old('codigo', $rolEditando?->codigo) }}" @if ($rolEditando) readonly aria-describedby="rol-codigo-ayuda" @endif @error('codigo') aria-invalid="true" @enderror>
                @if ($rolEditando)
                    <span id="rol-codigo-ayuda">El código identifica los accesos del rol y se conserva al editar.</span>
                @else
                    <span>Use letras mayúsculas, números o guion bajo.</span>
                @endif
                <label for="rol-nombre">Nombre del rol</label>
                <input id="rol-nombre" name="nombre" maxlength="255" required value="{{ old('nombre', $rolEditando?->nombre) }}" @error('nombre') aria-invalid="true" @enderror>
                <div class="zona-form-actions">
                    <button class="btn btn-primary icon-btn" type="submit" aria-label="Guardar" title="Guardar"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i></button>
                    <a class="btn icon-btn" href="{{ route('superusuario.roles.index') }}" aria-label="Cancelar" title="Cancelar"><i class="fa-solid fa-xmark" aria-hidden="true"></i></a>
                </div>
            </form>
        @endif
        <div class="sw-table-wrap">
            <table class="sw-table">
                <caption>Catálogo de roles de usuario</caption>
                <thead>
                    <tr>
                        <th scope="col">Código</th>
                        <th scope="col">Nombre del rol</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rolesUsuario as $rolUsuario)
                        <tr>
                            <td class="name-cell"><strong>{{ $rolUsuario->codigo }}</strong></td>
                            <td>{{ $rolUsuario->nombre }}</td>
                            <td>
                                <div class="actions">
                                    <a class="btn mini-btn icon-btn" href="{{ route('superusuario.roles.index', ['edit' => $rolUsuario->id]) }}" aria-label="Editar {{ $rolUsuario->nombre }}" title="Editar {{ $rolUsuario->nombre }}"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></a>
                                    <form method="POST" action="{{ route('superusuario.roles.destroy', $rolUsuario) }}" onsubmit="return confirm('¿Desea eliminar este rol?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn mini-btn btn-danger icon-btn" type="submit" aria-label="Eliminar {{ $rolUsuario->nombre }}" title="Eliminar {{ $rolUsuario->nombre }}"><i class="fa-solid fa-trash-can" aria-hidden="true"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty">No hay roles de usuario registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</section>
