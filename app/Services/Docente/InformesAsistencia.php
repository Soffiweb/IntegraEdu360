<?php

namespace App\Services\Docente;

use App\Models\AlertaAsistencia;
use App\Models\AsistenciaEstudiante;
use App\Models\Matricula;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class InformesAsistencia
{
    public const TABS = ['resumen' => 'Resumen', 'clases' => 'Por clase', 'cursos' => 'Por curso', 'semanas' => 'Por semana', 'meses' => 'Por mes',
        'estudiantes' => 'Por estudiante', 'alertas' => 'Alertas de asistencia', 'observaciones' => 'Observaciones'];

    private const POR_PAGINA = 25;

    public function __construct(private RegistroAsistencia $registro, private AlertasAsistencia $alertas) {}

    public function datos(Request $request): array
    {
        $tab = $request->input('tab', 'resumen');
        abort_unless(isset(self::TABS[$tab]), 404);
        [, $periodos, $periodo, $cargas, $carga, $consulta] = $this->registro->clasesFiltradas($request);
        $sesiones = $this->registro->limitarFechas($consulta, $request)->orderBy('fecha')->orderBy('id')->get()
            ->each(fn ($sesion) => $sesion->setRelation('carga', $cargas->firstWhere('id', $sesion->asignacion_docente_id)));
        $ids = $sesiones->modelKeys();
        $alcance = $carga ? collect([$carga]) : $cargas;
        $informe = match ($tab) {
            'resumen' => ['alertas' => $this->alertas->enRiesgo($this->matriculasDe($alcance, $periodo?->id))->count(),
                'estudiantes' => AsistenciaEstudiante::whereIn('sesion_asistencia_id', $ids)->distinct()->count('matricula_id')],
            'clases' => ['filas' => $this->porClase($sesiones, $alcance, $periodo?->id)],
            'cursos' => ['filas' => $this->registro->gruposPorCurso($sesiones)],
            'semanas' => ['filas' => $this->registro->gruposPorSemana($sesiones), 'cargaSemanal' => $alcance->sum(fn ($c) => (float) $c->horas_semanales)],
            'meses' => ['filas' => $this->porMes($sesiones)],
            'estudiantes' => ['filas' => $this->paginar($this->porEstudiante($ids), $request)],
            'alertas' => ['filas' => $this->paginar($this->filasDeAlerta($this->matriculasDe($alcance, $periodo?->id)), $request)],
            'observaciones' => ['filas' => $this->observaciones($ids, $cargas)],
        };

        return compact('periodos', 'periodo', 'cargas', 'carga', 'tab') + $informe + [
            'tabs' => self::TABS, 'estados' => AsistenciaEstudiante::ESTADOS, 'resumen' => $this->registro->totales($sesiones),
            'desde' => $request->input('desde'), 'hasta' => $request->input('hasta'),
        ];
    }

    private function porClase(Collection $sesiones, Collection $cargas, ?int $periodoId): Collection
    {
        return $cargas->map(fn ($carga) => [
            'titulo' => ($carga->asignatura?->nombre ?? $carga->titulo).' · '.$carga->curso->nombre.' '.$carga->paralelo->letra,
            'carga' => (float) $carga->horas_semanales,
            'url' => route('docente.asistencia.index', ['periodo' => $periodoId, 'carga' => $carga->id]),
        ] + $this->registro->totales($sesiones->where('asignacion_docente_id', $carga->id)));
    }

    private function porMes(Collection $sesiones): Collection
    {
        return $sesiones->groupBy(fn ($s) => $s->fecha->format('Y-m'))
            ->map(fn ($filas) => ['titulo' => ucfirst($filas->first()->fecha->locale('es')->isoFormat('MMMM YYYY'))] + $this->registro->totales($filas))->values();
    }

    private function porEstudiante(array $ids): Collection
    {
        $filas = AsistenciaEstudiante::whereIn('sesion_asistencia_id', $ids)->conteosPorMatricula()->get();
        $matriculas = $this->matriculas($filas->pluck('matricula_id'));

        return $filas->map(function ($fila) use ($matriculas) {
            $estados = collect(AsistenciaEstudiante::ESTADOS)->keys()->mapWithKeys(fn ($e) => [$e => (int) $fila->{'total_'.strtolower($e)}])->all();
            $registros = array_sum($estados);

            return $this->alumno($matriculas[$fila->matricula_id]) + ['estados' => $estados, 'registros' => $registros,
                'asistencia' => $registros ? round(($estados['P'] + $estados['A']) * 100 / $registros, 2) : null];
        })->sortBy(fn ($f) => $f['orden'], SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    /** Estudiantes activos de los paralelos a cargo del docente, para evaluar su asistencia global. */
    private function matriculasDe(Collection $cargas, ?int $periodoId): Collection
    {
        return Matricula::where('periodo_id', $periodoId)->where('estado', 'ACTIVA')->whereIn('paralelo_id', $cargas->pluck('paralelo_id')->unique())->pluck('id');
    }

    private function filasDeAlerta(Collection $ids): Collection
    {
        $riesgo = $this->alertas->enRiesgo($ids);
        $matriculas = $this->matriculas($riesgo->keys());
        $activas = AlertaAsistencia::whereIn('matricula_id', $riesgo->keys())->whereNull('resuelta_at')->get()->keyBy('matricula_id');

        return $riesgo->map(function ($datos, $id) use ($matriculas, $activas) {
            $representante = $matriculas[$id]->estudiante->representante;
            $notificada = $activas[$id]->notificada_at ?? null;

            return $this->alumno($matriculas[$id]) + $datos + [
                'matricula_id' => $id,
                'representante' => $representante ? ($representante->persona ? $representante->nombre_completo : $representante->username) : null,
                'notificacion' => match (true) {
                    (bool) $notificada => 'Notificado el '.$notificada->format('d/m/Y'),
                    blank($representante?->email) => 'Sin correo del representante',
                    default => 'Pendiente',
                },
            ];
        })->sortBy([['porcentaje', 'asc'], fn ($a, $b) => strnatcasecmp($a['orden'], $b['orden'])])->values();
    }

    private function observaciones(array $ids, Collection $cargas): LengthAwarePaginator
    {
        $filas = AsistenciaEstudiante::join('sesiones_asistencia as s', 's.id', '=', 'asistencias_estudiantes.sesion_asistencia_id')
            ->whereIn('sesion_asistencia_id', $ids)->whereNotNull('observacion')
            ->select('asistencias_estudiantes.*', 's.fecha', 's.asignacion_docente_id')
            ->with(['matricula.estudiante.persona', 'matricula.paralelo.curso'])
            ->orderByDesc('s.fecha')->orderByDesc('asistencias_estudiantes.id')->paginate(self::POR_PAGINA)->withQueryString();
        $filas->setCollection($filas->getCollection()->map(function ($fila) use ($cargas) {
            $carga = $cargas->firstWhere('id', $fila->asignacion_docente_id);

            return $this->alumno($fila->matricula) + ['asignatura' => $carga->asignatura?->nombre ?? $carga->titulo,
                'fecha' => CarbonImmutable::parse($fila->fecha), 'estado' => $fila->estado, 'observacion' => $fila->observacion];
        }));

        return $filas;
    }

    private function matriculas(Collection $ids): Collection
    {
        return Matricula::whereKey($ids->unique()->all())->with(['estudiante.persona', 'estudiante.representante.persona', 'paralelo.curso'])->get()->keyBy('id');
    }

    private function alumno(Matricula $matricula): array
    {
        $persona = $matricula->estudiante->persona;
        $curso = $matricula->paralelo->curso;
        $nombre = $persona ? trim(implode(' ', array_filter([$persona->primer_apellido, $persona->segundo_apellido, $persona->primer_nombre, $persona->segundo_nombre]))) : $matricula->estudiante->username;

        return ['nombre' => $nombre, 'identificacion' => $persona?->numero_identificacion ?: 'Sin registro', 'curso' => $curso->nombre.' '.$matricula->paralelo->letra,
            'orden' => sprintf('%03d', $curso->grado).'|'.$curso->nombre.'|'.$matricula->paralelo->letra.'|'.$nombre];
    }

    private function paginar(Collection $filas, Request $request): LengthAwarePaginator
    {
        $pagina = Paginator::resolveCurrentPage();

        return new LengthAwarePaginator($filas->forPage($pagina, self::POR_PAGINA)->values(), $filas->count(), self::POR_PAGINA, $pagina, ['path' => $request->url(), 'query' => $request->query()]);
    }
}
