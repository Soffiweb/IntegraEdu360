<section class="instituciones-content zonas-content catalogo-content" aria-label="Zonas educativas">
            <header class="hero">
                <div>
                    <div class="eyebrow">Módulo estratégico</div>
                    <h1>Zonas educativas</h1>
                    <p>Catálogo de zonas del Ministerio de Educación, Deporte y Cultura y su cobertura territorial.</p>
                </div>
                <div class="hero-side">
                    <strong>Cobertura territorial</strong>
                    <p>Consulte y actualice las zonas educativas desde el panel central.</p>
                </div>
            </header>
            <section class="panel">
                <div class="section-title">
                    <h2>Listado operativo</h2>
                    <span>{{ $zonas->count() }} resultados</span>
                </div>
            @if (session('success'))
                <p class="alert" role="status">{{ session('success') }}</p>
            @endif
            @if ($zonaEditando)
                <form class="zona-form" method="POST" action="{{ route('superusuario.zonas.update', $zonaEditando) }}">
                    @csrf
                    @method('PUT')
                    <h2>Modificar zona</h2>
                    <label for="zona-codigo">Código</label>
                    <input id="zona-codigo" name="codigo" type="number" min="1" max="255" required value="{{ old('codigo', $zonaEditando->codigo) }}" @error('codigo') aria-invalid="true" @enderror>
                    @error('codigo') <span role="alert">{{ $message }}</span> @enderror
                    <label for="zona-nombre">Nombre</label>
                    <input id="zona-nombre" name="nombre" maxlength="255" required value="{{ old('nombre', $zonaEditando->nombre) }}" @error('nombre') aria-invalid="true" @enderror>
                    @error('nombre') <span role="alert">{{ $message }}</span> @enderror
                    <label for="zona-cobertura">Cobertura territorial</label>
                    <textarea id="zona-cobertura" name="cobertura" maxlength="10000" rows="3" required @error('cobertura') aria-invalid="true" @enderror>{{ old('cobertura', $zonaEditando->cobertura) }}</textarea>
                    @error('cobertura') <span role="alert">{{ $message }}</span> @enderror
                    <div class="zona-form-actions">
                        <button class="btn btn-primary icon-btn" type="submit" aria-label="Guardar cambios" title="Guardar cambios"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i></button>
                        <a class="btn mini-btn icon-btn" href="{{ route('superusuario.zonas.index') }}" aria-label="Cancelar" title="Cancelar"><i class="fa-solid fa-xmark" aria-hidden="true"></i></a>
                    </div>
                </form>
            @endif
            <div class="sw-table-wrap">
                <table class="sw-table">
                    <caption>Zonas educativas y cobertura territorial</caption>
                    <thead>
                        <tr><th scope="col">Código</th><th scope="col">Nombre</th><th scope="col">Cobertura territorial</th><th scope="col">Acciones</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($zonas as $zona)
                            <tr><td>{{ $zona->codigo }}</td><td>{{ $zona->nombre }}</td><td>{{ $zona->cobertura }}</td><td><a class="btn mini-btn icon-btn" href="{{ route('superusuario.zonas.index', ['edit' => $zona->id]) }}" aria-label="Modificar {{ $zona->nombre }}" title="Modificar {{ $zona->nombre }}"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></a></td></tr>
                        @empty
                            <tr><td colspan="4">No hay zonas educativas registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            </section>
</section>
