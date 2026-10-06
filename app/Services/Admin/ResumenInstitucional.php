<?php

namespace App\Services\Admin;

use App\Models\Asignatura;
use App\Models\Curso;
use App\Models\Especialidad;
use App\Models\Institucion;
use App\Models\Paralelo;
use App\Models\PeriodoAcademico;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResumenInstitucional
{
    public function datos(Institucion $institucion): array
    {
        $ahora = now('America/Guayaquil');
        $desde = $ahora->copy()->subDays(6)->startOfDay()->setTimezone(config('app.timezone'));
        $hoy = $ahora->copy()->startOfDay()->setTimezone(config('app.timezone'));
        $hasta = $ahora->copy()->setTimezone(config('app.timezone'));
        $usuarios = Usuario::query()->where('institucion_id', $institucion->id);
        $cursos = Curso::query()->where('institucion_id', $institucion->id);
        $asignaturas = Asignatura::query()->where('institucion_id', $institucion->id);
        $paralelos = Paralelo::query()->whereIn('curso_id', (clone $cursos)->select('id'));
        $roles = Rol::query()->get(['id', 'codigo', 'nombre']);
        $porRol = [];
        foreach (['docente' => 'Docentes', 'estudiante' => 'Estudiantes', 'admin' => 'Administradores', 'inspector' => 'Inspectores', 'directivo' => 'Directivos', 'administrativo' => 'Personal administrativo'] as $clave => $label) {
            $ids = $roles->filter(fn (Rol $rol) => in_array(integraEduNormalizeRoleValue($rol->codigo), integraEduRoleAliases($clave), true)
                || in_array(integraEduNormalizeRoleValue($rol->nombre), integraEduRoleAliases($clave), true))->pluck('id');
            $porRol[$clave] = ['nombre' => $label, 'total' => (clone $usuarios)->whereHas('roles', fn ($q) => $q->whereIn('roles.id', $ids))->count()];
        }
        $porEstado = (clone $usuarios)->select('estado')->selectRaw('COUNT(*) as total')->groupBy('estado')->pluck('total', 'estado');
        $distribucion = Schema::hasTable('docente_asignatura_curso') ? DB::table('docente_asignatura_curso')
            ->whereIn('usuario_id', (clone $usuarios)->select('id'))->whereIn('curso_id', (clone $cursos)->select('id'))
            ->whereIn('asignatura_id', (clone $asignaturas)->select('id')) : null;
        $horarios = Schema::hasTable('horario_docente') ? DB::table('horario_docente')
            ->whereIn('usuario_id', (clone $usuarios)->select('id'))->whereIn('curso_id', (clone $cursos)->select('id'))
            ->whereIn('asignatura_id', (clone $asignaturas)->select('id')) : null;
        $periodos = PeriodoAcademico::query()->where('institucion_id', $institucion->id);
        $periodoActivo = (clone $periodos)->where('es_activo', true)->orderByDesc('fecha_inicio')->first();
        $pendientes = [];
        if (! $periodoActivo) {
            $pendientes[] = 'No hay un período académico activo.';
        }
        $cursosSinParalelos = (clone $cursos)->whereDoesntHave('paralelos')->count();
        if ($cursosSinParalelos > 0) {
            $pendientes[] = $cursosSinParalelos.' cursos no tienen paralelos registrados.';
        }
        $sinAcceso = (clone $usuarios)->whereNull('ultimo_acceso')->count();
        if ($sinAcceso > 0) {
            $pendientes[] = $sinAcceso.' cuentas aún no registran un ingreso.';
        }

        return [
            'actualizado' => $ahora, 'desde' => $desde->copy()->setTimezone('America/Guayaquil'),
            'usuarios' => (int) $porEstado->sum(), 'activos' => (int) $porEstado->get('ACTIVO', 0),
            'bloqueados' => (int) $porEstado->get('INACTIVO', 0), 'porEstado' => $porEstado, 'porRol' => $porRol,
            'cursos' => (clone $cursos)->count(), 'paralelos' => (clone $paralelos)->count(),
            'capacidad' => (int) (clone $paralelos)->where('estado', 'ACTIVO')->sum('capacidad'),
            'asignaturas' => (clone $asignaturas)->count(),
            'especialidades' => Especialidad::query()->where('institucion_id', $institucion->id)->count(),
            'periodos' => (clone $periodos)->count(), 'periodoActivo' => $periodoActivo,
            'asignaciones' => $distribucion ? (clone $distribucion)->count() : null,
            'horasAsignadas' => $distribucion ? (int) (clone $distribucion)->sum('horas_asignadas') : null,
            'bloquesHorario' => $horarios ? (clone $horarios)->count() : null,
            'accesosHoy' => (clone $usuarios)->whereBetween('ultimo_acceso', [$hoy, $hasta])->count(),
            'accesosSemana' => (clone $usuarios)->whereBetween('ultimo_acceso', [$desde, $hasta])->count(),
            'actividad' => [
                ['nombre' => 'Usuarios creados', 'total' => (clone $usuarios)->whereBetween('created_at', [$desde, $hasta])->count()],
                ['nombre' => 'Cuentas existentes actualizadas', 'total' => (clone $usuarios)->where('created_at', '<', $desde)->whereBetween('updated_at', [$desde, $hasta])->count()],
                ['nombre' => 'Cursos creados', 'total' => (clone $cursos)->whereBetween('created_at', [$desde, $hasta])->count()],
                ['nombre' => 'Paralelos creados', 'total' => (clone $paralelos)->whereBetween('created_at', [$desde, $hasta])->count()],
                ['nombre' => 'Asignaturas creadas', 'total' => (clone $asignaturas)->whereBetween('created_at', [$desde, $hasta])->count()],
            ],
            'ultimosAccesos' => (clone $usuarios)->select(['id', 'username', 'estado', 'ultimo_acceso'])
                ->whereNotNull('ultimo_acceso')->where('ultimo_acceso', '<=', $hasta)
                ->orderByDesc('ultimo_acceso')->orderByDesc('id')->limit(10)->get(),
            'pendientes' => $pendientes,
        ];
    }
}
