<?php

namespace App\Services\Docente;

use App\Models\AsignacionDocente;
use App\Models\AsistenciaEstudiante;
use App\Models\Institucion;
use App\Models\Matricula;
use App\Models\PeriodoAcademico;
use App\Models\SesionAsistencia;
use App\Models\Usuario;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistroAsistencia
{
    public const REPORTES = ['cursos' => 'Clases por curso', 'semanas' => 'Clases por semana'];

    public const REGLAS_FILTROS = ['periodo' => ['nullable', 'integer'], 'carga' => ['nullable', 'integer'],
        'desde' => ['nullable', 'date_format:Y-m-d'], 'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde']];

    public function __construct(private AlertasAsistencia $alertas) {}

    public function listado(Request $request): array
    {
        [, $periodos, $periodo, $cargas, $carga, $consulta] = $this->clasesFiltradas($request);
        $sesiones = $consulta->orderByDesc('fecha')->orderByDesc('id')->paginate(15)->withQueryString();
        $sesiones->getCollection()->each(fn ($sesion) => $sesion->setRelation('carga', $cargas->firstWhere('id', $sesion->asignacion_docente_id)));

        return compact('periodos', 'periodo', 'cargas', 'carga', 'sesiones') + ['estados' => AsistenciaEstudiante::ESTADOS];
    }

    public function reporte(Request $request, string $agrupacion): array
    {
        [$docente, , $periodo, $cargas, $carga, $consulta] = $this->clasesFiltradas($request);
        abort_unless($periodo, 404);
        $sesiones = $this->limitarFechas($consulta, $request)->orderBy('fecha')->orderBy('id')->get()
            ->each(fn ($sesion) => $sesion->setRelation('carga', $cargas->firstWhere('id', $sesion->asignacion_docente_id)));
        $grupos = $agrupacion === 'cursos' ? $this->gruposPorCurso($sesiones) : $this->gruposPorSemana($sesiones);
        $cargaSemanal = ($carga ? collect([$carga]) : $cargas)->sum(fn ($c) => (float) $c->horas_semanales);

        return compact('docente', 'periodo', 'carga', 'agrupacion', 'grupos', 'cargaSemanal') + [
            'titulo' => self::REPORTES[$agrupacion], 'institucion' => Institucion::findOrFail($docente->institucion_id),
            'estados' => AsistenciaEstudiante::ESTADOS, 'resumen' => $this->totales($sesiones),
            'desde' => $request->input('desde'), 'hasta' => $request->input('hasta'),
        ];
    }

    public function limitarFechas(Builder $consulta, Request $request): Builder
    {
        return $consulta->when($request->filled('desde'), fn ($q) => $q->whereDate('fecha', '>=', $request->input('desde')))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('fecha', '<=', $request->input('hasta')));
    }

    public function gruposPorCurso(Collection $sesiones): Collection
    {
        return $sesiones->groupBy(fn ($s) => $s->carga->paralelo_id)
            ->sortBy(function ($filas) {
                $carga = $filas->first()->carga;

                return sprintf('%03d', $carga->curso->grado).'|'.($carga->curso->especialidad?->nombre ?? '').'|'.$carga->curso->nombre.'|'.$carga->paralelo->letra;
            }, SORT_NATURAL)
            ->map(function ($filas) {
                $carga = $filas->first()->carga;

                return ['titulo' => $carga->curso->nombre.' '.$carga->paralelo->letra.' · '.($carga->curso->especialidad?->nombre ?? 'EGB'),
                    'filas' => $filas->sortBy(fn ($s) => $s->carga->asignatura?->nombre ?? $s->carga->titulo, SORT_NATURAL | SORT_FLAG_CASE)->values()] + $this->totales($filas);
            })->values();
    }

    public function gruposPorSemana(Collection $sesiones): Collection
    {
        return $sesiones->groupBy(fn ($s) => $s->fecha->copy()->startOfWeek()->toDateString())
            ->map(function ($filas, $lunes) {
                $inicio = CarbonImmutable::parse($lunes);

                return ['titulo' => 'Semana del '.$inicio->format('d/m/Y').' al '.$inicio->addDays(6)->format('d/m/Y'), 'filas' => $filas] + $this->totales($filas);
            })->values();
    }

    public function totales(Collection $sesiones): array
    {
        $estados = collect(AsistenciaEstudiante::ESTADOS)->keys()->mapWithKeys(fn ($e) => [$e => (int) $sesiones->sum('total_'.strtolower($e))])->all();
        $registros = array_sum($estados);

        return ['clases' => $sesiones->count(), 'horas' => (int) $sesiones->sum('horas'), 'estados' => $estados,
            'asistencia' => $registros ? round(($estados['P'] + $estados['A']) * 100 / $registros, 2) : null];
    }

    public function clasesFiltradas(Request $request): array
    {
        [$docente, $periodos, $periodo, $cargas] = $this->base($request);
        $cargas = $cargas->sortBy(fn ($c) => $c->asignatura?->nombre ?? $c->titulo, SORT_NATURAL | SORT_FLAG_CASE)->values();
        $carga = $request->filled('carga') ? $cargas->firstWhere('id', $request->integer('carga')) : null;
        abort_if($request->filled('carga') && ! $carga, 404);
        $conteos = collect(AsistenciaEstudiante::ESTADOS)->keys()
            ->mapWithKeys(fn ($estado) => ['asistencias as total_'.strtolower($estado) => fn ($q) => $q->where('estado', $estado)])->all();
        $consulta = SesionAsistencia::whereIn('asignacion_docente_id', $carga ? [$carga->id] : $cargas->pluck('id'))->withCount($conteos);

        return [$docente, $periodos, $periodo, $cargas, $carga, $consulta];
    }

    public function contexto(Request $request): array
    {
        [$docente, $periodos, $periodo, $cargas] = $this->base($request);
        $carga = $request->filled('carga') ? $cargas->firstWhere('id', $request->integer('carga')) : $cargas->first();
        abort_if($request->filled('carga') && ! $carga, 404);
        $referencia = CarbonImmutable::today('America/Guayaquil');
        if ($periodo) {
            $referencia = $referencia->max(CarbonImmutable::parse($periodo->fecha_inicio))->min(CarbonImmutable::parse($periodo->fecha_fin));
        }
        $fecha = CarbonImmutable::parse($request->input('fecha', $referencia->toDateString()));
        if ($periodo && ($fecha->greaterThan($periodo->fecha_fin) || $fecha->lessThan($periodo->fecha_inicio))) {
            throw ValidationException::withMessages(['fecha' => 'Seleccione una fecha dentro del período académico.']);
        }
        $maxHoras = $carga ? SesionAsistencia::maxHorasPorClase((float) $carga->horas_semanales) : 0;
        $matriculas = $this->matriculasActivas($docente->institucion_id, $periodo?->id, $carga?->paralelo_id);
        $sesion = SesionAsistencia::where('asignacion_docente_id', $carga?->id)->whereDate('fecha', $fecha)->with('asistencias')->first();
        $registros = AsistenciaEstudiante::whereIn('matricula_id', $matriculas->pluck('id'))
            ->whereIn('sesion_asistencia_id', SesionAsistencia::select('id')->where('asignacion_docente_id', $carga?->id))->get(['matricula_id', 'estado']);
        $resumen = [
            'clases' => SesionAsistencia::where('asignacion_docente_id', $carga?->id)->count(),
            'alumnos' => $matriculas->count(),
            'promedio' => $registros->isEmpty() ? null : round($registros->whereIn('estado', ['P', 'A'])->count() * 100 / $registros->count(), 2),
            'alertas' => $this->alertas->enRiesgo($matriculas->pluck('id'))->count(),
        ];

        return compact('periodos', 'periodo', 'cargas', 'carga', 'fecha', 'maxHoras', 'matriculas', 'sesion', 'resumen') + ['estados' => AsistenciaEstudiante::ESTADOS];
    }

    public function matriculasActivas(int $institucionId, ?int $periodoId, ?int $paraleloId): Collection
    {
        return Matricula::where('institucion_id', $institucionId)->where('periodo_id', $periodoId)
            ->where('paralelo_id', $paraleloId)->where('estado', 'ACTIVA')
            ->whereHas('estudiante', fn ($q) => $q->alumnos()->where('institucion_id', $institucionId))
            ->with('estudiante.persona')->get()
            ->sortBy(fn ($m) => ($m->estudiante->persona?->primer_apellido ?? '').' '.($m->estudiante->persona?->segundo_apellido ?? '').' '.$m->estudiante->nombre_completo, SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    private function base(Request $request): array
    {
        $docente = Usuario::findOrFail($request->session()->get('auth_user.id'));
        abort_unless($docente->institucion_id, 403);
        $periodos = PeriodoAcademico::where('institucion_id', $docente->institucion_id)
            ->orderByDesc('es_activo')->orderByDesc('fecha_inicio')->get();
        $periodo = $request->filled('periodo') ? $periodos->firstWhere('id', $request->integer('periodo')) : $periodos->first();
        abort_if($request->filled('periodo') && ! $periodo, 404);
        $cargas = AsignacionDocente::where('docente_id', $docente->id)->where('institucion_id', $docente->institucion_id)
            ->where('periodo_id', $periodo?->id)->where('tipo', 'carga')->where('horas_semanales', '>', 0)
            ->whereHas('curso', fn ($q) => $q->where('institucion_id', $docente->institucion_id))
            ->whereHas('paralelo')->with(['curso.especialidad', 'paralelo', 'asignatura'])->orderBy('curso_id')->orderBy('paralelo_id')->get()
            ->filter(fn ($c) => (int) $c->paralelo->curso_id === (int) $c->curso_id)->values();

        return [$docente, $periodos, $periodo, $cargas];
    }

    public function guardar(array $contexto, array $datos): void
    {
        DB::transaction(function () use ($contexto, $datos) {
            $sesion = SesionAsistencia::firstOrCreate([
                'asignacion_docente_id' => $contexto['carga']->id, 'fecha' => $contexto['fecha']->toDateString(),
            ]);
            $sesion = SesionAsistencia::whereKey($sesion->id)->lockForUpdate()->firstOrFail();
            $sesion->update(['horas' => $datos['horas'], 'tema' => $datos['tema'] ?? null]);
            foreach ($datos['estudiantes'] as $matricula => $estado) {
                $clave = ['sesion_asistencia_id' => $sesion->id, 'matricula_id' => $matricula];
                if ($estado) {
                    AsistenciaEstudiante::updateOrCreate($clave, ['estado' => $estado, 'observacion' => $datos['observaciones'][$matricula] ?? null]);
                } else {
                    AsistenciaEstudiante::where($clave)->delete();
                }
            }
            $this->alertas->evaluar(array_keys($datos['estudiantes']));
        });
    }
}
