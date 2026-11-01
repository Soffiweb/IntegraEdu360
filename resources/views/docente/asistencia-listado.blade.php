<section class="distributivo-personal asistencia-personal">
    <header class="page-header asistencia-encabezado">
        <div class="page-header-copy">
            <h1>Registro de asistencia</h1>
            <p>Consulte las clases registradas, corrija su asistencia o registre una clase nueva.</p>
        </div>
        @if($cargas->isNotEmpty())
            <div class="asistencia-acciones">
                <a class="asistencia-boton" href="{{ route('docente.asistencia.registro', array_filter(['periodo' => $periodo?->id, 'carga' => $carga?->id])) }}">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i> Registrar asistencia</a>
                @foreach(['cursos' => 'Reporte por curso', 'semanas' => 'Reporte por semana'] as $agrupacion => $etiqueta)
                    <a class="asistencia-volver" target="_blank" rel="noopener" href="{{ route('docente.asistencia.reporte', array_filter(['agrupacion' => $agrupacion, 'periodo' => $periodo?->id, 'carga' => $carga?->id])) }}">
                        <i class="fa-solid fa-file-pdf" aria-hidden="true"></i> {{ $etiqueta }}</a>
                @endforeach
            </div>
        @endif
    </header>
    @if(session('success'))
        <p class="sw-alert sw-alert-success" role="status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ session('success') }}</p>
    @endif
    <form method="GET" action="{{ route('docente.asistencia.index') }}" class="asistencia-filtros">
        <label for="listado-periodo">Período académico</label>
        <select name="periodo" id="listado-periodo" data-autosubmit>
            @forelse($periodos as $opcion)<option value="{{ $opcion->id }}" @selected($periodo?->id === $opcion->id)>{{ $opcion->nombre }}</option>
            @empty<option value="">Sin períodos</option>@endforelse
        </select>
        <label for="listado-carga">Asignatura · curso · paralelo</label>
        <select name="carga" id="listado-carga" class="asistencia-carga" data-autosubmit>
            <option value="">Todas las clases</option>
            @foreach($cargas as $opcion)<option value="{{ $opcion->id }}" @selected($carga?->id === $opcion->id)>{{ $opcion->asignatura?->nombre ?? $opcion->titulo }} · {{ $opcion->curso->nombre }} {{ $opcion->paralelo->letra }} · {{ $opcion->curso->especialidad?->nombre ?? 'EGB' }}</option>@endforeach
        </select>
        <noscript><button class="sw-btn sw-btn-primary" type="submit">Filtrar</button></noscript>
    </form>
    @if($cargas->isEmpty())
        <p class="distributivo-nota">No tiene carga horaria con curso y paralelo asignados en este período.</p>
    @else
        <div class="asistencia-tabla">
            <table>
                <caption>Clases registradas · {{ $sesiones->total() }}{{ $carga ? ' · '.($carga->asignatura?->nombre ?? $carga->titulo).' · '.$carga->curso->nombre.' '.$carga->paralelo->letra : '' }}</caption>
                <thead><tr>
                    <th scope="col">Fecha</th><th scope="col">Asignatura · curso · paralelo</th><th scope="col">Tema</th><th scope="col">Horas</th>
                    @foreach($estados as $codigo => $etiqueta)<th scope="col" class="asistencia-num"><abbr title="{{ $etiqueta }}">{{ $codigo }}</abbr></th>@endforeach
                    <th scope="col"><span class="sr-only">Acciones</span></th>
                </tr></thead>
                <tbody>
                    @forelse($sesiones as $sesion)
                        @php($clase = ($sesion->carga->asignatura?->nombre ?? $sesion->carga->titulo).' · '.$sesion->carga->curso->nombre.' '.$sesion->carga->paralelo->letra)
                        <tr>
                            <td class="asistencia-id">{{ ucfirst($sesion->fecha->locale('es')->isoFormat('ddd D MMM YYYY')) }}</td>
                            <th scope="row">{{ $clase }}</th>
                            <td>{{ $sesion->tema ?: 'Sin tema' }}</td>
                            <td class="asistencia-num">{{ $sesion->horas }}</td>
                            @foreach($estados as $codigo => $etiqueta)
                                <td class="asistencia-num"><span class="asistencia-conteo asistencia-estado--{{ strtolower($codigo) }}">{{ $sesion->{'total_'.strtolower($codigo)} }}</span></td>
                            @endforeach
                            <td><a class="asistencia-editar" href="{{ route('docente.asistencia.registro', ['periodo' => $periodo->id, 'carga' => $sesion->carga->id, 'fecha' => $sesion->fecha->toDateString()]) }}"
                                   aria-label="Editar asistencia de {{ $clase }} del {{ $sesion->fecha->format('d/m/Y') }}"><i class="fa-solid fa-pen" aria-hidden="true"></i> Editar</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ 5 + count($estados) }}">No hay clases registradas{{ $carga ? ' para esta asignatura, curso y paralelo' : '' }} en este período.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sesiones->hasPages())
            <nav class="asistencia-paginacion" aria-label="Paginación de clases">
                @if($sesiones->onFirstPage())<span>Anterior</span>@else<a href="{{ $sesiones->previousPageUrl() }}">Anterior</a>@endif
                <span>Página {{ $sesiones->currentPage() }} de {{ $sesiones->lastPage() }}</span>
                @if($sesiones->hasMorePages())<a href="{{ $sesiones->nextPageUrl() }}">Siguiente</a>@else<span>Siguiente</span>@endif
            </nav>
        @endif
    @endif
</section>
<script>
    document.querySelectorAll('.asistencia-personal [data-autosubmit]').forEach((select) => {
        select.addEventListener('change', () => select.form.requestSubmit());
    });
</script>
