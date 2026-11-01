<section class="distributivo-personal asistencia-personal">
    @php
        $filtros = array_filter(['periodo' => $periodo?->id, 'carga' => $carga?->id, 'desde' => $desde, 'hasta' => $hasta]);
        $porcentaje = fn ($valor) => $valor === null ? '—' : number_format($valor, 2, ',', '.').' %';
        $agregados = ['clases' => 'Clase', 'cursos' => 'Curso y paralelo', 'semanas' => 'Semana', 'meses' => 'Mes'];
    @endphp
    <header class="page-header asistencia-encabezado">
        <div class="page-header-copy">
            <h1>Informes de asistencia</h1>
            <p>Consulte la asistencia de sus clases y estudiantes desde distintas perspectivas.</p>
        </div>
        @if(in_array($tab, ['cursos', 'semanas'], true) && $cargas->isNotEmpty())
            <a class="asistencia-volver" target="_blank" rel="noopener" href="{{ route('docente.asistencia.reporte', ['agrupacion' => $tab] + $filtros) }}">
                <i class="fa-solid fa-file-pdf" aria-hidden="true"></i> Descargar PDF</a>
        @endif
    </header>
    @if($errors->any())
        <div class="sw-alert sw-alert-error" role="alert"><p>No se aplicaron los filtros. Revise lo siguiente:</p>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <form method="GET" action="{{ route('docente.informes-asistencia.index') }}" class="asistencia-filtros">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <label for="informe-periodo">Período académico</label>
        <select name="periodo" id="informe-periodo" data-autosubmit>
            @forelse($periodos as $opcion)<option value="{{ $opcion->id }}" @selected($periodo?->id === $opcion->id)>{{ $opcion->nombre }}</option>
            @empty<option value="">Sin períodos</option>@endforelse
        </select>
        <label for="informe-carga">Asignatura · curso · paralelo</label>
        <select name="carga" id="informe-carga" class="asistencia-carga" data-autosubmit>
            <option value="">Todas las clases</option>
            @foreach($cargas as $opcion)<option value="{{ $opcion->id }}" @selected($carga?->id === $opcion->id)>{{ $opcion->asignatura?->nombre ?? $opcion->titulo }} · {{ $opcion->curso->nombre }} {{ $opcion->paralelo->letra }} · {{ $opcion->curso->especialidad?->nombre ?? 'EGB' }}</option>@endforeach
        </select>
        <span class="asistencia-rango">
            <label for="informe-desde">Desde</label>
            <input type="date" name="desde" id="informe-desde" value="{{ $desde }}">
        </span>
        <span class="asistencia-rango">
            <label for="informe-hasta">Hasta</label>
            <input type="date" name="hasta" id="informe-hasta" value="{{ $hasta }}">
        </span>
        <button type="submit">Aplicar</button>
    </form>
    <nav class="asistencia-tabs" aria-label="Tipos de informe">
        @foreach($tabs as $clave => $nombre)
            <a href="{{ route('docente.informes-asistencia.index', ['tab' => $clave] + $filtros) }}" @if($tab === $clave) class="activo" aria-current="page" @endif>{{ $nombre }}</a>
        @endforeach
    </nav>
    @if($cargas->isEmpty())
        <p class="distributivo-nota">No tiene carga horaria con curso y paralelo asignados en este período.</p>
    @elseif($tab === 'resumen')
        <div class="asistencia-resumen">
            <div class="asistencia-stat asistencia-stat--info"><span class="asistencia-stat-icono"><i class="fa-solid fa-list" aria-hidden="true"></i></span>
                <div><p>Clases registradas</p><strong>{{ $resumen['clases'] }}</strong><small>{{ $resumen['horas'] }} horas</small></div></div>
            <div class="asistencia-stat asistencia-stat--info"><span class="asistencia-stat-icono"><i class="fa-solid fa-users" aria-hidden="true"></i></span>
                <div><p>Estudiantes con registros</p><strong>{{ $estudiantes }}</strong></div></div>
            <div class="asistencia-stat asistencia-stat--success"><span class="asistencia-stat-icono"><i class="fa-solid fa-percent" aria-hidden="true"></i></span>
                <div><p>Asistencia promedio</p><strong>{{ $porcentaje($resumen['asistencia']) }}</strong></div></div>
            <div class="asistencia-stat asistencia-stat--warning"><span class="asistencia-stat-icono"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span>
                <div><p>Alertas</p><strong>{{ $alertas }}</strong><small>Menos de {{ number_format(\App\Services\Docente\AlertasAsistencia::UMBRAL, 0) }} % de asistencia</small></div></div>
        </div>
        @php($registros = array_sum($resumen['estados']))
        <div class="asistencia-tabla">
            <table>
                <caption>Registros por estado · {{ $registros }}</caption>
                <thead><tr><th scope="col">Estado</th><th scope="col" class="asistencia-num">Registros</th><th scope="col" class="asistencia-num">Porcentaje</th></tr></thead>
                <tbody>
                    @foreach($estados as $codigo => $etiqueta)
                        <tr><th scope="row"><span class="asistencia-conteo asistencia-estado--{{ strtolower($codigo) }}">{{ $codigo }}</span> {{ $etiqueta }}</th>
                            <td class="asistencia-num">{{ $resumen['estados'][$codigo] }}</td>
                            <td class="asistencia-num">{{ $porcentaje($registros ? round($resumen['estados'][$codigo] * 100 / $registros, 2) : null) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="distributivo-nota">La asistencia promedio considera a los estudiantes presentes y atrasados.</p>
    @elseif(isset($agregados[$tab]))
        <div class="asistencia-tabla">
            <table>
                <caption>{{ $tabs[$tab] }} · {{ $filas->count() }}</caption>
                <thead><tr>
                    <th scope="col">{{ $agregados[$tab] }}</th>
                    @if($tab === 'clases')<th scope="col" class="asistencia-num">Períodos / semana</th>@endif
                    <th scope="col" class="asistencia-num">Clases</th><th scope="col" class="asistencia-num">Horas</th>
                    @foreach($estados as $codigo => $etiqueta)<th scope="col" class="asistencia-num"><abbr title="{{ $etiqueta }}">{{ $codigo }}</abbr></th>@endforeach
                    <th scope="col" class="asistencia-num">Asistencia</th>
                </tr></thead>
                <tbody>
                    @forelse($filas as $fila)
                        <tr>
                            <th scope="row">@isset($fila['url'])<a class="asistencia-enlace" href="{{ $fila['url'] }}">{{ $fila['titulo'] }}</a>@else{{ $fila['titulo'] }}@endisset</th>
                            @if($tab === 'clases')<td class="asistencia-num">{{ number_format($fila['carga'], 0, ',', '.') }}</td>@endif
                            <td class="asistencia-num">{{ $fila['clases'] }}</td>
                            <td class="asistencia-num">{{ $fila['horas'] }}@if($tab === 'semanas') / {{ number_format($cargaSemanal, 0, ',', '.') }}@endif</td>
                            @foreach($estados as $codigo => $etiqueta)<td class="asistencia-num"><span class="asistencia-conteo asistencia-estado--{{ strtolower($codigo) }}">{{ $fila['estados'][$codigo] }}</span></td>@endforeach
                            <td class="asistencia-num">{{ $porcentaje($fila['asistencia']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ 5 + count($estados) }}">No hay clases registradas con los filtros actuales.</td></tr>
                    @endforelse
                </tbody>
                @if($filas->isNotEmpty())
                    <tfoot><tr>
                        <th scope="row">Total</th>
                        @if($tab === 'clases')<td></td>@endif
                        <td class="asistencia-num">{{ $resumen['clases'] }}</td><td class="asistencia-num">{{ $resumen['horas'] }}</td>
                        @foreach($estados as $codigo => $etiqueta)<td class="asistencia-num">{{ $resumen['estados'][$codigo] }}</td>@endforeach
                        <td class="asistencia-num">{{ $porcentaje($resumen['asistencia']) }}</td>
                    </tr></tfoot>
                @endif
            </table>
        </div>
    @elseif($tab === 'estudiantes')
        <div class="asistencia-tabla">
            <table>
                <caption>Por estudiante · {{ $filas->total() }}</caption>
                <thead><tr>
                    <th scope="col">Identificación</th><th scope="col">Apellidos y nombres</th><th scope="col">Curso</th>
                    @foreach($estados as $codigo => $etiqueta)<th scope="col" class="asistencia-num"><abbr title="{{ $etiqueta }}">{{ $codigo }}</abbr></th>@endforeach
                    <th scope="col" class="asistencia-num">Asistencia</th>
                </tr></thead>
                <tbody>
                    @forelse($filas as $fila)
                        <tr>
                            <td class="asistencia-id">{{ $fila['identificacion'] }}</td><th scope="row">{{ $fila['nombre'] }}</th><td>{{ $fila['curso'] }}</td>
                            @foreach($estados as $codigo => $etiqueta)<td class="asistencia-num"><span class="asistencia-conteo asistencia-estado--{{ strtolower($codigo) }}">{{ $fila['estados'][$codigo] }}</span></td>@endforeach
                            <td class="asistencia-num">{{ $porcentaje($fila['asistencia']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ 4 + count($estados) }}">No hay registros de estudiantes con los filtros actuales.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @elseif($tab === 'alertas')
        <div class="asistencia-tabla">
            <table>
                <caption>Alertas de asistencia · {{ $filas->total() }}</caption>
                <thead><tr>
                    <th scope="col">Identificación</th><th scope="col">Apellidos y nombres</th><th scope="col">Curso</th>
                    @foreach($estados as $codigo => $etiqueta)<th scope="col" class="asistencia-num"><abbr title="{{ $etiqueta }}">{{ $codigo }}</abbr></th>@endforeach
                    <th scope="col" class="asistencia-num">Asistencia</th><th scope="col">Representante</th><th scope="col">Notificación</th><th scope="col">Boleta</th>
                </tr></thead>
                <tbody>
                    @forelse($filas as $fila)
                        <tr>
                            <td class="asistencia-id">{{ $fila['identificacion'] }}</td><th scope="row">{{ $fila['nombre'] }}</th><td>{{ $fila['curso'] }}</td>
                            @foreach($estados as $codigo => $etiqueta)<td class="asistencia-num"><span class="asistencia-conteo asistencia-estado--{{ strtolower($codigo) }}">{{ $fila['estados'][$codigo] }}</span></td>@endforeach
                            <td class="asistencia-num">{{ $porcentaje($fila['porcentaje']) }}</td>
                            <td>{{ $fila['representante'] ?? 'Sin representante' }}</td>
                            <td>{{ $fila['notificacion'] }}</td>
                            <td><a class="asistencia-enlace" target="_blank" rel="noopener" href="{{ route('docente.informes-asistencia.boleta', $fila['matricula_id']) }}"><i class="fa-solid fa-file-pdf" aria-hidden="true"></i> PDF</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ 7 + count($estados) }}">Ningún estudiante tiene menos de {{ number_format(\App\Services\Docente\AlertasAsistencia::UMBRAL, 0) }} % de asistencia.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="distributivo-nota">La asistencia considera todas las clases del estudiante en el período, de todas las asignaturas, sin aplicar el filtro de fechas. Presentes y atrasados cuentan como asistencia; faltas y justificadas, no. Se evalúa desde {{ \App\Services\Docente\AlertasAsistencia::MIN_REGISTROS }} registros.</p>
    @else
        <div class="asistencia-tabla">
            <table>
                <caption>Observaciones · {{ $filas->total() }}</caption>
                <thead><tr><th scope="col">Fecha</th><th scope="col">Apellidos y nombres</th><th scope="col">Curso</th><th scope="col">Asignatura</th><th scope="col" class="asistencia-num">Estado</th><th scope="col">Observación</th></tr></thead>
                <tbody>
                    @forelse($filas as $fila)
                        <tr>
                            <td class="asistencia-id">{{ $fila['fecha']->format('d/m/Y') }}</td><th scope="row">{{ $fila['nombre'] }}</th><td>{{ $fila['curso'] }}</td><td>{{ $fila['asignatura'] }}</td>
                            <td class="asistencia-num"><abbr title="{{ $estados[$fila['estado']] ?? $fila['estado'] }}" class="asistencia-conteo asistencia-estado--{{ strtolower($fila['estado']) }}">{{ $fila['estado'] }}</abbr></td>
                            <td>{{ $fila['observacion'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No hay observaciones con los filtros actuales.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
    @if(isset($filas) && $filas instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $filas->hasPages())
        <nav class="asistencia-paginacion" aria-label="Paginación del informe">
            @if($filas->onFirstPage())<span>Anterior</span>@else<a href="{{ $filas->previousPageUrl() }}">Anterior</a>@endif
            <span>Página {{ $filas->currentPage() }} de {{ $filas->lastPage() }}</span>
            @if($filas->hasMorePages())<a href="{{ $filas->nextPageUrl() }}">Siguiente</a>@else<span>Siguiente</span>@endif
        </nav>
    @endif
</section>
<script>
    document.querySelectorAll('.asistencia-personal [data-autosubmit]').forEach((select) => {
        select.addEventListener('change', () => select.form.requestSubmit());
    });
</script>
