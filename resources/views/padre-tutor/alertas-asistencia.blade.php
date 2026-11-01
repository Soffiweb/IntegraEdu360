<section class="distributivo-personal asistencia-personal">
    <header class="page-header asistencia-encabezado">
        <div class="page-header-copy">
            <h1>Alertas de asistencia</h1>
            <p>Se genera una alerta cuando la asistencia de su representado baja del {{ number_format(\App\Services\Docente\AlertasAsistencia::UMBRAL, 0) }} % en el período académico.</p>
        </div>
    </header>
    <div class="asistencia-tabla">
        <table>
            <caption>Alertas · {{ $alertas->total() }}</caption>
            <thead><tr><th scope="col">Estudiante</th><th scope="col">Curso</th><th scope="col" class="asistencia-num">Asistencia</th><th scope="col">Fecha de la alerta</th><th scope="col">Estado</th><th scope="col">Boleta</th></tr></thead>
            <tbody>
                @forelse($alertas as $alerta)
                    @php($estudiante = $alerta->matricula->estudiante)
                    <tr>
                        <th scope="row">{{ $estudiante->persona ? $estudiante->nombre_completo : $estudiante->username }}</th>
                        <td>{{ $alerta->matricula->paralelo->curso->nombre }} {{ $alerta->matricula->paralelo->letra }}</td>
                        <td class="asistencia-num">{{ number_format($alerta->porcentaje, 2, ',', '.') }} %</td>
                        <td class="asistencia-id">{{ $alerta->created_at->format('d/m/Y') }}</td>
                        <td>@if($alerta->resuelta_at)<span class="asistencia-chip">Recuperada el {{ $alerta->resuelta_at->format('d/m/Y') }}</span>@else<span class="asistencia-conteo asistencia-estado--f">Activa</span>@endif</td>
                        <td>@if(! $alerta->resuelta_at && $alerta->matricula->estado === 'ACTIVA')<a class="asistencia-enlace" target="_blank" rel="noopener" href="{{ route('padre-tutor.alertas-asistencia.boleta', $alerta->id) }}"><i class="fa-solid fa-file-pdf" aria-hidden="true"></i> PDF</a>@else — @endif</td>
                    </tr>
                @empty
                    <tr><td colspan="6">No hay alertas de asistencia.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($alertas->hasPages())
        <nav class="asistencia-paginacion" aria-label="Paginación de alertas">
            @if($alertas->onFirstPage())<span>Anterior</span>@else<a href="{{ $alertas->previousPageUrl() }}">Anterior</a>@endif
            <span>Página {{ $alertas->currentPage() }} de {{ $alertas->lastPage() }}</span>
            @if($alertas->hasMorePages())<a href="{{ $alertas->nextPageUrl() }}">Siguiente</a>@else<span>Siguiente</span>@endif
        </nav>
    @endif
</section>
