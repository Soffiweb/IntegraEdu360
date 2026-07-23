<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use App\Models\Institucion;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HorarioDocenteController extends Controller
{
    private const DIAS = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miercoles',
        4 => 'Jueves',
        5 => 'Viernes',
    ];

    public function index(Request $request)
    {
        $institucionActiva = $this->activeInstitucion($request);
        $rolDocente = $this->docenteRole();

        $query = Usuario::query()
            ->with('persona')
            ->whereHas('roles', fn ($q) => $q->where('roles.id', $rolDocente->id))
            ->when($institucionActiva, fn ($q) => $q->where('institucion_id', $institucionActiva->id))
            ->where('estado', 'ACTIVO');

        if ($search = trim((string) $request->string('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhereHas('persona', function ($pq) use ($search) {
                        $pq->where('primer_nombre', 'like', "%{$search}%")
                            ->orWhere('primer_apellido', 'like', "%{$search}%")
                            ->orWhere('numero_identificacion', 'like', "%{$search}%");
                    });
            });
        }

        $docentes = $query->orderByRaw(
            "EXISTS (SELECT 1 FROM horario_docente WHERE horario_docente.usuario_id = usuarios.id) DESC"
        )
            ->orderBy('username')
            ->paginate(10)
            ->withQueryString();

        $docenteIds = $docentes->pluck('id');

        $bloques = DB::table('horario_docente as h')
            ->join('asignaturas as a', 'a.id', '=', 'h.asignatura_id')
            ->join('cursos as c', 'c.id', '=', 'h.curso_id')
            ->whereIn('h.usuario_id', $docenteIds)
            ->select([
                'h.usuario_id',
                'h.dia_semana',
                'h.hora_inicio',
                'h.hora_fin',
                'h.aula',
                'a.nombre as asignatura',
                'c.nombre as curso',
            ])
            ->orderBy('h.dia_semana')
            ->orderBy('h.hora_inicio')
            ->get()
            ->groupBy('usuario_id');

        $totalConHorario = DB::table('horario_docente as h')
            ->join('usuarios as u', 'u.id', '=', 'h.usuario_id')
            ->join('usuario_rol as ur', function ($join) use ($rolDocente) {
                $join->on('ur.usuario_id', '=', 'u.id')
                    ->where('ur.rol_id', '=', $rolDocente->id);
            })
            ->when($institucionActiva, fn ($q) => $q->where('u.institucion_id', $institucionActiva->id))
            ->distinct('h.usuario_id')
            ->count('h.usuario_id');

        $totalBloques = DB::table('horario_docente as h')
            ->join('usuarios as u', 'u.id', '=', 'h.usuario_id')
            ->when($institucionActiva, fn ($q) => $q->where('u.institucion_id', $institucionActiva->id))
            ->count();

        $metricas = [
            'con_horario' => $totalConHorario,
            'total_docentes' => $docentes->total(),
            'total_bloques' => $totalBloques,
        ];

        return view('inspector.horario-docente.index', [
            'docentes' => $docentes,
            'bloques' => $bloques,
            'dias' => self::DIAS,
            'metricas' => $metricas,
            'institucionActiva' => $institucionActiva,
        ]);
    }

    public function show(Request $request, Usuario $usuario)
    {
        $institucionActiva = $this->activeInstitucion($request);
        $rolDocente = $this->docenteRole();

        abort_unless($usuario->roles()->where('roles.id', $rolDocente->id)->exists(), 404);

        if ($institucionActiva) {
            abort_unless((int) $usuario->institucion_id === (int) $institucionActiva->id, 404);
        }

        $usuario->load('persona');

        $bloques = DB::table('horario_docente as h')
            ->join('asignaturas as a', 'a.id', '=', 'h.asignatura_id')
            ->join('cursos as c', 'c.id', '=', 'h.curso_id')
            ->where('h.usuario_id', $usuario->id)
            ->select([
                'h.dia_semana',
                'h.hora_inicio',
                'h.hora_fin',
                'h.aula',
                'a.nombre as asignatura',
                'a.area',
                'c.nombre as curso',
            ])
            ->orderBy('h.dia_semana')
            ->orderBy('h.hora_inicio')
            ->get()
            ->groupBy('dia_semana');

        $allSlots = DB::table('horario_docente')
            ->where('usuario_id', $usuario->id)
            ->get(['hora_inicio', 'hora_fin']);

        $franjas = $allSlots
            ->map(fn ($s) => $s->hora_inicio . '-' . $s->hora_fin)
            ->unique()
            ->sort()
            ->values()
            ->map(function ($slot) {
                [$inicio, $fin] = explode('-', $slot);
                return (object) ['inicio' => $inicio, 'fin' => $fin, 'key' => $slot];
            });

        $grid = [];
        foreach ($franjas as $franja) {
            $row = ['franja' => $franja];
            foreach (self::DIAS as $diaNum => $diaNombre) {
                $row[$diaNum] = $bloques->get($diaNum, collect())
                    ->first(fn ($b) => $b->hora_inicio === $franja->inicio && $b->hora_fin === $franja->fin);
            }
            $grid[] = $row;
        }

        $totalHoras = $allSlots->sum(function ($s) {
            $inicio = strtotime($s->hora_inicio);
            $fin = strtotime($s->hora_fin);
            return ($fin - $inicio) / 3600;
        });

        return view('inspector.horario-docente.show', [
            'usuario' => $usuario,
            'grid' => $grid,
            'dias' => self::DIAS,
            'totalBloques' => $allSlots->count(),
            'totalHoras' => round($totalHoras, 1),
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
