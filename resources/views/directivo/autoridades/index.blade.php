@extends('layouts.admin-shell')
@section('title', 'Autoridades y consejo')
@section('admin-nav', 'autoridades')
@section('sidebar-title', 'Directivo')
@section('crumbs')
    <a href="{{ route('roles.dashboard', 'directivo') }}">Resumen Ejecutivo</a><span>/</span><span>Autoridades y consejo</span>
@endsection
@section('head')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/asignaciones-docentes.css') }}?v={{ filemtime(public_path('css/asignaciones-docentes.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/tutorias.css') }}?v={{ filemtime(public_path('css/tutorias.css')) }}" rel="stylesheet">
@endsection
@section('content')
<div class="asignaciones-workspace tutorias-workspace">
    <header class="asignaciones-heading">
        <div><span class="asignaciones-kicker">Organización docente</span><h1>Autoridades y consejo</h1><p>Registra las autoridades y los tres vocales del consejo ejecutivo con sus respectivos suplentes por período académico.</p></div>
        <form method="GET" action="{{ route('directivo.autoridades.index') }}" class="periodo-selector">
            <label for="periodo">Período lectivo</label>
            <div class="periodo-control"><select class="select" id="periodo" name="periodo" @disabled($periodos->isEmpty())>
                @forelse($periodos as $opcion)<option value="{{ $opcion->id }}" @selected($periodo?->id === $opcion->id)>{{ $opcion->nombre }}{{ $opcion->es_activo ? ' · Vigente' : '' }}</option>@empty<option>Sin períodos registrados</option>@endforelse
            </select><button class="btn" type="submit" @disabled($periodos->isEmpty())>Ver</button></div>
        </form>
    </header>
    @if(session('status'))<div class="alert" role="status">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="errors" role="alert"><strong>No se pudo guardar la designación.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if(!$periodo)
        <section class="asignaciones-empty"><h2>Primero necesitas un período lectivo</h2><p>Registra un período académico para designar las autoridades y el consejo ejecutivo.</p></section>
    @else
        <p class="tutoria-help">Designaciones de <strong>{{ $periodo->nombre }}</strong>. Los cambios se aplican únicamente a este período académico.</p>
        @if($docentes->isEmpty())<p role="status">No hay docentes, directivos o inspectores activos disponibles. Puede retirar una designación existente.</p>@endif
        <div class="tutorias-table-wrap">
            <table class="tutorias-table">
                <thead><tr><th scope="col">Ord</th><th scope="col">Cargo</th><th scope="col">Persona designada</th><th scope="col">Designación</th></tr></thead>
                <tbody>
                @foreach($cargos as $codigo => $nombre)
                    @php
                        $designacion = $designaciones->get($codigo);
                        $director = $designacion?->docente;
                        $seleccion = old('cargo') === $codigo && (int) old('periodo_id') === $periodo->id ? old('docente_id') : $designacion?->docente_id;
                    @endphp
                    <tr>
                        <td data-label="Ord">{{ $loop->iteration }}</td>
                        <td data-label="Cargo"><strong>{{ $nombre }}</strong></td>
                        <td data-label="Persona designada">{{ $director && (int) $director->institucion_id === (int) $institucion->id ? ($director->persona ? $director->nombre_completo : $director->username) : 'Sin designación' }}</td>
                        <td data-label="Designación">
                            <form class="tutoria-form" method="POST" action="{{ route('directivo.autoridades.update') }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="periodo_id" value="{{ $periodo->id }}">
                                <input type="hidden" name="cargo" value="{{ $codigo }}">
                                <label class="sr-only" for="director-{{ $codigo }}">Responsable de {{ $nombre }}</label>
                                <select class="select" id="director-{{ $codigo }}" name="docente_id">
                                    <option value="" @selected(!$seleccion)>Sin designación</option>
                                    @if($seleccion && !$docentes->contains('id', (int) $seleccion))<option value="{{ $seleccion }}" selected disabled>Persona no disponible: seleccione un reemplazo</option>@endif
                                    @foreach($docentes as $docente)
                                        @php $ocupado = $designaciones->contains(fn ($d) => $d->cargo !== $codigo && (int) $d->docente_id === (int) $docente->id); @endphp
                                        <option value="{{ $docente->id }}" @selected((string) $seleccion === (string) $docente->id) @disabled($ocupado)>{{ $docente->persona ? $docente->nombre_completo : $docente->username }}{{ $ocupado ? ' · Ya ocupa otro cargo' : '' }}</option>
                                    @endforeach
                                </select>
                                <button class="btn asignacion-primary" type="submit" aria-label="Guardar designación de {{ $nombre }}"><i class="fa-solid fa-check" aria-hidden="true"></i> Guardar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
