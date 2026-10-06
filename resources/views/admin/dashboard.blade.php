@extends('layouts.admin-shell')

@section('title', 'Resumen Ejecutivo')
@section('admin-nav', 'resumen')
@section('sidebar-title', 'Administrador')
@section('sidebar-body', 'Estadísticas y seguimiento de la institución asignada.')
@section('crumbs')
    <span>Resumen Ejecutivo</span>
@endsection
@section('head')
    <link href="{{ asset('css/admin-resumen.css') }}" rel="stylesheet">
@endsection
@section('topbar-action')
    <a class="btn resumen-refresh" href="{{ route('admin.resumen.index') }}" aria-label="Actualizar resumen" title="Actualizar resumen"><i class="fa-solid fa-rotate-right" aria-hidden="true"></i></a>
@endsection

@section('content')
<div class="admin-resumen">
    <section class="hero">
        <div>
            <div class="eyebrow">Panel institucional</div>
            <h1>Resumen Ejecutivo</h1>
            <p>Actividad académica, cuentas y configuración de la institución.</p>
        </div>
        <div class="hero-side">
            <strong>{{ $resumen ? $resumen['actualizado']->format('d/m/Y H:i') : 'Institución no asignada' }}</strong>
            <p>{{ $resumen ? 'Datos actuales · Hora de Ecuador' : 'Asigne una institución a esta cuenta para consultar su resumen.' }}</p>
        </div>
    </section>
    @if ($resumen)
        <section class="stats" style="--stats-columns: 4;" aria-label="Estadísticas institucionales">
            @foreach ([['Usuarios', $resumen['usuarios']], ['Docentes', $resumen['porRol']['docente']['total']], ['Estudiantes', $resumen['porRol']['estudiante']['total']], ['Cursos', $resumen['cursos']], ['Paralelos', $resumen['paralelos']], ['Asignaturas', $resumen['asignaturas']], ['Usuarios activos', $resumen['activos']], ['Usuarios bloqueados', $resumen['bloqueados']]] as [$label, $total])
                <article class="card"><strong class="metric">{{ number_format($total, 0, ',', '.') }}</strong><span>{{ $label }}</span></article>
            @endforeach
        </section>
        <div class="resumen-grid">
            <section class="panel">
                <div class="section-title"><h2>Organización académica</h2></div>
                <div class="resumen-table-wrap">
                    <table>
                        <thead><tr><th scope="col">Indicador</th><th scope="col">Registro actual</th></tr></thead>
                        <tbody>
                            <tr><td>Período activo</td><td>{{ $resumen['periodoActivo']?->nombre ?? 'Sin período activo' }}</td></tr>
                            @if ($resumen['periodoActivo'])
                                <tr><td>Fechas del período</td><td>{{ $resumen['periodoActivo']->fecha_inicio->format('d/m/Y') }} – {{ $resumen['periodoActivo']->fecha_fin->format('d/m/Y') }}</td></tr>
                            @endif
                            <tr><td>Períodos registrados</td><td>{{ $resumen['periodos'] }}</td></tr>
                            <tr><td>Especialidades</td><td>{{ $resumen['especialidades'] }}</td></tr>
                            <tr><td>Capacidad de paralelos activos</td><td>{{ number_format($resumen['capacidad'], 0, ',', '.') }} plazas</td></tr>
                            <tr><td>Asignaciones docente / asignatura / curso</td><td>{{ $resumen['asignaciones'] ?? 'Sin datos disponibles' }}</td></tr>
                            <tr><td>Horas semanales asignadas</td><td>{{ $resumen['horasAsignadas'] ?? 'Sin datos disponibles' }}</td></tr>
                            <tr><td>Bloques de horario registrados</td><td>{{ $resumen['bloquesHorario'] ?? 'Sin datos disponibles' }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>
            <section class="panel">
                <div class="section-title"><h2>Comunidad educativa</h2></div>
                <div class="resumen-table-wrap">
                    <table>
                        <thead><tr><th scope="col">Perfil</th><th scope="col">Cuentas</th></tr></thead>
                        <tbody>
                            @foreach ($resumen['porRol'] as $rol)
                                <tr><td>{{ $rol['nombre'] }}</td><td>{{ number_format($rol['total'], 0, ',', '.') }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p>Una cuenta puede tener varios roles. Los estudiantes corresponden a cuentas registradas, no a matrículas.</p>
            </section>
            <section class="panel">
                <div class="section-title"><h2>Actividad de los últimos 7 días</h2></div>
                <p>{{ $resumen['desde']->format('d/m/Y') }} – {{ $resumen['actualizado']->format('d/m/Y') }}</p>
                <div class="resumen-table-wrap">
                    <table>
                        <thead><tr><th scope="col">Actividad</th><th scope="col">Cantidad</th></tr></thead>
                        <tbody>
                            @foreach ($resumen['actividad'] as $actividad)
                                <tr><td>{{ $actividad['nombre'] }}</td><td>{{ $actividad['total'] }}</td></tr>
                            @endforeach
                            <tr><td>Usuarios con ingreso hoy</td><td>{{ $resumen['accesosHoy'] }}</td></tr>
                            <tr><td>Usuarios con ingreso en los últimos 7 días</td><td>{{ $resumen['accesosSemana'] }}</td></tr>
                        </tbody>
                    </table>
                </div>
                <p>Se cuentan registros distintos según sus fechas. Los ingresos también actualizan las cuentas; los accesos cuentan usuarios, no sesiones.</p>
            </section>
            <section class="panel">
                <div class="section-title"><h2>Pendientes de configuración</h2></div>
                @forelse ($resumen['pendientes'] as $pendiente)
                    <p class="resumen-pendiente">{{ $pendiente }}</p>
                @empty
                    <p>No hay pendientes en los controles de período, paralelos y primer ingreso.</p>
                @endforelse
                <div class="actions">
                    @foreach ([['admin.periodos.index', 'Períodos académicos', 'calendar-days'], ['admin.usuarios.index', 'Usuarios', 'users'], ['admin.cursos.index', 'Cursos', 'school'], ['admin.paralelos.index', 'Paralelos', 'table-cells']] as [$ruta, $label, $icono])
                        <a class="btn resumen-icon" href="{{ route($ruta) }}" aria-label="{{ $label }}" title="{{ $label }}"><i class="fa-solid fa-{{ $icono }}" aria-hidden="true"></i></a>
                    @endforeach
                </div>
                <p>Asistencia, calificaciones y novedades disciplinarias se incorporarán cuando dispongan de registros institucionales persistentes.</p>
            </section>
        </div>
        <section class="panel resumen-access">
            <div class="section-title"><h2>Últimos accesos de la institución</h2><span>Hasta 10 usuarios</span></div>
            <div class="resumen-table-wrap">
                <table>
                    <thead><tr><th scope="col">Usuario</th><th scope="col">Estado</th><th scope="col">Último ingreso</th></tr></thead>
                    <tbody>
                        @forelse ($resumen['ultimosAccesos'] as $usuario)
                            <tr><td>{{ $usuario->username }}</td><td>{{ $usuario->estado }}</td><td>{{ $usuario->ultimo_acceso->setTimezone('America/Guayaquil')->format('d/m/Y H:i') }}</td></tr>
                        @empty
                            <tr><td colspan="3">No hay accesos registrados en esta institución.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @else
        <section class="panel"><p role="status">No hay una institución asociada a esta cuenta. No se pueden mostrar estadísticas institucionales.</p></section>
    @endif
</div>
@endsection
