<section class="instituciones-content zonas-content catalogo-content" aria-label="Resumen de superusuario">
    <header class="hero">
        <div>
            <div class="eyebrow">Módulo estratégico</div>
            <h1>Resumen de superusuario</h1>
            <p>Estado institucional y actividad actual de los administradores.</p>
        </div>
        <div class="hero-side">
            <strong>Datos actualizados</strong>
            <p>{{ $resumen['actualizado']->format('d/m/Y H:i') }} · Hora de Ecuador</p>
        </div>
    </header>

    <section class="stats" aria-label="Indicadores actuales">
        <article class="card"><strong class="metric">{{ number_format($resumen['institucionesTotal'], 0, ',', '.') }}</strong><span>Instituciones registradas · {{ number_format($resumen['institucionesActivas'], 0, ',', '.') }} activas</span></article>
        <article class="card"><strong class="metric">{{ number_format($resumen['administradoresTotal'], 0, ',', '.') }}</strong><span>Administradores registrados</span></article>
        <article class="card"><strong class="metric">{{ number_format($resumen['administradoresActivos'], 0, ',', '.') }}</strong><span>Administradores activos</span></article>
        <article class="card"><strong class="metric">{{ number_format($resumen['administradoresBloqueados'], 0, ',', '.') }}</strong><span>Administradores bloqueados</span></article>
        <article class="card"><strong class="metric">{{ number_format($resumen['accesosHoy'], 0, ',', '.') }}</strong><span>Administradores con acceso hoy</span></article>
        <article class="card"><strong class="metric">{{ number_format($resumen['accesosSemana'], 0, ',', '.') }}</strong><span>Administradores con acceso en los últimos 7 días</span></article>
    </section>

    <section class="panel">
        <div class="section-title">
            <h2>Actividad de los últimos 7 días</h2>
            <span>{{ $resumen['desde']->format('d/m/Y') }} – {{ $resumen['actualizado']->format('d/m/Y') }}</span>
        </div>
        <div class="sw-table-wrap">
            <table class="sw-table">
                <thead><tr><th scope="col">Actividad registrada</th><th scope="col">Registros</th></tr></thead>
                <tbody>
                    @foreach ($resumen['actividad'] as $actividad)
                        <tr><td>{{ $actividad['nombre'] }}</td><td>{{ number_format($actividad['total'], 0, ',', '.') }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p>Los valores cuentan registros distintos según su fecha de creación o última actualización. Las actualizaciones de cuentas incluyen los ingresos.</p>
    </section>

    <section class="panel">
        <div class="section-title"><h2>Estado y cobertura institucional</h2></div>
        <div class="sw-table-wrap">
            <table class="sw-table">
                <thead><tr><th scope="col">Indicador</th><th scope="col">Cantidad</th></tr></thead>
                <tbody>
                    @foreach ($resumen['institucionesPorEstado'] as $estado => $total)
                        <tr><td>Instituciones · {{ $estado ?: 'Sin estado' }}</td><td>{{ number_format($total, 0, ',', '.') }}</td></tr>
                    @endforeach
                    <tr><td>Instituciones sin administrador asignado</td><td>{{ number_format($resumen['institucionesSinAdministrador'], 0, ',', '.') }}</td></tr>
                    <tr><td>Instituciones sin distrito asignado</td><td>{{ number_format($resumen['institucionesSinDistrito'], 0, ',', '.') }}</td></tr>
                    <tr><td>Administradores sin accesos registrados</td><td>{{ number_format($resumen['sinAcceso'], 0, ',', '.') }}</td></tr>
                    <tr><td>Zonas educativas</td><td>{{ $resumen['zonas'] }}</td></tr>
                    <tr><td>Distritos educativos</td><td>{{ $resumen['distritos'] }}</td></tr>
                    <tr><td>Roles de usuario</td><td>{{ $resumen['roles'] }}</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <div class="section-title"><h2>Últimos accesos de administradores</h2><span>Hasta 10 cuentas</span></div>
        <div class="sw-table-wrap">
            <table class="sw-table">
                <thead><tr><th scope="col">Usuario</th><th scope="col">Institución</th><th scope="col">Estado</th><th scope="col">Último acceso</th></tr></thead>
                <tbody>
                    @forelse ($resumen['ultimosAccesos'] as $usuario)
                        <tr>
                            <td>{{ $usuario->username }}</td>
                            <td>{{ $usuario->institucion?->nombre ?: 'Sin institución asignada' }}</td>
                            <td>{{ $usuario->estado }}</td>
                            <td>{{ $usuario->ultimo_acceso->setTimezone('America/Guayaquil')->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">No hay accesos de administradores registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p>Se muestra el último ingreso de cada cuenta. Los indicadores de acceso cuentan administradores, no el número de sesiones.</p>
        <div class="actions">
            <a class="btn icon-btn" href="{{ route('superusuario.usuarios.index') }}" title="Gestión de Usuarios" aria-label="Gestión de Usuarios"><i class="fa-solid fa-users" aria-hidden="true"></i></a>
            <a class="btn icon-btn" href="{{ route('superusuario.instituciones.index') }}" title="Instituciones" aria-label="Instituciones"><i class="fa-solid fa-school" aria-hidden="true"></i></a>
        </div>
    </section>
</section>
