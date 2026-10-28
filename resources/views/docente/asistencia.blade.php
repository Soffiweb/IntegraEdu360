<section class="distributivo-personal asistencia-personal">
    <header class="page-header"><div class="page-header-copy">
        <h1>Registro de asistencia</h1>
        <p>Registre la asistencia de sus estudiantes en cada período de clase de la semana.</p>
    </div></header>
    @if(session('success'))<p class="sw-alert sw-alert-success" role="status">{{ session('success') }}</p>@endif
    @if($errors->any())
        <div class="sw-alert sw-alert-error" role="alert"><p>No se guardaron los cambios. Revise lo siguiente:</p>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <form method="GET" action="{{ route('docente.asistencia.index') }}" class="distributivo-filtros">
        <label for="asistencia-periodo">Período académico</label>
        <select name="periodo" id="asistencia-periodo">
            @forelse($periodos as $opcion)<option value="{{ $opcion->id }}" @selected($periodo?->id === $opcion->id)>{{ $opcion->nombre }}</option>
            @empty<option value="">Sin períodos</option>@endforelse
        </select>
        <button class="sw-btn sw-btn-primary" type="submit">Cambiar período</button>
    </form>
    @if($periodo)
        <form method="GET" action="{{ route('docente.asistencia.index') }}" class="distributivo-filtros">
            <input type="hidden" name="periodo" value="{{ $periodo->id }}">
            <label for="asistencia-carga">Asignatura · curso · paralelo</label>
            <select name="carga" id="asistencia-carga">
                @forelse($cargas as $opcion)
                    <option value="{{ $opcion->id }}" @selected($carga?->id === $opcion->id)>{{ $opcion->asignatura?->nombre ?? $opcion->titulo }} · {{ $opcion->curso->nombre }} {{ $opcion->paralelo->letra }} · {{ $opcion->curso->especialidad?->nombre ?? 'EGB' }} ({{ number_format((float) $opcion->horas_semanales, 2, ',', '.') }} períodos/semana)</option>
                @empty<option value="">Sin carga horaria con paralelo asignado</option>@endforelse
            </select>
            <label for="asistencia-semana">Semana de la fecha</label>
            <input class="sw-input" type="date" name="semana" id="asistencia-semana" value="{{ $semana->toDateString() }}" required>
            <button class="sw-btn sw-btn-primary" type="submit">Consultar</button>
        </form>
    @endif
    @if($carga)
        <p><strong>{{ $carga->asignatura?->nombre ?? $carga->titulo }}</strong> · {{ $carga->curso->nombre }} {{ $carga->paralelo->letra }} · {{ $carga->curso->especialidad?->nombre ?? 'EGB' }}</p>
        <p>Semana: {{ $semana->format('d/m/Y') }} al {{ $semana->endOfWeek()->format('d/m/Y') }} · Carga semanal: {{ number_format((float) $carga->horas_semanales, 2, ',', '.') }} períodos.</p>
        <p class="distributivo-nota">Seleccione la fecha de cada clase. Puede registrar varios períodos en un mismo día. Una casilla vacía significa «Sin registrar».</p>
        @if($matriculas->isNotEmpty())
            <form method="POST" action="{{ route('docente.asistencia.update') }}">
                @csrf @method('PUT')
                <input type="hidden" name="periodo" value="{{ $periodo->id }}">
                <input type="hidden" name="carga" value="{{ $carga->id }}">
                <input type="hidden" name="semana" value="{{ $semana->toDateString() }}">
                <div class="distributivo-tabla asistencia-tabla">
                    <table class="sw-table">
                        <caption>Asistencia semanal · {{ $matriculas->count() }} estudiantes</caption>
                        <thead><tr>
                            <th scope="col">Ord</th><th scope="col">Identificación</th><th scope="col">Apellidos y nombres</th>
                            @for($numero = 1; $numero <= $cantidad; $numero++)
                                @php $duracion = min(1, (float) $carga->horas_semanales - $numero + 1); @endphp
                                <th scope="col">
                                    <label for="fecha-sesion-{{ $numero }}">Período {{ $numero }}@if($duracion < 1) ({{ $duracion }})@endif</label>
                                    <input type="date" id="fecha-sesion-{{ $numero }}" name="sesiones[{{ $numero }}][fecha]"
                                           value="{{ old('sesiones.'.$numero.'.fecha', $sesiones->get($numero)?->fecha?->toDateString()) }}"
                                           min="{{ max($semana->toDateString(), $periodo->fecha_inicio->toDateString()) }}"
                                           max="{{ min($semana->endOfWeek()->toDateString(), $periodo->fecha_fin->toDateString()) }}"
                                           aria-label="Fecha del período {{ $numero }}">
                                </th>
                            @endfor
                        </tr></thead>
                        <tbody>
                            @foreach($matriculas as $matricula)
                                @php
                                    $persona = $matricula->estudiante->persona;
                                    $nombre = $persona ? trim(implode(' ', array_filter([$persona->primer_apellido, $persona->segundo_apellido, $persona->primer_nombre, $persona->segundo_nombre]))) : $matricula->estudiante->username;
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td><td>{{ $persona?->numero_identificacion ?: 'Sin registro' }}</td><th scope="row">{{ $nombre }}</th>
                                    @for($numero = 1; $numero <= $cantidad; $numero++)
                                        @php $estado = old('sesiones.'.$numero.'.estudiantes.'.$matricula->id, $sesiones->get($numero)?->asistencias->firstWhere('matricula_id', $matricula->id)?->estado); @endphp
                                        <td><select name="sesiones[{{ $numero }}][estudiantes][{{ $matricula->id }}]" aria-label="{{ $nombre }} · período {{ $numero }}">
                                            <option value="">Sin registrar</option>
                                            @foreach($estados as $codigo => $etiqueta)<option value="{{ $codigo }}" @selected($estado === $codigo)>{{ $etiqueta }}</option>@endforeach
                                        </select></td>
                                    @endfor
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
