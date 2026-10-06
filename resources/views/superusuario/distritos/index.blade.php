<section class="instituciones-content zonas-content catalogo-content" aria-label="Distritos Educativos">
    <header class="hero">
        <div>
            <div class="eyebrow">Módulo estratégico</div>
            <h1>Distritos Educativos</h1>
            <p>Seleccione una zona educativa para consultar sus distritos.</p>
        </div>
        <div class="hero-side">
            <strong>Organización territorial</strong>
            <p>Consulte, edite y genere reportes de los distritos de cada zona.</p>
        </div>
    </header>

    <section class="panel">
        <div class="section-title">
            <h2>Listado operativo</h2>
            <span>{{ $distritos?->total() ?? 0 }} resultados</span>
        </div>
        <div class="toolbar">
    <form class="distritos-filter filters" method="GET" action="{{ route('superusuario.distritos.index') }}">
        <div>
            <label for="distritos-zona">Zona educativa</label>
            <select id="distritos-zona" name="zona_id" required>
                <option value="">Seleccione una zona</option>
                @foreach ($zonas as $zona)
                    <option value="{{ $zona->id }}" @selected($zonaSeleccionada?->id === $zona->id)>{{ $zona->nombre }}</option>
                @endforeach
            </select>
            @error('zona_id') <span role="alert">{{ $message }}</span> @enderror
        </div>
        <button class="btn btn-primary icon-btn" type="submit" aria-label="Mostrar distritos" title="Mostrar distritos"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
    </form>

        @if ($zonaSeleccionada)
            <div class="actions-bar">
                <a class="btn btn-primary icon-btn" href="{{ route('superusuario.distritos.pdf', $zonaSeleccionada) }}" aria-label="Generar reporte PDF" title="Generar reporte PDF"><i class="fa-solid fa-file-pdf" aria-hidden="true"></i></a>
            </div>
        @endif
        </div>

    @if (session('success'))
        <p class="alert" role="status">{{ session('success') }}</p>
    @endif

    @if ($distritoEditando)
        <form class="zona-form" method="POST" action="{{ route('superusuario.distritos.update', ['zona' => $zonaSeleccionada, 'distrito' => $distritoEditando]) }}">
            @csrf
            @method('PUT')
            <h2>Editar distrito — {{ $zonaSeleccionada->nombre }}</h2>
            <label for="distrito-codigo">Código</label>
            <input id="distrito-codigo" name="codigo" maxlength="5" pattern="[0-9]{2}D[0-9]{2}" required value="{{ old('codigo', $distritoEditando->codigo) }}" @error('codigo') aria-invalid="true" @enderror>
            @error('codigo') <span role="alert">{{ $message }}</span> @enderror
            <label for="distrito-nombre">Nombre</label>
            <textarea id="distrito-nombre" name="nombre" maxlength="255" rows="3" required @error('nombre') aria-invalid="true" @enderror>{{ old('nombre', $distritoEditando->nombre) }}</textarea>
            @error('nombre') <span role="alert">{{ $message }}</span> @enderror
            <label for="distrito-provincia">Provincia</label>
            <input id="distrito-provincia" name="provincia" maxlength="100" required value="{{ old('provincia', $distritoEditando->provincia) }}" @error('provincia') aria-invalid="true" @enderror>
            @error('provincia') <span role="alert">{{ $message }}</span> @enderror
            <div class="zona-form-actions">
                <button class="btn btn-primary icon-btn" type="submit" aria-label="Guardar cambios" title="Guardar cambios"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i></button>
                <a class="btn mini-btn icon-btn" href="{{ route('superusuario.distritos.index', ['zona_id' => $zonaSeleccionada->id]) }}" aria-label="Cancelar" title="Cancelar"><i class="fa-solid fa-xmark" aria-hidden="true"></i></a>
            </div>
        </form>
    @endif

    @if ($zonaSeleccionada)
        <div class="sw-table-wrap">
            <table class="sw-table">
                <caption>Distritos de {{ $zonaSeleccionada->nombre }}</caption>
                <thead>
                    <tr>
                        <th scope="col">Código</th>
                        <th scope="col">Provincia</th>
                        <th scope="col">Nombre del distrito</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($distritos as $distrito)
                        <tr>
                            <td>{{ $distrito->codigo }}</td>
                            <td>{{ $distrito->provincia }}</td>
                            <td>{{ $distrito->nombre }}</td>
                            <td><a class="btn mini-btn icon-btn" href="{{ route('superusuario.distritos.index', ['zona_id' => $zonaSeleccionada->id, 'edit' => $distrito->id]) }}" aria-label="Editar distrito {{ $distrito->codigo }}" title="Editar distrito {{ $distrito->codigo }}"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No hay distritos registrados para esta zona educativa.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($distritos->hasPages())
            <nav class="distritos-pagination" aria-label="Páginas de distritos">
                @if ($distritos->onFirstPage())
                    <span class="btn icon-btn" aria-label="Anterior" title="Anterior" aria-disabled="true"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></span>
                @else
                    <a href="{{ $distritos->previousPageUrl() }}" class="btn icon-btn" aria-label="Anterior" title="Anterior"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></a>
                @endif
                <span>Página {{ $distritos->currentPage() }} de {{ $distritos->lastPage() }}</span>
                @if ($distritos->hasMorePages())
                    <a href="{{ $distritos->nextPageUrl() }}" class="btn icon-btn" aria-label="Siguiente" title="Siguiente"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></a>
                @else
                    <span class="btn icon-btn" aria-label="Siguiente" title="Siguiente" aria-disabled="true"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></span>
                @endif
            </nav>
        @endif
        <section class="distritos-summary" aria-label="Resumen estadístico">
            <h2>Resumen estadístico — {{ $zonaSeleccionada->nombre }}</h2>
            <p>{{ $distritos->total() }} distritos · {{ $porProvincia->count() }} provincias. Totales de toda la zona.</p>
            <ul>
                @foreach ($porProvincia as $provincia => $total)
                    <li>{{ $provincia }}: {{ $total }} {{ $total == 1 ? 'distrito' : 'distritos' }}</li>
                @endforeach
            </ul>
        </section>
    @else
        <p class="distritos-empty" role="status">Seleccione una zona para mostrar únicamente los distritos que le corresponden.</p>
    @endif
    </section>
</section>
