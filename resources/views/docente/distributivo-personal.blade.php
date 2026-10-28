<section class="distributivo-personal">
    <header class="page-header">
        <div class="page-header-copy">
            <h1>Distributivo Personal</h1>
            <p>Consulte sus asignaturas, carga horaria semanal, comisiones y tutorías asignadas.</p>
        </div>
    </header>

    <form method="GET" action="{{ route('docente.distributivo.index') }}" class="distributivo-filtros">
        <label for="periodo-distributivo">Período académico</label>
        <select name="periodo" id="periodo-distributivo">
            @forelse ($periodos as $opcion)
                <option value="{{ $opcion->id }}" @selected($periodo?->id === $opcion->id)>{{ $opcion->nombre }}</option>
            @empty
                <option value="">Sin períodos registrados</option>
            @endforelse
        </select>
        <button type="submit" class="sw-btn sw-btn-primary">Consultar</button>
    </form>

    <div class="distributivo-tabla">
        <table class="sw-table">
            <caption>Asignaciones de carga horaria{{ $periodo ? ' · '.$periodo->nombre : '' }}</caption>
            <thead>
                <tr>
                    <th scope="col">Ord</th>
                    <th scope="col">Asignatura</th>
                    <th scope="col">Especialidad</th>
                    <th scope="col">Curso</th>
                    <th scope="col">Paralelo</th>
                    <th scope="col">Horas semanales</th>
                    <th scope="col">Estudiantes</th>
                    <th scope="col">Listado PDF</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($asignaciones as $asignacion)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $asignacion->asignatura?->nombre ?? $asignacion->titulo }}</td>
                        <td>{{ $asignacion->curso?->especialidad?->nombre ?? 'Sin especialidad' }}</td>
                        <td>{{ $asignacion->curso?->nombre ?? 'Sin curso asignado' }}</td>
                        <td>{{ $asignacion->paralelo?->letra ?? 'Sin paralelo asignado' }}</td>
                        <td>{{ number_format((float) $asignacion->horas_semanales, 2, ',', '.') }}</td>
                        <td>Sin registro</td>
                        <td>
                            @if ($asignacion->curso && $asignacion->paralelo)
                                <a class="sw-btn sw-btn-ghost" href="{{ route('docente.distributivo.estudiantes.pdf', $asignacion->id) }}" target="_blank" rel="noopener"
                                   aria-label="Listado de estudiantes de {{ $asignacion->curso->nombre }} {{ $asignacion->paralelo->letra }} en PDF">
                                    <i class="fa-solid fa-file-pdf" aria-hidden="true"></i> PDF
                                </a>
                            @else
                                Sin paralelo asignado
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">No tiene asignaturas asignadas en este período.</td></tr>
                @endforelse
            </tbody>
            @if ($asignaciones->isNotEmpty())
                <tfoot><tr><th colspan="5" scope="row">Carga horaria semanal total</th><td>{{ number_format((float) $totalHoras, 2, ',', '.') }}</td><td>—</td><td>—</td></tr></tfoot>
            @endif
        </table>
    </div>
    <p class="distributivo-nota">El número de estudiantes estará disponible cuando se registren las matrículas por paralelo.</p>

    <div class="distributivo-tabla">
        <table class="sw-table">
            <caption>Comisiones asignadas</caption>
            <thead>
                <tr><th scope="col">Ord</th><th scope="col">Comisión</th><th scope="col">Horas semanales</th><th scope="col">Fecha límite</th><th scope="col">Observaciones</th></tr>
            </thead>
            <tbody>
                @forelse ($comisiones as $comision)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $comision->titulo }}</td>
                        <td>{{ number_format((float) $comision->horas_semanales, 2, ',', '.') }}</td>
                        <td>{{ $comision->fecha_limite?->format('d/m/Y') ?? 'Sin fecha límite' }}</td>
                        <td>{{ $comision->observaciones ?: 'Sin observaciones' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">No tiene comisiones asignadas en este período.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="distributivo-tabla">
        <table class="sw-table">
            <caption>Curso y paralelo a su cargo como tutor</caption>
            <thead>
                <tr><th scope="col">Ord</th><th scope="col">Curso</th><th scope="col">Paralelo</th><th scope="col">Especialidad</th></tr>
            </thead>
            <tbody>
                @forelse ($tutorias as $tutoria)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $tutoria->paralelo?->curso?->nombre ?? 'Sin curso asignado' }}</td>
                        <td>{{ $tutoria->paralelo?->letra ?? 'Sin paralelo asignado' }}</td>
                        <td>{{ $tutoria->paralelo?->curso?->especialidad?->nombre ?? 'Sin especialidad' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">No tiene tutorías asignadas en este período.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
