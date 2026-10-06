<section class="instituciones-content" aria-label="Instituciones educativas">
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
                            <button class="btn icon-btn" type="submit" aria-label="Filtrar" title="Filtrar"><i class="fa-solid fa-filter" aria-hidden="true"></i></button>
                            <a class="btn icon-btn" href="{{ route('superusuario.instituciones.index') }}" aria-label="Limpiar" title="Limpiar"><i class="fa-solid fa-filter-circle-xmark" aria-hidden="true"></i></a>
                        </form>

                        <div class="actions-bar">
                            <button class="btn btn-primary icon-btn" type="button" data-open-create aria-label="Nueva institucion" title="Nueva institucion"><i class="fa-solid fa-plus" aria-hidden="true"></i></button>
                        </div>
                    </div>

                    @if ($instituciones->count())
                        <table>
                            <thead>
                                <tr>
                                    <th>Institucion</th>
                                    <th>Distrito educativo</th>
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
                                        <td>{{ $institucion->distrito?->codigo ?? 'Sin asignar' }}<br>{{ $institucion->distrito?->zona?->nombre }}</td>
                                        <td>{{ $institucion->telefono ?: 'Sin telefono' }}</td>
                                        <td>{{ $institucion->provincia ?: 'No registrado' }}</td>
                                        <td>{{ $institucion->canton ?: 'No registrado' }}</td>
                                        <td>{{ $institucion->parroquia ?: 'No registrado' }}</td>
                                        <td><span class="badge badge-{{ strtolower($institucion->estado) }}">{{ $estados[$institucion->estado] ?? $institucion->estado }}</span></td>
                                        <td>
                                            <div class="actions">
                                                <a class="btn mini-btn icon-btn" href="{{ route('superusuario.instituciones.index', array_filter(['edit' => $institucion->id, 'search' => request('search'), 'estado' => request('estado')])) }}" aria-label="Editar" title="Editar"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></a>
                                                <form method="POST" action="{{ route('superusuario.instituciones.destroy', $institucion) }}" onsubmit="return confirm('Desea eliminar esta institucion?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn mini-btn btn-danger icon-btn" type="submit" aria-label="Eliminar" title="Eliminar"><i class="fa-solid fa-trash-can" aria-hidden="true"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="pager">
                            @if ($instituciones->onFirstPage())
                                <span class="btn icon-btn" aria-label="Anterior" title="Anterior" aria-disabled="true"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></span>
                            @else
                                <a class="btn icon-btn" href="{{ $instituciones->previousPageUrl() }}" aria-label="Anterior" title="Anterior"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></a>
                            @endif
                            <span class="page-note">Pagina {{ $instituciones->currentPage() }} de {{ $instituciones->lastPage() }}</span>
                            @if ($instituciones->hasMorePages())
                                <a class="btn icon-btn" href="{{ $instituciones->nextPageUrl() }}" aria-label="Siguiente" title="Siguiente"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></a>
                            @else
                                <span class="btn icon-btn" aria-label="Siguiente" title="Siguiente" aria-disabled="true"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></span>
                            @endif
                        </div>
                    @else
                        <div class="empty">No hay instituciones con los filtros actuales.</div>
                    @endif
                </section>
    <dialog class="modal" id="create-institucion-modal" data-open="{{ $errors->any() && ! $institucionEditando ? 'true' : 'false' }}">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <h3>Nueva institucion</h3>
                    <span>Registre una institucion con datos basicos.</span>
                </div>
                <button class="btn modal-close icon-btn" type="button" data-close-create aria-label="Cerrar" title="Cerrar"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
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
                    <div class="field-group full">
                        <label for="create_distrito_id">Distrito educativo</label>
                        <select id="create_distrito_id" class="select" name="distrito_id">
                            <option value="">Sin asignar</option>
                            @foreach ($distritos as $distrito)
                                <option value="{{ $distrito->id }}" @selected((string) old('distrito_id') === (string) $distrito->id)>{{ $distrito->zona->nombre }} — {{ $distrito->codigo }} — {{ $distrito->nombre }}</option>
                            @endforeach
                        </select>
                        @error('distrito_id') <span role="alert">{{ $message }}</span> @enderror
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
                    <button class="btn btn-primary icon-btn" type="submit" form="create-institucion-form" aria-label="Crear institucion" title="Crear institucion"><i class="fa-solid fa-plus" aria-hidden="true"></i></button>
                    <button class="btn icon-btn" type="button" data-close-create aria-label="Cancelar" title="Cancelar"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
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
                    <button class="btn modal-close icon-btn" type="button" data-close-edit aria-label="Cerrar" title="Cerrar"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
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
                        <div class="field-group full">
                            <label for="edit_distrito_id">Distrito educativo</label>
                            <select id="edit_distrito_id" class="select" name="distrito_id">
                                <option value="">Sin asignar</option>
                                @foreach ($distritos as $distrito)
                                    <option value="{{ $distrito->id }}" @selected((string) old('distrito_id', $institucionEditando->distrito_id) === (string) $distrito->id)>{{ $distrito->zona->nombre }} — {{ $distrito->codigo }} — {{ $distrito->nombre }}</option>
                                @endforeach
                            </select>
                            @error('distrito_id') <span role="alert">{{ $message }}</span> @enderror
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
                        <button class="btn btn-primary icon-btn" type="submit" form="edit-institucion-form" aria-label="Guardar cambios" title="Guardar cambios"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i></button>
                        <a class="btn icon-btn" href="{{ route('superusuario.instituciones.index') }}" aria-label="Cancelar edicion" title="Cancelar edicion"><i class="fa-solid fa-xmark" aria-hidden="true"></i></a>
                    </div>
                </form>
            </div>
        </dialog>
    @endif

</section>
<script src="{{ asset('js/instituciones.js') }}" defer></script>
