@extends('layouts.admin-shell')
@section('title', 'Docentes tutores')
@section('admin-nav', 'tutorias')
@section('sidebar-title', 'Directivo')
@section('crumbs')
    <a href="{{ route('roles.dashboard', 'directivo') }}">Resumen Ejecutivo</a><span>/</span><span>Docentes tutores</span>
@endsection
@section('head')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/asignaciones-docentes.css') }}?v={{ filemtime(public_path('css/asignaciones-docentes.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/tutorias.css') }}?v={{ filemtime(public_path('css/tutorias.css')) }}" rel="stylesheet">
@endsection
@section('content')
<div class="asignaciones-workspace tutorias-workspace">
    <header class="asignaciones-heading">
        <div><span class="asignaciones-kicker">Organización docente</span><h1>Docentes tutores</h1><p>Asigna, cambia o retira el tutor de cada curso y paralelo en el período lectivo seleccionado.</p></div>
        <form method="GET" action="{{ route('directivo.tutorias.index') }}" class="periodo-selector">
            <label for="periodo">Período lectivo</label>
            <div class="periodo-control"><select class="select" id="periodo" name="periodo" @disabled($periodos->isEmpty())>
                @forelse($periodos as $opcion)<option value="{{ $opcion->id }}" @selected($periodo?->id === $opcion->id)>{{ $opcion->nombre }}{{ $opcion->es_activo ? ' · Vigente' : '' }}</option>@empty<option>Sin períodos registrados</option>@endforelse
            </select><button class="btn" type="submit" @disabled($periodos->isEmpty())>Ver</button></div>
        </form>
    </header>
    @if(session('status'))<div class="alert" role="status">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="errors" role="alert"><strong>No se pudo guardar la tutoría.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if(!$periodo)
        <section class="asignaciones-empty"><h2>Primero necesitas un período lectivo</h2><p>Registra un período académico para asignar los docentes tutores.</p></section>
    @else
        <div class="asignacion-section-heading">
            <p class="tutoria-help">Asignaciones de <strong>{{ $periodo->nombre }}</strong>. Los cambios no modifican las tutorías de otros períodos ni las horas pedagógicas.</p>
            <a class="btn" target="_blank" rel="noopener" href="{{ route('directivo.asignaciones.agrupado.pdf', ['agrupacion' => 'tutores', 'periodo' => $periodo->id]) }}"><i class="fa-solid fa-file-pdf" aria-hidden="true"></i> Reporte de tutores PDF</a>
        </div>
        @if($docentes->isEmpty())<p class="tutoria-help" role="status">No hay docentes activos disponibles para asignar. Puede retirar una tutoría existente seleccionando «Sin tutor».</p>@endif
        <div class="tutorias-table-wrap">
            <table class="tutorias-table">
                <thead><tr><th scope="col">Ord</th><th scope="col">Curso / paralelo</th><th scope="col">Tutor en {{ $periodo->nombre }}</th><th scope="col">Asignación</th></tr></thead>
                <tbody>
                @forelse($paralelos as $paralelo)
                    @php
                        $tutoria = $paralelo->tutorias->first();
                        $tutor = $tutoria?->docente;
                        $esFilaAnterior = (int) old('paralelo_id') === $paralelo->id;
                        $seleccion = $esFilaAnterior ? old('docente_id') : $tutoria?->docente_id;
                    @endphp
                    <tr id="paralelo-{{ $paralelo->id }}">
                        <td data-label="Ord">{{ $loop->iteration }}</td>
                        <td data-label="Curso / paralelo"><strong>{{ $paralelo->curso->nombre }} · {{ $paralelo->letra }}</strong><small>{{ $paralelo->curso->especialidad?->nombre ?? 'Educación General Básica' }}</small></td>
                        <td data-label="Tutor actual">{{ $tutor && (int) $tutor->institucion_id === (int) $institucion->id ? ($tutor->persona ? $tutor->nombre_completo : $tutor->username) : 'Sin tutor asignado' }}</td>
                        <td data-label="Asignación">
                            <form class="tutoria-form" method="POST" action="{{ route('directivo.tutorias.update', $paralelo) }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="periodo_id" value="{{ $periodo->id }}">
                                <input type="hidden" name="paralelo_id" value="{{ $paralelo->id }}">
                                <label class="sr-only" for="tutor-{{ $paralelo->id }}">Tutor de {{ $paralelo->curso->nombre }} {{ $paralelo->letra }}, {{ $paralelo->curso->especialidad?->nombre ?? 'EGB' }}</label>
                                <select class="select" id="tutor-{{ $paralelo->id }}" name="docente_id">
                                    <option value="" @selected(!$seleccion)>Sin tutor</option>
                                    @if($seleccion && !$docentes->contains('id', (int) $seleccion))<option value="{{ $seleccion }}" selected disabled>Docente no disponible: seleccione un reemplazo</option>@endif
                                    @foreach($docentes as $docente)<option value="{{ $docente->id }}" @selected((string) $seleccion === (string) $docente->id)>{{ $docente->persona ? $docente->nombre_completo : $docente->username }}</option>@endforeach
                                </select>
                                <button class="btn asignacion-primary" type="submit" aria-label="Guardar tutor de {{ $paralelo->curso->nombre }} {{ $paralelo->letra }}"><i class="fa-solid fa-check" aria-hidden="true"></i> Guardar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">No hay cursos y paralelos activos para asignar tutores.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
