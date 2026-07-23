<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use App\Models\Asignatura;
use App\Models\Institucion;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistribucionHorasController extends Controller
{
    public function index(Request $request)
    {
        $institucionActiva = $this->activeInstitucion($request);
        $rolDocente = $this->docenteRole();

        $query = DB::table('docente_asignatura_curso as dac')
            ->join('usuarios as u', 'u.id', '=', 'dac.usuario_id')
            ->join('personas as p', 'p.id', '=', 'u.persona_id')
            ->join('asignaturas as a', 'a.id', '=', 'dac.asignatura_id')
            ->join('cursos as c', 'c.id', '=', 'dac.curso_id')
            ->join('usuario_rol as ur', function ($join) use ($rolDocente) {
                $join->on('ur.usuario_id', '=', 'u.id')
                    ->where('ur.rol_id', '=', $rolDocente->id);
            })
            ->select([
                'u.id as usuario_id',
                'p.primer_nombre',
                'p.segundo_nombre',
                'p.primer_apellido',
                'p.segundo_apellido',
                'a.nombre as asignatura',
                'a.area',
                'c.nombre as curso',
                'c.nivel',
                'dac.horas_asignadas',
            ]);

        if ($institucionActiva) {
            $query->where('u.institucion_id', $institucionActiva->id);
        }

        if ($search = trim((string) $request->string('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('p.primer_nombre', 'like', "%{$search}%")
                    ->orWhere('p.primer_apellido', 'like', "%{$search}%")
                    ->orWhere('a.nombre', 'like', "%{$search}%")
                    ->orWhere('c.nombre', 'like', "%{$search}%");
            });
        }

        $rows = $query->orderBy('p.primer_apellido')
            ->orderBy('p.primer_nombre')
            ->orderBy('c.nombre')
            ->orderBy('a.nombre')
            ->get();

        $docentes = $rows->groupBy('usuario_id')->map(function ($group) {
            $first = $group->first();
            $nombre = trim(implode(' ', array_filter([
                $first->primer_nombre,
                $first->segundo_nombre,
                $first->primer_apellido,
                $first->segundo_apellido,
            ])));

            return (object) [
                'id' => $first->usuario_id,
                'nombre' => $nombre ?: 'Sin nombre',
                'total_horas' => $group->sum('horas_asignadas'),
                'total_asignaturas' => $group->unique('asignatura')->count(),
                'total_cursos' => $group->unique('curso')->count(),
                'asignaciones' => $group->map(fn ($row) => (object) [
                    'asignatura' => $row->asignatura,
                    'area' => $row->area,
                    'curso' => $row->curso,
                    'nivel' => $row->nivel,
                    'horas' => $row->horas_asignadas,
                ]),
            ];
        })->values();

        $totalDocentesInstitucion = Usuario::query()
            ->whereHas('roles', fn ($q) => $q->where('roles.id', $rolDocente->id))
            ->when($institucionActiva, fn ($q) => $q->where('institucion_id', $institucionActiva->id))
            ->count();

        $metricas = [
            'docentes_asignados' => $docentes->count(),
            'total_docentes' => $totalDocentesInstitucion,
            'total_horas' => $docentes->sum('total_horas'),
            'promedio_horas' => $docentes->count() > 0
                ? round($docentes->avg('total_horas'), 1)
                : 0,
        ];

        return view('inspector.distribucion-horas.index', [
            'docentes' => $docentes,
            'metricas' => $metricas,
            'institucionActiva' => $institucionActiva,
        ]);
    }

    private function docenteRole(): Rol
    {
        return Rol::query()
            ->where(function ($query) {
                $query->where('codigo', '04')
                    ->orWhereRaw('UPPER(nombre) = ?', ['DOCENTE']);
            })
            ->firstOrFail();
    }

    private function activeInstitucion(Request $request): ?Institucion
    {
        $sessionUser = $request->session()->get('auth_user', []);
        $institucionId = $sessionUser['institucion_id'] ?? null;

        if ($institucionId) {
            return Institucion::find($institucionId);
        }

        $usuarioId = $sessionUser['id'] ?? null;

        if (! $usuarioId) {
            return null;
        }

        $usuario = Usuario::find($usuarioId);

        return $usuario?->institucion_id ? Institucion::find($usuario->institucion_id) : null;
    }
}
