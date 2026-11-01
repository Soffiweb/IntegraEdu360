<section class="distributivo-personal asistencia-personal">
    <header class="page-header asistencia-encabezado"><div class="page-header-copy">
        <h1>Registrar asistencia</h1>
        <p>Registre la asistencia y el comportamiento de sus estudiantes en cada período de clase.</p>
    </div>
        <a class="asistencia-volver" href="{{ route('docente.asistencia.index', array_filter(['periodo' => $periodo?->id, 'carga' => $carga?->id])) }}">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Clases registradas</a>
    </header>
    @if(session('success'))
        <p class="sw-alert sw-alert-success" role="status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ session('success') }}</p>
    @endif
    @if($errors->any())
        <div class="sw-alert sw-alert-error" role="alert"><p>No se guardaron los cambios. Revise lo siguiente:</p>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <form method="GET" action="{{ route('docente.asistencia.registro') }}" class="asistencia-filtros">
        <label for="asistencia-periodo">Período académico</label>
        <select name="periodo" id="asistencia-periodo" data-autosubmit>
            @forelse($periodos as $opcion)<option value="{{ $opcion->id }}" @selected($periodo?->id === $opcion->id)>{{ $opcion->nombre }}</option>
            @empty<option value="">Sin períodos</option>@endforelse
        </select>
        <noscript><button class="sw-btn sw-btn-primary" type="submit">Cambiar período</button></noscript>
    </form>
    @if($periodo)
        <form method="GET" action="{{ route('docente.asistencia.registro') }}" class="asistencia-filtros">
            <input type="hidden" name="periodo" value="{{ $periodo->id }}">
            <label for="asistencia-carga">Asignatura · curso · paralelo</label>
            <select name="carga" id="asistencia-carga" class="asistencia-carga">
                @forelse($cargas as $opcion)
                    <option value="{{ $opcion->id }}" @selected($carga?->id === $opcion->id)>{{ $opcion->asignatura?->nombre ?? $opcion->titulo }} · {{ $opcion->curso->nombre }} {{ $opcion->paralelo->letra }} · {{ $opcion->curso->especialidad?->nombre ?? 'EGB' }} ({{ number_format((float) $opcion->horas_semanales, 2, ',', '.') }} períodos/semana)</option>
                @empty<option value="">Sin carga horaria con paralelo asignado</option>@endforelse
            </select>
            <label for="asistencia-fecha">Fecha de la clase</label>
            <input type="date" name="fecha" id="asistencia-fecha" value="{{ $fecha->toDateString() }}"
                   min="{{ $periodo->fecha_inicio->toDateString() }}" max="{{ $periodo->fecha_fin->toDateString() }}" required>
            <button class="sw-btn sw-btn-primary" type="submit">Consultar</button>
        </form>
    @endif
    @if($carga)
        <h2 class="asistencia-subtitulo">Resumen de asistencia del período</h2>
        <div class="asistencia-resumen">
            <div class="asistencia-stat asistencia-stat--info"><span class="asistencia-stat-icono"><i class="fa-solid fa-list" aria-hidden="true"></i></span>
                <div><p>Total clases</p><strong>{{ $resumen['clases'] }}</strong></div></div>
            <div class="asistencia-stat asistencia-stat--error"><span class="asistencia-stat-icono"><i class="fa-solid fa-users" aria-hidden="true"></i></span>
                <div><p>Total alumnos</p><strong>{{ $resumen['alumnos'] }}</strong></div></div>
            <div class="asistencia-stat asistencia-stat--success"><span class="asistencia-stat-icono"><i class="fa-solid fa-percent" aria-hidden="true"></i></span>
                <div><p>Asistencia promedio</p><strong>{{ $resumen['promedio'] === null ? '—' : number_format($resumen['promedio'], 2, ',', '.').' %' }}</strong></div></div>
            <div class="asistencia-stat asistencia-stat--warning"><span class="asistencia-stat-icono"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span>
                <div><p>Alertas</p><strong>{{ $resumen['alertas'] }}</strong><small>Menos de {{ number_format(\App\Services\Docente\AlertasAsistencia::UMBRAL, 0) }} % de asistencia</small></div></div>
        </div>
        <p class="distributivo-nota">
            {{ $sesion ? 'Esta clase ya tiene registro; puede corregirlo.' : 'Clase nueva: se registrará al guardar.' }}
            Toque una letra para marcar la asistencia; tóquela de nuevo para dejarla sin registrar. Las observaciones describen el comportamiento del estudiante en la clase.
        </p>
        @if($matriculas->isNotEmpty())
            <form method="POST" action="{{ route('docente.asistencia.update') }}">
                @csrf @method('PUT')
                <input type="hidden" name="periodo" value="{{ $periodo->id }}">
                <input type="hidden" name="carga" value="{{ $carga->id }}">
                <input type="hidden" name="fecha" value="{{ $fecha->toDateString() }}">
                <div class="asistencia-tabla">
                    <table>
                        <caption>Asistencia de la clase · {{ $matriculas->count() }} estudiantes</caption>
                        <thead><tr>
                            <th scope="col">Ord</th><th scope="col">Identificación</th><th scope="col">Apellidos y nombres</th>
                            <th scope="col" class="asistencia-clase">
                                <span class="asistencia-clase-titulo">Período de clase</span>
                                <span class="asistencia-chip">{{ ucfirst($fecha->locale('es')->isoFormat('ddd, D MMM YYYY')) }}</span>
                                <span class="asistencia-clase-campos">
                                    <select name="horas" class="asistencia-horas" aria-label="Duración de la clase">
                                        @for($horas = 1; $horas <= $maxHoras; $horas++)
                                            <option value="{{ $horas }}" @selected((int) old('horas', $sesion?->horas ?? 1) === $horas)>{{ $horas }} {{ $horas === 1 ? 'hora' : 'horas' }}</option>
                                        @endfor
                                    </select>
                                    <input type="text" class="asistencia-tema" name="tema" maxlength="255" value="{{ old('tema', $sesion?->tema) }}"
                                           placeholder="Tema a tratar" aria-label="Tema a tratar en la clase">
                                </span>
                            </th>
                            <th scope="col">Observaciones</th>
                        </tr></thead>
                        <tbody>
                            @foreach($matriculas as $matricula)
                                @php
                                    $persona = $matricula->estudiante->persona;
                                    $nombre = $persona ? trim(implode(' ', array_filter([$persona->primer_apellido, $persona->segundo_apellido, $persona->primer_nombre, $persona->segundo_nombre]))) : $matricula->estudiante->username;
                                    $iniciales = $persona ? mb_strtoupper(mb_substr($persona->primer_nombre ?? '', 0, 1).mb_substr($persona->primer_apellido ?? '', 0, 1)) : mb_strtoupper(mb_substr($matricula->estudiante->username, 0, 2));
                                    $registro = $sesion?->asistencias->firstWhere('matricula_id', $matricula->id);
                                    $estado = old('estudiantes.'.$matricula->id, $registro?->estado);
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td><td class="asistencia-id">{{ $persona?->numero_identificacion ?: 'Sin registro' }}</td>
                                    <th scope="row"><span class="asistencia-alumno"><span class="asistencia-avatar" aria-hidden="true">{{ $iniciales }}</span>{{ $nombre }}</span></th>
                                    <td class="asistencia-celda">
                                        <div class="asistencia-estados" role="radiogroup" aria-label="Asistencia de {{ $nombre }}">
                                            <input type="radio" class="asistencia-vacio sr-only" name="estudiantes[{{ $matricula->id }}]" value="" @checked(! $estado) aria-label="Sin registrar">
                                            @foreach($estados as $codigo => $etiqueta)
                                                <label class="asistencia-estado asistencia-estado--{{ strtolower($codigo) }}" title="{{ $etiqueta }}">
                                                    <input type="radio" class="sr-only" name="estudiantes[{{ $matricula->id }}]" value="{{ $codigo }}" @checked($estado === $codigo)>
                                                    <span aria-hidden="true">{{ $codigo }}</span><span class="sr-only">{{ $etiqueta }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="asistencia-observacion" name="observaciones[{{ $matricula->id }}]" maxlength="500"
                                               value="{{ old('observaciones.'.$matricula->id, $registro?->observacion) }}"
                                               placeholder="Comportamiento en clase" aria-label="Observación de comportamiento de {{ $nombre }}"
                                               @error('observaciones.'.$matricula->id) aria-invalid="true" @enderror>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button class="sw-btn sw-btn-primary asistencia-guardar" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Guardar asistencia</button>
            </form>
        @else
            <p class="distributivo-nota">No hay estudiantes con matrícula activa en este curso y paralelo para el período seleccionado.</p>
        @endif
    @else
        <p class="distributivo-nota">No tiene carga horaria con curso y paralelo asignados en este período.</p>
    @endif
</section>
<script>
    document.querySelectorAll('.asistencia-personal [data-autosubmit]').forEach((select) => {
        select.addEventListener('change', () => select.form.requestSubmit());
    });
    document.querySelectorAll('.asistencia-estado').forEach((boton) => {
        const radio = boton.querySelector('input');
        boton.addEventListener('pointerdown', () => { radio.dataset.marcado = radio.checked ? '1' : ''; });
        radio.addEventListener('click', () => {
            if (radio.dataset.marcado) {
                boton.closest('.asistencia-estados').querySelector('.asistencia-vacio').checked = true;
            }
            radio.dataset.marcado = '';
        });
    });
</script>
