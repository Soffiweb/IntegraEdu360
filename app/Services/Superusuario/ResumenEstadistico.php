<?php

namespace App\Services\Superusuario;

use App\Models\Distrito;
use App\Models\Institucion;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\Zona;

class ResumenEstadistico
{
    public function datos(): array
    {
        $ahora = now('America/Guayaquil');
        $hoy = $ahora->copy()->startOfDay()->setTimezone(config('app.timezone'));
        $desde = $ahora->copy()->subDays(6)->startOfDay()->setTimezone(config('app.timezone'));
        $hasta = $ahora->copy()->setTimezone(config('app.timezone'));
        $roles = Rol::query()->get(['id', 'codigo', 'nombre']);
        $administradorIds = $roles->filter(fn (Rol $rol) => in_array(integraEduNormalizeRoleValue($rol->codigo), integraEduRoleAliases('admin'), true)
            || in_array(integraEduNormalizeRoleValue($rol->nombre), integraEduRoleAliases('admin'), true))->pluck('id');
        $administradores = Usuario::query()->whereHas('roles', fn ($query) => $query->whereIn('roles.id', $administradorIds));
        $institucionesPorEstado = Institucion::query()->select('estado')->selectRaw('COUNT(*) as total')->groupBy('estado')->pluck('total', 'estado');
        $administradoresPorEstado = (clone $administradores)->select('estado')->selectRaw('COUNT(*) as total')->groupBy('estado')->pluck('total', 'estado');
        $institucionesConAdministrador = Institucion::query()->whereIn('id', (clone $administradores)->select('institucion_id'))->count();

        return [
            'actualizado' => $ahora,
            'desde' => $desde->copy()->setTimezone('America/Guayaquil'),
            'institucionesTotal' => (int) $institucionesPorEstado->sum(),
            'institucionesActivas' => (int) $institucionesPorEstado->get('ACTIVO', 0) + (int) $institucionesPorEstado->get('ACTIVA', 0),
            'institucionesPorEstado' => $institucionesPorEstado,
            'administradoresTotal' => (int) $administradoresPorEstado->sum(),
            'administradoresActivos' => (int) $administradoresPorEstado->get('ACTIVO', 0),
            'administradoresBloqueados' => (int) $administradoresPorEstado->get('INACTIVO', 0),
            'administradoresPorEstado' => $administradoresPorEstado,
            'accesosHoy' => (clone $administradores)->whereBetween('ultimo_acceso', [$hoy, $hasta])->count(),
            'accesosSemana' => (clone $administradores)->whereBetween('ultimo_acceso', [$desde, $hasta])->count(),
            'sinAcceso' => (clone $administradores)->whereNull('ultimo_acceso')->count(),
            'institucionesSinAdministrador' => (int) $institucionesPorEstado->sum() - $institucionesConAdministrador,
            'institucionesSinDistrito' => Institucion::query()->whereNull('distrito_id')->count(),
            'zonas' => Zona::count(),
            'distritos' => Distrito::count(),
            'roles' => $roles->count(),
            'actividad' => [
                ['nombre' => 'Instituciones creadas', 'total' => Institucion::query()->whereBetween('created_at', [$desde, $hasta])->count()],
                ['nombre' => 'Instituciones existentes actualizadas', 'total' => Institucion::query()->where('created_at', '<', $desde)->whereBetween('updated_at', [$desde, $hasta])->count()],
                ['nombre' => 'Administradores creados', 'total' => (clone $administradores)->whereBetween('created_at', [$desde, $hasta])->count()],
                ['nombre' => 'Cuentas existentes actualizadas', 'total' => (clone $administradores)->where('created_at', '<', $desde)->whereBetween('updated_at', [$desde, $hasta])->count()],
            ],
            'ultimosAccesos' => (clone $administradores)->select(['id', 'username', 'institucion_id', 'estado', 'ultimo_acceso'])
                ->with('institucion:id,nombre')->whereNotNull('ultimo_acceso')->where('ultimo_acceso', '<=', $hasta)
                ->orderByDesc('ultimo_acceso')->orderByDesc('id')->limit(10)->get(),
        ];
    }
}
