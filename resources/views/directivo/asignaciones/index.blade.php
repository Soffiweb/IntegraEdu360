@extends('layouts.admin-shell')
@section('title', 'Asignaciones docentes')
@section('admin-nav', 'asignaciones')
@section('sidebar-title', 'Directivo')
@section('crumbs')
    <a href="{{ route('roles.dashboard', 'directivo') }}">Resumen Ejecutivo</a><span>/</span><span>Asignaciones docentes</span>
@endsection
@section('head')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/asignaciones-docentes.css') }}?v={{ filemtime(public_path('css/asignaciones-docentes.css')) }}" rel="stylesheet">
@endsection
@section('content')
<div class="asignaciones-workspace">
    <header class="asignaciones-heading">
        <div><span class="asignaciones-kicker">Organización del equipo docente</span><h1>Asignaciones docentes</h1><p>Organiza las horas semanales, actividades y comisiones de cada docente.</p></div>
        <form method="GET" action="{{ route('directivo.asignaciones.index') }}" class="periodo-selector">
            <label for="periodo">Período académico</label>
            <div class="periodo-control"><select id="periodo" name="periodo" class="select" @disabled($periodos->isEmpty())>
                @forelse ($periodos as $opcion)<option value="{{ $opcion->id }}" @selected($periodo?->id === $opcion->id)>{{ $opcion->nombre }}{{ $opcion->es_activo ? ' · Activo' : '' }}</option>@empty<option>Sin períodos registrados</option>@endforelse
            </select><button class="btn" type="submit" @disabled($periodos->isEmpty())>Ver</button></div>
        </form>
    </header>
    @if(session('status'))<div class="alert" role="status">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="errors" role="alert"><strong>Revisa los datos de la asignación.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if(!$periodo)
        <section class="asignaciones-empty"><i class="fa-regular fa-calendar" aria-hidden="true"></i><h2>Primero necesitas un período académico</h2><p>Solicita al administrador institucional que registre el período para organizar las asignaciones docentes.</p></section>
    @else
    <div class="asignacion-section-heading">
        <a class="btn" target="_blank" rel="noopener" href="{{ route('directivo.asignaciones.pdf', ['periodo' => $periodo->id, 'search' => request('search')]) }}"><i class="fa-solid fa-file-pdf" aria-hidden="true"></i> Reporte general PDF</a>
        @if($docente)<a class="btn" target="_blank" rel="noopener" href="{{ route('directivo.asignaciones.docente.pdf', ['docente' => $docente->id, 'periodo' => $periodo->id]) }}"><i class="fa-solid fa-file-pdf" aria-hidden="true"></i> Reporte individual PDF</a>@endif
    </div>
    <details>
        <summary class="btn">Reportes agrupados PDF</summary>
        <p>
            @foreach(\App\Services\Directivo\ReporteDocentesAgrupado::TIPOS as $agrupacion => $nombreReporte)
                <a class="btn" target="_blank" rel="noopener" href="{{ route('directivo.asignaciones.agrupado.pdf', ['agrupacion' => $agrupacion, 'periodo' => $periodo->id, 'search' => request('search')]) }}"><i class="fa-solid fa-file-pdf" aria-hidden="true"></i> {{ $nombreReporte }}</a>
            @endforeach
        </p>
    </details>
    <div class="asignaciones-layout">
        <aside class="docentes-panel" aria-label="Seleccionar docente">
            <div class="docentes-panel-title"><h2>Equipo docente</h2><span>{{ $docentesVisibles->count() }}</span></div>
            <form method="GET" action="{{ route('directivo.asignaciones.index') }}" class="docentes-search">
                <input type="hidden" name="periodo" value="{{ $periodo->id }}">
                <label class="sr-only" for="search-docente">Buscar docente</label><input class="input" id="search-docente" name="search" value="{{ request('search') }}" placeholder="Nombre o identificación">
                <button class="btn" type="submit" aria-label="Buscar docente"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
            </form>
            <div class="docentes-list">
                @forelse($docentesVisibles as $opcion)
                    <a class="docente-choice {{ $docente?->id === $opcion->id ? 'is-selected' : '' }}" href="{{ route('directivo.asignaciones.index', ['periodo' => $periodo->id, 'docente' => $opcion->id, 'search' => request('search')]) }}" @if($docente?->id === $opcion->id) aria-current="true" @endif>
                        <span class="docente-avatar" aria-hidden="true"><i class="fa-solid fa-user"></i></span><span class="docente-choice-copy"><strong>{{ $opcion->persona ? $opcion->nombre_completo : $opcion->username }}</strong><small>{{ $opcion->username }} · {{ number_format((float) ($horas[$opcion->id] ?? 0), 2, ',', '.') }} h/semana</small></span><i class="fa-solid fa-angle-right" aria-hidden="true"></i>
                    </a>
                @empty<div class="docentes-no-results">No hay docentes con este criterio.<a href="{{ route('directivo.asignaciones.index', ['periodo' => $periodo->id]) }}">Limpiar búsqueda</a></div>@endforelse
            </div>
        </aside>
        <div class="docente-detail">
        @if($docente)
            <section class="docente-context"><div><span class="asignaciones-kicker">Docente seleccionado</span><h2>{{ $docente->persona ? $docente->nombre_completo : $docente->username }}</h2><p>{{ $periodo->nombre }} · {{ $asignaciones->count() }} asignaciones registradas</p></div><div class="docente-hours"><strong>{{ number_format((float) $asignaciones->where('tipo', 'carga')->sum('horas_semanales'), 2, ',', '.') }}</strong><span>períodos pedagógicos / semana</span></div></section>
            @php($tipo = old('tipo', $editando?->tipo ?? 'carga'))
            <section class="asignacion-editor" id="asignacion-editor">
                <div class="asignacion-section-heading"><h2>{{ $editando ? 'Editar asignación' : 'Nueva asignación' }}</h2>@if($editando)<a class="btn" href="{{ route('directivo.asignaciones.index', ['periodo' => $periodo->id, 'docente' => $docente->id]) }}">Cancelar edición</a>@endif</div>
                <form method="POST" action="{{ $editando ? route('directivo.asignaciones.update', $editando) : route('directivo.asignaciones.store') }}" id="asignacion-form">
                    @csrf @if($editando) @method('PUT') @endif
                    <input type="hidden" name="periodo_id" value="{{ $periodo->id }}"><input type="hidden" name="docente_id" value="{{ $docente->id }}">
                    <fieldset class="asignacion-types"><legend>¿Qué deseas asignar?</legend>
                        @foreach(\App\Models\AsignacionDocente::TIPOS as $valor => $nombre)
                            <label class="asignacion-type"><input type="radio" name="tipo" value="{{ $valor }}" @checked($tipo === $valor) required><span><i class="fa-solid {{ ['carga'=>'fa-clock', 'actividad'=>'fa-list-check', 'comision'=>'fa-people-group'][$valor] }}" aria-hidden="true"></i>{{ $nombre }}</span></label>
                        @endforeach
                    </fieldset>
                    <div class="asignacion-form-grid">
                        <div class="field-group full"><label for="titulo">Nombre de la asignación</label><input class="input" id="titulo" name="titulo" maxlength="255" value="{{ old('titulo', $editando?->titulo) }}" placeholder="Ej. Matemática · Octavo A / Tutoría / Comisión de convivencia" required></div>
                        <div class="field-group"><label for="horas_semanales">Horas por semana</label><input class="input" type="number" id="horas_semanales" name="horas_semanales" min="0" max="168" step="0.01" value="{{ old('horas_semanales', $editando?->horas_semanales) }}" placeholder="Ej. 4" required><small>Las comisiones no cuentan como períodos pedagógicos de clase.</small></div>
                        <div class="field-group"><label for="fecha_limite">Fecha de entrega <span class="optional">Opcional</span></label><input class="input" type="date" id="fecha_limite" name="fecha_limite" min="{{ $periodo->fecha_inicio->format('Y-m-d') }}" max="{{ $periodo->fecha_fin->format('Y-m-d') }}" value="{{ old('fecha_limite', $editando?->fecha_limite?->format('Y-m-d')) }}"></div>
                        <div class="field-group" data-carga><label for="paralelo_id">Curso y paralelo</label><select class="select" id="paralelo_id" name="paralelo_id"><option value="">Selecciona un curso y paralelo</option>@foreach($cursos as $curso)@foreach($curso->paralelos as $paralelo)<option value="{{ $paralelo->id }}" data-curso="{{ $curso->id }}" @selected((string) old('paralelo_id', $editando?->paralelo_id) === (string) $paralelo->id)>{{ $curso->nombre }} · {{ $paralelo->letra }}{{ $curso->especialidad ? ' · '.$curso->especialidad->nombre : '' }}</option>@endforeach @endforeach</select></div>
                        <div class="field-group" data-carga><label for="asignatura_id">Asignatura del currículo</label><select class="select" id="asignatura_id" name="asignatura_id"><option value="">Selecciona una asignatura</option>@foreach($cursos as $curso)@foreach($curso->asignaturas as $asignatura)<option value="{{ $asignatura->id }}" data-curso="{{ $curso->id }}" @selected((string) old('asignatura_id', $editando?->asignatura_id) === (string) $asignatura->id && (string) old('curso_id', $editando?->curso_id) === (string) $curso->id)>{{ $asignatura->nombre }}</option>@endforeach @endforeach</select><small id="curriculo-ayuda" aria-live="polite">Selecciona primero un curso y paralelo.</small></div>
                        <div class="field-group full"><label for="observaciones">Responsabilidades y observaciones <span class="optional">Opcional</span></label><textarea class="textarea" id="observaciones" name="observaciones" maxlength="2000" rows="3" placeholder="Describe las tareas, acuerdos o entregables…">{{ old('observaciones', $editando?->observaciones) }}</textarea></div>
                    </div>
                    <div class="asignacion-save"><span id="asignacion-preview" aria-live="polite">Solo la carga horaria se suma al máximo de 25 períodos pedagógicos.</span><button class="btn asignacion-primary" type="submit"><i class="fa-solid fa-check" aria-hidden="true"></i>{{ $editando ? 'Guardar cambios' : 'Guardar asignación' }}</button></div>
                </form>
            </section>
            <section class="asignacion-records"><div class="asignacion-section-heading"><h2>Responsabilidades asignadas</h2><span>{{ $asignaciones->count() }} registros</span></div>
                @forelse($asignaciones as $asignacion)
                    <article class="asignacion-record"><div class="asignacion-record-main"><span class="asignacion-kind">{{ \App\Models\AsignacionDocente::TIPOS[$asignacion->tipo] }}</span><h3>{{ $asignacion->titulo }}</h3>@if($asignacion->tipo === 'carga')<p>{{ $asignacion->asignatura?->nombre }} · {{ $asignacion->curso?->nombre }} {{ $asignacion->paralelo?->letra }}</p>@endif @if($asignacion->observaciones)<p>{{ $asignacion->observaciones }}</p>@endif @if($asignacion->fecha_limite)<small>Entrega: {{ $asignacion->fecha_limite->format('d/m/Y') }}</small>@endif</div><div class="asignacion-record-actions"><strong>{{ number_format((float) $asignacion->horas_semanales, 2, ',', '.') }} h/sem</strong><a class="btn record-action" href="{{ route('directivo.asignaciones.index', ['edit' => $asignacion->id]) }}#asignacion-editor" aria-label="Editar {{ $asignacion->titulo }}"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></a><form method="POST" action="{{ route('directivo.asignaciones.destroy', $asignacion) }}" data-delete-assignment>@csrf @method('DELETE')<button class="btn record-action" type="submit" aria-label="Retirar {{ $asignacion->titulo }}"><i class="fa-solid fa-trash-can" aria-hidden="true"></i></button></form></div></article>
                @empty<div class="asignaciones-empty"><i class="fa-regular fa-clipboard" aria-hidden="true"></i><h3>Este docente aún no tiene asignaciones</h3><p>Empieza con su carga horaria o añade una actividad o comisión.</p></div>@endforelse
            </section>
        @else<section class="asignaciones-empty"><h2>No hay docentes registrados</h2><p>El administrador debe registrar las cuentas docentes de la institución.</p></section>@endif
        </div>
    </div>
    @endif
</div>
@endsection
@section('scripts')
    <script src="{{ asset('js/asignaciones-docentes.js') }}?v={{ filemtime(public_path('js/asignaciones-docentes.js')) }}" defer></script>
@endsection
