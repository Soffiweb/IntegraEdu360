<section class="distributivo-personal">
    <header class="page-header">
        <div class="page-header-copy">
            <h1>Estudiantes por curso</h1>
            <p>Seleccione un curso de su carga horaria o tutoría para consultar sus estudiantes matriculados.</p>
        </div>
    </header>
    <form method="GET" action="{{ route('docente.estudiantes.index') }}" class="distributivo-filtros" id="estudiantes-curso-filtros">
        <label for="estudiantes-periodo">Período académico</label>
        <select name="periodo" id="estudiantes-periodo">
            @forelse ($periodos as $opcion)
                <option value="{{ $opcion->id }}" @selected($periodo?->id === $opcion->id)>{{ $opcion->nombre }}</option>
            @empty
                <option value="">Sin períodos registrados</option>
            @endforelse
        </select>
        <label for="estudiantes-curso">Curso</label>
        <select name="curso" id="estudiantes-curso">
            @forelse ($cursos as $opcion)
                <option value="{{ $opcion->id }}" @selected($curso?->id === $opcion->id)>{{ $opcion->nombre }}{{ $opcion->especialidad ? ' · '.$opcion->especialidad->nombre : '' }}</option>
            @empty
                <option value="">Sin cursos asignados</option>
            @endforelse
        </select>
        <label for="estudiantes-paralelo">Paralelo</label>
        <select name="paralelo" id="estudiantes-paralelo">
            <option value="">Todos mis paralelos del curso</option>
            @foreach ($paralelos as $opcion)
                <option value="{{ $opcion->id }}" @selected($paralelo?->id === $opcion->id)>{{ $opcion->letra }}</option>
            @endforeach
        </select>
        <button type="submit" class="sw-btn sw-btn-primary">Consultar</button>
    </form>
    @if ($errors->any())
        <p role="alert">Revise los filtros seleccionados.</p>
    @endif
    <div class="distributivo-tabla">
        <table class="sw-table">
            <caption>{{ $curso?->nombre ?? 'Estudiantes matriculados' }}{{ $paralelo ? ' · Paralelo '.$paralelo->letra : '' }}</caption>
            <thead><tr><th scope="col">Ord</th><th scope="col">Identificación</th><th scope="col">Estudiante</th><th scope="col">Paralelo</th></tr></thead>
            <tbody>
                @forelse ($matriculas as $matricula)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $matricula->estudiante->persona?->numero_identificacion ?: 'Sin identificación' }}</td>
                        <td>{{ $matricula->estudiante->persona ? $matricula->estudiante->nombre_completo : $matricula->estudiante->username }}</td>
                        <td>{{ $matricula->paralelo->letra }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">{{ $curso ? 'No hay estudiantes con matrícula activa en los paralelos seleccionados.' : 'No tiene cursos asignados en este período.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
<script src="{{ asset('js/estudiantes-curso.js') }}" defer></script>
